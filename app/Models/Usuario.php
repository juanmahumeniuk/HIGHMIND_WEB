<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Usuario
{
    public function existeEmail(string $email): bool
    {
        $stmt = Database::pdo()->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    public function crear(string $email, string $nombre, string $passwordHash): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO usuarios (email, nombre, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $nombre, $passwordHash]);
    }

    /**
     * @return array{id: int, nombre: string, password: string}|null
     */
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, nombre, password FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
