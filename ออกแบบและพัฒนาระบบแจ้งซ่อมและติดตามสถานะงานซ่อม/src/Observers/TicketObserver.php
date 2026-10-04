<?php

namespace App\Observers;

use App\Notifications\NotificationChannelInterface;
use App\Notifications\LineMessagingService;

/**
 * Ticket Observer
 * Listens to ticket events and sends notifications
 * Matching Class Diagram 5 & Sequence Diagram 6.1
 */
class TicketObserver
{
    private NotificationChannelInterface $notifier;

    public function __construct(?NotificationChannelInterface $notifier = null)
    {
        $this->notifier = $notifier ?? new LineMessagingService();
    }

    public function handleCreated(array $ticket): void
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $ticketId = $ticket['id'] ?? 'N/A';
        $title = $ticket['title'] ?? 'ไม่มีหัวข้อ';
        $priority = $ticket['priority'] ?? 'Medium';
        $userName = $ticket['user_name'] ?? 'ผู้ใช้งาน';
        $categoryName = $ticket['category_name'] ?? 'ทั่วไป';

        $message = "🔔 [Smart IT Helpdesk] แจ้งซ่อมใหม่!\n"
                 . "─────────────────\n"
                 . "📌 เลขที่งาน: #{$ticketId}\n"
                 . "📝 หัวข้อ: {$title}\n"
                 . "🏷️ หมวดหมู่: {$categoryName}\n"
                 . "⚡ ความเร่งด่วน: {$priority}\n"
                 . "👤 ผู้แจ้ง: {$userName}\n"
                 . "🔗 ตรวจสอบงาน: {$appUrl}/tickets/{$ticketId}";

        // Send to group or user line id
        $recipient = $ticket['user_line_id'] ?? '';
        $this->notifier->send($recipient, $message, $ticket);
    }

    public function handleStatusChanged(array $payload): void
    {
        $ticket = $payload['ticket'] ?? [];
        $from = $payload['from'] ?? '';
        $to = $payload['to'] ?? '';
        $actor = $payload['actor'] ?? [];
        $note = $payload['note'] ?? '';

        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $ticketId = $ticket['id'] ?? 'N/A';
        $title = $ticket['title'] ?? '';
        $actorName = $actor['name'] ?? 'เจ้าหน้าที่';
        $actorRole = $actor['role'] ?? '';

        $message = "🔄 [Smart IT Helpdesk] อัปเดตสถานะงานซ่อม\n"
                 . "─────────────────\n"
                 . "📌 รหัสงาน: #{$ticketId}\n"
                 . "📝 หัวข้อ: {$title}\n"
                 . "📊 สถานะ: [{$from}] ➔ [{$to}]\n"
                 . "👨‍🔧 ดำเนินการโดย: {$actorName} ({$actorRole})\n"
                 . ($note ? "💬 บันทึก: {$note}\n" : "")
                 . "🔗 ติดตามสถานะ: {$appUrl}/tickets/{$ticketId}";

        // Target recipient: user line ID or technician line ID
        $recipient = $ticket['user_line_id'] ?? ($ticket['technician_line_id'] ?? '');
        $this->notifier->send($recipient, $message, $payload);
    }
}
