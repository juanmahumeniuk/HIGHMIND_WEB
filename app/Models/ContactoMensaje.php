<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class ContactoMensaje
{
    public function guardar(string $nombre, string $email, string $mensaje): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO contacto_mensajes (nombre, email, mensaje) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nombre, $email, $mensaje]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function adminListar(): array
    {
        $stmt = Database::pdo()->query(
            'SELECT id, nombre, email, LEFT(mensaje, 200) AS mensaje_preview, creado
             FROM contacto_mensajes ORDER BY id DESC'
        );
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function adminObtener(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, nombre, email, mensaje, creado FROM contacto_mensajes WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function adminEliminar(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM contacto_mensajes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public static function esTablaAusente(PDOException $e): bool
    {
        $msg = $e->getMessage();
        return str_contains($msg, '42S02') || (str_contains($msg, '1146') && str_contains($msg, 'contacto_mensajes'));
    }
}
