<?php

namespace App\Repositories;

class TicketRepository extends BaseRepository
{
    protected string $table = 'tickets';

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT t.*, 
                       u.name as user_name, u.email as user_email, u.line_user_id as user_line_id,
                       c.name as category_name,
                       tech.name as technician_name, tech.email as technician_email, tech.line_user_id as technician_line_id,
                       r.score as rating_score, r.feedback as rating_feedback, r.created_at as rating_created_at
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN categories c ON t.category_id = c.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                LEFT JOIN ratings r ON t.id = r.ticket_id
                WHERE t.id = :id
                LIMIT 1";

        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    public function findByUser(int $userId): array
    {
        $sql = "SELECT t.*, c.name as category_name, tech.name as technician_name
                FROM tickets t
                JOIN categories c ON t.category_id = c.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                WHERE t.user_id = :user_id
                ORDER BY t.id DESC";

        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    public function findByTechnician(int $techId): array
    {
        $sql = "SELECT t.*, u.name as user_name, c.name as category_name
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN categories c ON t.category_id = c.id
                WHERE t.technician_id = :tech_id
                ORDER BY t.id DESC";

        return $this->db->fetchAll($sql, ['tech_id' => $techId]);
    }

    public function search(?string $keyword = null, array $filters = []): array
    {
        $conditions = [];
        $params = [];

        if (!empty($keyword)) {
            $conditions[] = "(t.id LIKE :kw_id OR t.title LIKE :kw_title OR t.description LIKE :kw_desc OR u.name LIKE :kw_user)";
            $params['kw_id'] = "%{$keyword}%";
            $params['kw_title'] = "%{$keyword}%";
            $params['kw_desc'] = "%{$keyword}%";
            $params['kw_user'] = "%{$keyword}%";
        }

        if (!empty($filters['status'])) {
            $conditions[] = "t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $conditions[] = "t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = "t.category_id = :category_id";
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = "t.user_id = :user_id";
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['technician_id'])) {
            $conditions[] = "t.technician_id = :technician_id";
            $params['technician_id'] = (int) $filters['technician_id'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT t.*, 
                       u.name as user_name,
                       c.name as category_name,
                       tech.name as technician_name,
                       r.score as rating_score
                FROM tickets t
                JOIN users u ON t.user_id = u.id
                JOIN categories c ON t.category_id = c.id
                LEFT JOIN users tech ON t.technician_id = tech.id
                LEFT JOIN ratings r ON t.id = r.ticket_id
                {$whereClause}
                ORDER BY t.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function countByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM tickets GROUP BY status";
        $rows = $this->db->fetchAll($sql);

        $counts = [
            'Open'       => 0,
            'Assigned'   => 0,
            'InProgress' => 0,
            'Resolved'   => 0,
            'Closed'     => 0,
            'total'      => 0
        ];

        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }

        return $counts;
    }

    public function avgResolutionTime(): float
    {
        // Calculate average hours between created_at and resolved_at
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_minutes
                FROM tickets 
                WHERE resolved_at IS NOT NULL";

        $res = $this->db->fetchOne($sql);
        $minutes = (float) ($res['avg_minutes'] ?? 0);
        return round($minutes / 60, 1); // in hours
    }

    public function topTechnicians(int $limit = 5): array
    {
        $sql = "SELECT tech.id, tech.name, tech.email,
                       COUNT(t.id) as total_resolved,
                       AVG(r.score) as avg_rating
                FROM users tech
                JOIN tickets t ON tech.id = t.technician_id
                LEFT JOIN ratings r ON t.id = r.ticket_id
                WHERE tech.role IN ('technician', 'admin')
                  AND t.status IN ('Resolved', 'Closed')
                GROUP BY tech.id, tech.name, tech.email
                ORDER BY total_resolved DESC, avg_rating DESC
                LIMIT :lim";

        // Bind limit directly or via string replacement for PDO compatibility
        $limit = max(1, $limit);
        $sql = str_replace(':lim', (string) $limit, $sql);
        return $this->db->fetchAll($sql);
    }

    public function monthlyRatings(): array
    {
        $sql = "SELECT DATE_FORMAT(r.created_at, '%Y-%m') as month_str,
                       AVG(r.score) as avg_score,
                       COUNT(r.id) as count
                FROM ratings r
                GROUP BY month_str
                ORDER BY month_str DESC
                LIMIT 6";

        return array_reverse($this->db->fetchAll($sql));
    }

    public function getRecentActivity(int $limit = 8): array
    {
        $limit = max(1, $limit);
        $sql = "SELECT sl.*, u.name as user_name, u.role as user_role, t.title as ticket_title
                FROM status_logs sl
                JOIN users u ON sl.changed_by = u.id
                JOIN tickets t ON sl.ticket_id = t.id
                ORDER BY sl.created_at DESC
                LIMIT {$limit}";

        return $this->db->fetchAll($sql);
    }
}
