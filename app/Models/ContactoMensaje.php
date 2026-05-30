<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\AdminListable;
use App\Models\Contracts\AdminReadable;
use PDOException;

final class ContactoMensaje extends BaseModel implements AdminListable, AdminReadable
{
    public function guardar(string $nombre, string $email, string $mensaje): void
    {
        $this->execute(
            'INSERT INTO contacto_mensajes (nombre, email, mensaje) VALUES (?, ?, ?)',
            [$nombre, $email, $mensaje]
        );
    }

    public function adminListar(): array
    {
        return $this->queryAll(
            'SELECT id, nombre, email, LEFT(mensaje, 200) AS mensaje_preview, creado
             FROM contacto_mensajes ORDER BY id DESC'
        );
    }

    public function adminObtener(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, email, mensaje, creado FROM contacto_mensajes WHERE id = ?',
            [$id]
        );
    }

    public function adminEliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM contacto_mensajes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public static function esTablaAusente(PDOException $e): bool
    {
        $msg = $e->getMessage();
        return str_contains($msg, '42S02') || (str_contains($msg, '1146') && str_contains($msg, 'contacto_mensajes'));
    }
}
