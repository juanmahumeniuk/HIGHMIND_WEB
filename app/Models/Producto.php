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
            return 0; // If column doesn't exist, assume 0 or handle accordingly
        }
    }
}
