<?php

namespace App\Repositories;

class CategoryRepository extends BaseRepository
{
    protected string $table = 'categories';

    public function allWithCount(): array
    {
        $sql = "SELECT c.*, COUNT(t.id) as ticket_count 
                FROM categories c 
                LEFT JOIN tickets t ON c.id = t.category_id 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        return $this->db->fetchAll($sql);
    }
}
