<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    /** @return array<string, mixed>|null */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    protected function insert(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    protected function exists(string $sql, array $params): bool
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }

    /** @return list<array<string, mixed>> */
    protected function queryAll(string $sql): array
    {
        $stmt = $this->pdo->query($sql);
        return $stmt ? ($stmt->fetchAll() ?: []) : [];
    }
}
