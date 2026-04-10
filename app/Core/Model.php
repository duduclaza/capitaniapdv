<?php

namespace App\Core;

/**
 * Base Model with common CRUD operations via PDO
 */
abstract class Model
{
    protected \PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$dir}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findWhere(array $conditions, string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $where = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY `{$orderBy}` {$dir}"
        );
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    public function findOneWhere(array $conditions): ?array
    {
        $where = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1"
        );
        $stmt->execute(array_values($conditions));
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function insert(array $data): int
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET {$set} WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([$id]);
    }

    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}`");
            $stmt->execute();
        } else {
            $where = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE {$where}");
            $stmt->execute(array_values($conditions));
        }
        return (int) $stmt->fetchColumn();
    }

    public function raw(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function rawOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function rawScalar(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function rawExec(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollBack();
    }
}
