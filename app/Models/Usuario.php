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
     * @return array{id: int, nombre: string, email: string, password: string}|null
     */
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, nombre, email, password FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array{id: int, nombre: string, email: string}|null */
    public function buscarPorFirebaseUid(string $uid): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, nombre, email FROM usuarios WHERE firebase_uid = ?');
        $stmt->execute([$uid]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function crearDesdeFirebase(string $uid, string $email, string $nombre): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO usuarios (firebase_uid, email, nombre) VALUES (?, ?, ?)'
        );
        $stmt->execute([$uid, $email, $nombre]);
        return (int) Database::pdo()->lastInsertId();
    }

    public function vincularFirebaseUid(int $id, string $uid): void
    {
        $stmt = Database::pdo()->prepare('UPDATE usuarios SET firebase_uid = ? WHERE id = ?');
        $stmt->execute([$uid, $id]);
    }
}
