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

    public function esAdminPorId(int $id): bool
    {
        $stmt = Database::pdo()->prepare('SELECT COALESCE(es_admin, 0) FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $v = $stmt->fetchColumn();
        return (int) $v === 1;
    }

    public function existeEmailExceptoId(string $email, int $exceptId): bool
    {
        $stmt = Database::pdo()->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $exceptId]);
        return (bool) $stmt->fetch();
    }

    public function crear(string $email, string $nombre, string $passwordHash): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO usuarios (email, nombre, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $nombre, $passwordHash]);
    }

    public function adminCrear(string $email, string $nombre, string $passwordHash, int $esAdmin): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (email, nombre, password, es_admin) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$email, $nombre, $passwordHash, $esAdmin]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * @return array{id: int, nombre: string, password: string, es_admin: int}|null
     */
    public function buscarPorEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, nombre, password, COALESCE(es_admin, 0) AS es_admin FROM usuarios WHERE email = ?'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * @return list<array{id: int, email: string, nombre: string, es_admin: int, creado: string|null}>
     */
    public function adminListar(): array
    {
        $stmt = Database::pdo()->query(
            'SELECT id, email, nombre, COALESCE(es_admin, 0) AS es_admin, creado FROM usuarios ORDER BY id ASC'
        );
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array{id: int, email: string, nombre: string, es_admin: int, creado: string|null}|null
     */
    public function adminObtener(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, email, nombre, COALESCE(es_admin, 0) AS es_admin, creado FROM usuarios WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function adminActualizarPerfil(int $id, string $email, string $nombre, int $esAdmin): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE usuarios SET email = ?, nombre = ?, es_admin = ? WHERE id = ?'
        );
        return $stmt->execute([$email, $nombre, $esAdmin, $id]);
    }

    public function adminActualizarPassword(int $id, string $passwordHash): void
    {
        $stmt = Database::pdo()->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $id]);
    }

    /**
     * @throws \PDOException si hay ítems en carrito (integridad) u otro error de BD
     */
    public function adminEliminar(int $id): void
    {
        $pdo = Database::pdo();
        $c = $pdo->prepare('SELECT COUNT(*) FROM carrito_items WHERE usuario_id = ?');
        $c->execute([$id]);
        if ((int) $c->fetchColumn() > 0) {
            throw new \RuntimeException('El usuario tiene ítems en el carrito; vaciá el carrito antes de eliminar.');
        }
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
    }
}
