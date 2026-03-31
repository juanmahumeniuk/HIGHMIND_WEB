<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ContactoMensaje
{
    public function guardar(string $nombre, string $email, string $mensaje): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO contacto_mensajes (nombre, email, mensaje) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nombre, $email, $mensaje]);
    }
}
