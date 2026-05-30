<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\AdminListable;
use App\Models\Contracts\AdminReadable;

final class Producto extends BaseModel implements AdminListable, AdminReadable
{
    public function getActivos(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre, descripcion, precio, img, stock FROM productos WHERE activo = 1 ORDER BY id ASC'
        );
    }

    public function obtenerStock(int $id): int
    {
        $row = $this->fetchOne('SELECT stock FROM productos WHERE id = ?', [$id]);
        return $row ? (int) $row['stock'] : 0;
    }

    public function adminListar(): array
    {
        return $this->queryAll(
            'SELECT id, nombre, descripcion, precio, img, stock, activo FROM productos ORDER BY id ASC'
        );
    }

    public function adminObtener(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, descripcion, precio, img, stock, activo FROM productos WHERE id = ?',
            [$id]
        );
    }

    public function adminCrear(
        string $nombre,
        ?string $descripcion,
        string $precio,
        string $img,
        int $stock,
        int $activo
    ): int {
        return $this->insert(
            'INSERT INTO productos (nombre, descripcion, precio, img, stock, activo) VALUES (?, ?, ?, ?, ?, ?)',
            [$nombre, $descripcion, $precio, $img, $stock, $activo]
        );
    }

    public function adminActualizar(
        int $id,
        string $nombre,
        ?string $descripcion,
        string $precio,
        string $img,
        int $stock,
        int $activo
    ): void {
        $this->execute(
            'UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, img = ?, stock = ?, activo = ? WHERE id = ?',
            [$nombre, $descripcion, $precio, $img, $stock, $activo, $id]
        );
    }

    public function adminBajaLogica(int $id): void
    {
        $this->execute('UPDATE productos SET activo = 0 WHERE id = ?', [$id]);
    }
}
