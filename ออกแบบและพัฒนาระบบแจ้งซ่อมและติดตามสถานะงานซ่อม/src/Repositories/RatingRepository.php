<?php

namespace App\Repositories;

class RatingRepository extends BaseRepository
{
    protected string $table = 'ratings';

    public function findByTicket(int $ticketId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM ratings WHERE ticket_id = :ticket_id LIMIT 1", ['ticket_id' => $ticketId]);
    }

    public function getOverallAverage(): float
    {
        $res = $this->db->fetchOne("SELECT AVG(score) as avg_score, COUNT(id) as total_ratings FROM ratings");
        return round((float) ($res['avg_score'] ?? 0), 1);
    }

    public function getTotalRatings(): int
    {
        $res = $this->db->fetchOne("SELECT COUNT(id) as total_ratings FROM ratings");
        return (int) ($res['total_ratings'] ?? 0);
    }
}
