<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Producto
{
    public function getActivos(): array
    {
        $pdo = Database::pdo();
        $sqlFull =
            'SELECT id, nombre, descripcion, precio, img, stock FROM productos WHERE activo = 1 ORDER BY id ASC';
        $sqlMini =
            'SELECT id, nombre, descripcion, precio, img FROM productos WHERE activo = 1 ORDER BY id ASC';

        try {
            $stmt = $pdo->prepare($sqlFull);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '1054') && str_contains($msg, 'stock')) {
                $stmt = $pdo->prepare($sqlMini);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                foreach ($rows as &$row) {
                    $row['stock'] = 0;
                }
                unset($row);
                return $rows;
            }
            throw $e;
        }
    }

    public function obtenerStock(int $id): int
    {
        $pdo = Database::pdo();
        try {
            $stmt = $pdo->prepare('SELECT stock FROM productos WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? (int) $row['stock'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function adminListarTodos(): array
    {
        $stmt = Database::pdo()->query(
            'SELECT id, nombre, descripcion, precio, img, stock, activo FROM productos ORDER BY id ASC'
        );
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function adminObtener(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, nombre, descripcion, precio, img, stock, activo FROM productos WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function adminCrear(
        string $nombre,
        ?string $descripcion,
        string $precio,
        string $img,
        int $stock,
        int $activo
    ): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO productos (nombre, descripcion, precio, img, stock, activo) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $descripcion, $precio, $img, $stock, $activo]);
        return (int) $pdo->lastInsertId();
    }

    public function adminActualizar(
        int $id,
        string $nombre,
        ?string $descripcion,
        string $precio,
        string $img,
        int $stock,
        int $activo
    ): bool {
        $stmt = Database::pdo()->prepare(
            'UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, img = ?, stock = ?, activo = ? WHERE id = ?'
        );
        return $stmt->execute([$nombre, $descripcion, $precio, $img, $stock, $activo, $id]);
    }

    public function adminBajaLogica(int $id): void
    {
        $stmt = Database::pdo()->prepare('UPDATE productos SET activo = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }
}
