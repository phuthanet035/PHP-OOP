<?php

namespace App\Repositories;

class CommentRepository extends BaseRepository
{
    protected string $table = 'comments';

    public function findByTicket(int $ticketId): array
    {
        $sql = "SELECT c.*, u.name as user_name, u.role as user_role, u.email as user_email
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.ticket_id = :ticket_id
                ORDER BY c.created_at ASC";

        return $this->db->fetchAll($sql, ['ticket_id' => $ticketId]);
    }
}
