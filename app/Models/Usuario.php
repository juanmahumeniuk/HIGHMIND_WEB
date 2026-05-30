<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Contracts\AdminListable;
use App\Models\Contracts\AdminReadable;

final class Usuario extends BaseModel implements AdminListable, AdminReadable
{
    public function existeEmail(string $email): bool
    {
        return $this->exists('SELECT id FROM usuarios WHERE email = ?', [$email]);
    }

    public function esAdminPorId(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(es_admin, 0) FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() === 1;
    }

    public function existeEmailExceptoId(string $email, int $exceptId): bool
    {
        return $this->exists('SELECT id FROM usuarios WHERE email = ? AND id <> ?', [$email, $exceptId]);
    }

    public function adminCrear(string $firebaseUid, string $email, string $nombre, int $esAdmin): int
    {
        return $this->insert(
            'INSERT INTO usuarios (firebase_uid, email, nombre, es_admin) VALUES (?, ?, ?, ?)',
            [$firebaseUid, $email, $nombre, $esAdmin]
        );
    }

    /** @return array{id: int, nombre: string, email: string, es_admin: int}|null */
    public function buscarPorEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, email, COALESCE(es_admin, 0) AS es_admin FROM usuarios WHERE email = ?',
            [$email]
        );
    }

    public function buscarPorFirebaseUid(string $uid): ?array
    {
        return $this->fetchOne('SELECT id, nombre, email FROM usuarios WHERE firebase_uid = ?', [$uid]);
    }

    public function buscarPorId(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre, email, COALESCE(es_admin, 0) AS es_admin FROM usuarios WHERE id = ?',
            [$id]
        );
    }

    public function adminListar(): array
    {
        return $this->queryAll(
            'SELECT id, email, nombre, COALESCE(es_admin, 0) AS es_admin, creado FROM usuarios ORDER BY id ASC'
        );
    }

    public function adminObtener(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, email, nombre, COALESCE(es_admin, 0) AS es_admin, creado FROM usuarios WHERE id = ?',
            [$id]
        );
    }

    public function crearDesdeFirebase(string $uid, string $email, string $nombre): int
    {
        return $this->insert(
            'INSERT INTO usuarios (firebase_uid, email, nombre) VALUES (?, ?, ?)',
            [$uid, $email, $nombre]
        );
    }

    public function vincularFirebaseUid(int $id, string $uid): void
    {
        $this->execute('UPDATE usuarios SET firebase_uid = ? WHERE id = ?', [$uid, $id]);
    }

    public function adminActualizarPerfil(int $id, string $email, string $nombre, int $esAdmin): bool
    {
        return $this->execute(
            'UPDATE usuarios SET email = ?, nombre = ?, es_admin = ? WHERE id = ?',
            [$email, $nombre, $esAdmin, $id]
        );
    }

    /** @throws \RuntimeException */
    public function adminEliminar(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM carrito_items WHERE usuario_id = ?');
        $stmt->execute([$id]);
        if ((int) $stmt->fetchColumn() > 0) {
            throw new \RuntimeException('El usuario tiene ítems en el carrito; vaciá el carrito antes de eliminar.');
        }
        $this->execute('DELETE FROM usuarios WHERE id = ?', [$id]);
    }
}
