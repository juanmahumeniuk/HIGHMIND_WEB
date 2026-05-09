<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Carrito
{
    public function agregarOIncrementar(int $usuarioId, int $productoId, int $cantidad): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT cantidad FROM carrito_items WHERE usuario_id = ? AND producto_id = ?'
        );
        $stmt->execute([$usuarioId, $productoId]);
        if ($stmt->fetch()) {
            $u = $pdo->prepare(
                'UPDATE carrito_items SET cantidad = cantidad + ? WHERE usuario_id = ? AND producto_id = ?'
            );
            $u->execute([$cantidad, $usuarioId, $productoId]);
        } else {
            $i = $pdo->prepare(
                'INSERT INTO carrito_items (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)'
            );
            $i->execute([$usuarioId, $productoId, $cantidad]);
        }
    }

    public function quitar(int $usuarioId, int $productoId): void
    {
        $stmt = Database::pdo()->prepare(
            'DELETE FROM carrito_items WHERE usuario_id = ? AND producto_id = ?'
        );
        $stmt->execute([$usuarioId, $productoId]);
    }

    public function actualizarCantidad(int $usuarioId, int $productoId, int $cantidad): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE carrito_items SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?'
        );
        $stmt->execute([$cantidad, $usuarioId, $productoId]);
    }

    public function vaciar(int $usuarioId): void
    {
        $stmt = Database::pdo()->prepare('DELETE FROM carrito_items WHERE usuario_id = ?');
        $stmt->execute([$usuarioId]);
    }

    public function obtenerCantidadItem(int $usuarioId, int $productoId): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT cantidad FROM carrito_items WHERE usuario_id = ? AND producto_id = ?'
        );
        $stmt->execute([$usuarioId, $productoId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['cantidad'] : 0;
    }

    /**
     * @return array{carrito: list<array<string, mixed>>, subtotal: float|int, total_items: int}
     */
    public function obtenerConProductos(int $usuarioId): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT ci.producto_id, ci.cantidad, p.nombre, p.precio, p.img
             FROM carrito_items ci
             JOIN productos p ON p.id = ci.producto_id
             WHERE ci.usuario_id = ?'
        );
        $stmt->execute([$usuarioId]);
        $carrito = $stmt->fetchAll();
        $subtotal = 0;
        foreach ($carrito as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }
        return [
            'carrito' => $carrito,
            'subtotal' => $subtotal,
            'total_items' => (int) array_sum(array_column($carrito, 'cantidad')),
        ];
    }
}
