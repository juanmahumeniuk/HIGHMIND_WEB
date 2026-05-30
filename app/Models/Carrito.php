<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\AdminListable;

final class Carrito extends BaseModel implements AdminListable
{
    public function agregarOIncrementar(int $usuarioId, int $productoId, int $cantidad): void
    {
        if ($this->fetchOne(
            'SELECT cantidad FROM carrito_items WHERE usuario_id = ? AND producto_id = ?',
            [$usuarioId, $productoId]
        )) {
            $this->execute(
                'UPDATE carrito_items SET cantidad = cantidad + ? WHERE usuario_id = ? AND producto_id = ?',
                [$cantidad, $usuarioId, $productoId]
            );
        } else {
            $this->execute(
                'INSERT INTO carrito_items (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)',
                [$usuarioId, $productoId, $cantidad]
            );
        }
    }

    public function quitar(int $usuarioId, int $productoId): void
    {
        $this->execute(
            'DELETE FROM carrito_items WHERE usuario_id = ? AND producto_id = ?',
            [$usuarioId, $productoId]
        );
    }

    public function actualizarCantidad(int $usuarioId, int $productoId, int $cantidad): void
    {
        $this->execute(
            'UPDATE carrito_items SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?',
            [$cantidad, $usuarioId, $productoId]
        );
    }

    public function vaciar(int $usuarioId): void
    {
        $this->execute('DELETE FROM carrito_items WHERE usuario_id = ?', [$usuarioId]);
    }

    public function obtenerCantidadItem(int $usuarioId, int $productoId): int
    {
        $row = $this->fetchOne(
            'SELECT cantidad FROM carrito_items WHERE usuario_id = ? AND producto_id = ?',
            [$usuarioId, $productoId]
        );
        return $row ? (int) $row['cantidad'] : 0;
    }

    /** @return array{carrito: list<array<string, mixed>>, subtotal: float|int, total_items: int} */
    public function obtenerConProductos(int $usuarioId): array
    {
        $carrito = $this->fetchAll(
            'SELECT ci.producto_id, ci.cantidad, p.nombre, p.precio, p.img
             FROM carrito_items ci
             JOIN productos p ON p.id = ci.producto_id
             WHERE ci.usuario_id = ?',
            [$usuarioId]
        );
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

    public function adminListar(): array
    {
        return $this->queryAll(
            'SELECT ci.id, ci.usuario_id, ci.producto_id, ci.cantidad, ci.agregado,
                    u.email AS usuario_email, u.nombre AS usuario_nombre,
                    p.nombre AS producto_nombre, p.precio AS producto_precio, p.img AS producto_img
             FROM carrito_items ci
             INNER JOIN usuarios u ON u.id = ci.usuario_id
             INNER JOIN productos p ON p.id = ci.producto_id
             ORDER BY ci.agregado DESC, ci.id DESC'
        );
    }

    public function adminActualizarCantidadPorItemId(int $itemId, int $cantidad): bool
    {
        if ($cantidad < 1) {
            return false;
        }
        $stmt = $this->pdo->prepare('UPDATE carrito_items SET cantidad = ? WHERE id = ?');
        return $stmt->execute([$cantidad, $itemId]) && $stmt->rowCount() > 0;
    }

    public function adminEliminarLinea(int $itemId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM carrito_items WHERE id = ?');
        $stmt->execute([$itemId]);
        return $stmt->rowCount() > 0;
    }

    public function adminVaciarUsuario(int $usuarioId): void
    {
        $this->execute('DELETE FROM carrito_items WHERE usuario_id = ?', [$usuarioId]);
    }
}
