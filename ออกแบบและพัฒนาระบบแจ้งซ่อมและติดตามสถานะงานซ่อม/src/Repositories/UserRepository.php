<?php

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = :email LIMIT 1", ['email' => $email]);
    }

    public function findTechnicians(): array
    {
        return $this->db->fetchAll("SELECT id, name, email, role, line_user_id FROM users WHERE role IN ('technician', 'admin') ORDER BY name ASC");
    }

    public function getUsersByRole(?string $role = null): array
    {
        if ($role) {
            return $this->db->fetchAll("SELECT id, name, email, role, line_user_id, created_at FROM users WHERE role = :role ORDER BY id DESC", ['role' => $role]);
        }
        return $this->db->fetchAll("SELECT id, name, email, role, line_user_id, created_at FROM users ORDER BY id DESC");
    }

    public function countByRole(): array
    {
        $rows = $this->db->fetchAll("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $counts = ['admin' => 0, 'technician' => 0, 'user' => 0];
        foreach ($rows as $row) {
            $counts[$row['role']] = (int) $row['count'];
        }
        return $counts;
    }

    public function createWithPassword(string $name, string $email, string $password, string $role = 'user', ?string $lineUserId = null): int
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        return $this->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'line_user_id' => $lineUserId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
