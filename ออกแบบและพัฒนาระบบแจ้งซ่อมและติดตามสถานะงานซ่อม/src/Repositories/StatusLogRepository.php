<?php

namespace App\Repositories;

class StatusLogRepository extends BaseRepository
{
    protected string $table = 'status_logs';

    public function findByTicket(int $ticketId): array
    {
        $sql = "SELECT sl.*, u.name as user_name, u.role as user_role 
                FROM status_logs sl
                JOIN users u ON sl.changed_by = u.id
                WHERE sl.ticket_id = :ticket_id
                ORDER BY sl.created_at ASC";

        return $this->db->fetchAll($sql, ['ticket_id' => $ticketId]);
    }
}
