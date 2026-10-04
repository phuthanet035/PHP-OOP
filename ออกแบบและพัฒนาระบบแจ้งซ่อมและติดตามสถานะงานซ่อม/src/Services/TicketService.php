<?php

namespace App\Services;

use App\Core\Database;
use App\Core\EventDispatcher;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Repositories\TicketRepository;
use App\Repositories\CommentRepository;
use App\Repositories\StatusLogRepository;

/**
 * Ticket Service for Ticket creation and lifecycle management
 * Matching Sequence Diagram 6.1
 */
class TicketService
{
    private TicketRepository $ticketRepo;
    private CommentRepository $commentRepo;
    private StatusLogRepository $statusLogRepo;
    private FileUploader $uploader;
    private EventDispatcher $events;
    private Database $db;

    public function __construct(
        ?TicketRepository $ticketRepo = null,
        ?CommentRepository $commentRepo = null,
        ?StatusLogRepository $statusLogRepo = null,
        ?FileUploader $uploader = null,
        ?EventDispatcher $events = null,
        ?Database $db = null
    ) {
        $this->ticketRepo = $ticketRepo ?? new TicketRepository();
        $this->commentRepo = $commentRepo ?? new CommentRepository();
        $this->statusLogRepo = $statusLogRepo ?? new StatusLogRepository();
        $this->uploader = $uploader ?? new FileUploader();
        $this->events = $events ?? EventDispatcher::getInstance();
        $this->db = $db ?? Database::getInstance();
    }

    public function createTicket(array $data, ?array $file, array $actor): int
    {
        $this->db->beginTransaction();

        try {
            // Priority enum fallback
            $priority = TicketPriority::tryFrom($data['priority'] ?? 'Medium') ?? TicketPriority::Medium;

            // Handle optional image upload
            $uploadedImage = null;
            if ($file && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                $uploadedImage = $this->uploader->upload($file, 'ticket_problem');
            }

            // 1. Insert ticket
            $ticketId = $this->ticketRepo->create([
                'user_id'       => (int) $actor['id'],
                'category_id'   => (int) $data['category_id'],
                'technician_id' => null,
                'title'         => trim($data['title']),
                'description'   => trim($data['description']),
                'status'        => TicketStatus::Open->value,
                'priority'      => $priority->value,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            // 2. Insert initial status log
            $this->statusLogRepo->create([
                'ticket_id'   => $ticketId,
                'changed_by'  => (int) $actor['id'],
                'from_status' => TicketStatus::Open->value,
                'to_status'   => TicketStatus::Open->value,
                'note'        => 'สร้างคำขอแจ้งซ่อมเข้าระบบสำเร็จ',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            // 3. If image was attached, add as initial comment
            if ($uploadedImage) {
                $this->commentRepo->create([
                    'ticket_id'  => $ticketId,
                    'user_id'    => (int) $actor['id'],
                    'body'       => 'แนบรูปภาพประกอบอาการเสียเริ่มต้น',
                    'image_path' => $uploadedImage,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        // 4. Dispatch Event (ticket.created)
        $ticketWithDetails = $this->ticketRepo->findWithDetails($ticketId);
        $this->events->dispatch('ticket.created', $ticketWithDetails);

        return $ticketId;
    }

    public function addComment(int $ticketId, string $body, ?array $file, array $actor): int
    {
        $uploadedImage = null;
        if ($file && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadedImage = $this->uploader->upload($file, 'comment');
        }

        if (empty(trim($body)) && empty($uploadedImage)) {
            throw new \InvalidArgumentException("กรุณากรอกข้อความหรือแนบรูปภาพ");
        }

        $commentId = $this->commentRepo->create([
            'ticket_id'  => $ticketId,
            'user_id'    => (int) $actor['id'],
            'body'       => trim($body) ?: 'แนบรูปภาพเพิ่มเติม',
            'image_path' => $uploadedImage,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $commentId;
    }
}
