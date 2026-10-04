<?php

namespace App\Services;

use App\Core\Database;
use App\Core\EventDispatcher;
use App\Enums\TicketStatus;
use App\Repositories\TicketRepository;
use App\Repositories\CommentRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\RatingRepository;

/**
 * Ticket Status State Machine Service
 * Implements Transition Rules Matrix from Diagram 3 & Sequence 6.2
 */
class TicketStatusService
{
    private TicketRepository $ticketRepo;
    private CommentRepository $commentRepo;
    private StatusLogRepository $statusLogRepo;
    private RatingRepository $ratingRepo;
    private EventDispatcher $events;
    private Database $db;

    public function __construct(
        ?TicketRepository $ticketRepo = null,
        ?CommentRepository $commentRepo = null,
        ?StatusLogRepository $statusLogRepo = null,
        ?RatingRepository $ratingRepo = null,
        ?EventDispatcher $events = null,
        ?Database $db = null
    ) {
        $this->ticketRepo = $ticketRepo ?? new TicketRepository();
        $this->commentRepo = $commentRepo ?? new CommentRepository();
        $this->statusLogRepo = $statusLogRepo ?? new StatusLogRepository();
        $this->ratingRepo = $ratingRepo ?? new RatingRepository();
        $this->events = $events ?? EventDispatcher::getInstance();
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Perform State Transition with strict validation, permission check, DB transaction, and events
     */
    public function transition(array $ticket, string $newStatusStr, array $actor, array $extra = []): bool
    {
        $currentStatusStr = $ticket['status'];

        // Validate Backed Enums
        $fromStatus = TicketStatus::tryFrom($currentStatusStr);
        $toStatus = TicketStatus::tryFrom($newStatusStr);

        if (!$fromStatus || !$toStatus) {
            throw new \InvalidArgumentException("สถานะไม่ถูกต้องในระบบ");
        }

        // 1. Validate State Transition Graph
        $this->validateTransition($fromStatus, $toStatus);

        // 2. Check Permission (RBAC)
        $this->checkPermission($actor, $ticket, $fromStatus, $toStatus, $extra);

        // 3. Execute DB Transaction
        $this->db->beginTransaction();

        try {
            $ticketUpdate = [
                'status' => $toStatus->value,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $logNote = $extra['note'] ?? '';
            $commentBody = $extra['comment'] ?? $logNote;
            $commentImage = $extra['image_path'] ?? null;

            // Specific transition business logic
            switch ($toStatus) {
                case TicketStatus::Assigned:
                    $techId = (int) ($extra['technician_id'] ?? 0);
                    if ($techId <= 0) {
                        throw new \InvalidArgumentException("กรุณาระบุช่างเทคนิคที่ต้องการมอบหมาย");
                    }
                    $ticketUpdate['technician_id'] = $techId;
                    if (empty($logNote)) {
                        $logNote = "มอบหมายงานให้ช่างรหัส #{$techId}";
                    }
                    break;

                case TicketStatus::InProgress:
                    if ($fromStatus === TicketStatus::Resolved) {
                        // User rejected resolution
                        $ticketUpdate['resolved_at'] = null;
                        if (empty($commentBody)) {
                            throw new \InvalidArgumentException("กรุณาระบุเหตุผลการปฏิเสธหรือไม่พึงพอใจในการซ่อม");
                        }
                        $logNote = "ผู้แจ้งปฏิเสธผลงาน: " . $commentBody;
                    } else {
                        // Tech accepted job
                        if (empty($commentBody)) {
                            $commentBody = "ช่างรับงานและกำลังดำเนินการตรวจสอบ/ซ่อมแซม";
                        }
                        $logNote = "ช่างเริ่มดำเนินการซ่อมแซม";
                    }
                    break;

                case TicketStatus::Resolved:
                    $ticketUpdate['resolved_at'] = date('Y-m-d H:i:s');
                    if (empty($commentBody)) {
                        $commentBody = "ช่างดำเนินการซ่อมแซมและทดสอบอุปกรณ์เรียบร้อยแล้ว";
                    }
                    if (empty($logNote)) {
                        $logNote = "ซ่อมแซมเสร็จสิ้น พร้อมให้ผู้แจ้งตรวจสอบ";
                    }
                    break;

                case TicketStatus::Closed:
                    $ticketUpdate['closed_at'] = date('Y-m-d H:i:s');
                    $score = (int) ($extra['rating_score'] ?? 5);
                    $feedback = $extra['rating_feedback'] ?? '';

                    if ($score < 1 || $score > 5) {
                        throw new \InvalidArgumentException("คะแนนความพึงพอใจต้องอยู่ระหว่าง 1 ถึง 5 ดาว");
                    }

                    // Insert or update rating
                    $this->ratingRepo->create([
                        'ticket_id'  => (int) $ticket['id'],
                        'score'      => $score,
                        'feedback'   => $feedback,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);

                    $logNote = "ผู้แจ้งยืนยันผลงานและให้คะแนนความพึงพอใจ {$score}/5 ดาว";
                    $commentBody = "ยืนยันปิดงานเรียบร้อย (คะแนน: {$score} ดาว" . ($feedback ? " - ความเห็น: {$feedback}" : "") . ")";
                    break;
            }

            // Update ticket
            $this->ticketRepo->update((int) $ticket['id'], $ticketUpdate);

            // Add comment if body or image provided
            if (!empty($commentBody) || !empty($commentImage)) {
                $this->commentRepo->create([
                    'ticket_id'  => (int) $ticket['id'],
                    'user_id'    => (int) $actor['id'],
                    'body'       => $commentBody ?: 'อัปเดตสถานะงาน',
                    'image_path' => $commentImage,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Insert status audit log
            $this->statusLogRepo->create([
                'ticket_id'   => (int) $ticket['id'],
                'changed_by'  => (int) $actor['id'],
                'from_status' => $fromStatus->value,
                'to_status'   => $toStatus->value,
                'note'        => $logNote,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        // 4. Dispatch Event (Observer Pattern)
        $updatedTicket = $this->ticketRepo->findWithDetails((int) $ticket['id']);
        $this->events->dispatch('ticket.status_changed', [
            'ticket' => $updatedTicket,
            'from'   => $fromStatus->value,
            'to'     => $toStatus->value,
            'actor'  => $actor,
            'note'   => $logNote,
        ]);

        return true;
    }

    /**
     * Validate State Machine Transition Rules
     */
    private function validateTransition(TicketStatus $from, TicketStatus $to): void
    {
        $allowedTransitions = [
            TicketStatus::Open->value => [
                TicketStatus::Assigned->value,
            ],
            TicketStatus::Assigned->value => [
                TicketStatus::InProgress->value,
            ],
            TicketStatus::InProgress->value => [
                TicketStatus::Resolved->value,
            ],
            TicketStatus::Resolved->value => [
                TicketStatus::Closed->value,     // User confirms & rates
                TicketStatus::InProgress->value, // User rejects with reason
            ],
            TicketStatus::Closed->value => [],
        ];

        $validTos = $allowedTransitions[$from->value] ?? [];
        if (!in_array($to->value, $validTos, true)) {
            throw new \DomainException("ไม่สามารถเปลี่ยนสถานะจาก '{$from->label()}' ไปเป็น '{$to->label()}' ได้ตามกฎ State Machine");
        }
    }

    /**
     * Enforce Allowed Roles according to Transition Rules Matrix
     */
    private function checkPermission(array $actor, array $ticket, TicketStatus $from, TicketStatus $to, array $extra): void
    {
        $actorRole = $actor['role'] ?? 'user';
        $actorId = (int) ($actor['id'] ?? 0);
        $ticketOwnerId = (int) ($ticket['user_id'] ?? 0);
        $assignedTechId = (int) ($ticket['technician_id'] ?? 0);

        // Admin has superuser override, but still follows workflow logic
        if ($actorRole === 'admin') {
            return;
        }

        switch ("{$from->value}->{$to->value}") {
            case 'Open->Assigned':
                // Admin only
                throw new \DomainException("เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถมอบหมายงานให้ช่างได้");

            case 'Assigned->InProgress':
                // Assigned Tech only
                if ($actorRole !== 'technician' || $actorId !== $assignedTechId) {
                    throw new \DomainException("เฉพาะช่างเทคนิคที่ได้รับมอบหมายงานนี้เท่านั้นที่สามารถกดรับงานได้");
                }
                break;

            case 'InProgress->Resolved':
                // Assigned Tech only
                if ($actorRole !== 'technician' || $actorId !== $assignedTechId) {
                    throw new \DomainException("เฉพาะช่างเทคนิคที่ดูแลงานนี้เท่านั้นที่สามารถบันทึกผลการซ่อมได้");
                }
                // Verify required repair result image
                if (empty($extra['image_path'])) {
                    throw new \InvalidArgumentException("ตามระเบียบงานซ่อม ช่างเทคนิคต้องแนบรูปภาพผลการซ่อมก่อนเปลี่ยนสถานะเป็น 'ซ่อมแซมเสร็จสิ้น'");
                }
                break;

            case 'Resolved->Closed':
            case 'Resolved->InProgress':
                // Ticket Owner only
                if ($actorId !== $ticketOwnerId) {
                    throw new \DomainException("เฉพาะเจ้าของคำขอแจ้งซ่อมเท่านั้นที่สามารถยืนยันหรือส่งแก้งานซ่อมได้");
                }
                break;

            default:
                throw new \DomainException("คุณไม่มีสิทธิ์ในการเปลี่ยนสถานะนี้");
        }
    }
}
