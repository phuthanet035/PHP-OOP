<?php

namespace App\Repositories;

use App\Core\Database;

/**
 * Base Abstract Repository providing standard CRUD methods
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Database $db;
    protected string $table;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function find(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1", ['id' => $id]);
    }

    public function all(): array
    {
        return $this->db->fetchAll("SELECT * FROM `{$this->table}` ORDER BY id DESC");
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $columns = implode('`, `', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $sql = "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})";
        $this->db->execute($sql, $data);

        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['_id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = :{$key}";
            $params[$key] = $value;
        }

        $fieldStr = implode(', ', $fields);
        $sql = "UPDATE `{$this->table}` SET {$fieldStr} WHERE id = :_id";

        return $this->db->execute($sql, $params);
    }

    public function delete(int $id): bool
    {
        return $this->db->execute("DELETE FROM `{$this->table}` WHERE id = :id", ['id' => $id]);
    }
}
