<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\ContactoMensaje;
use PDOException;

final class ContactoAdminHandler extends AbstractAdminHandler
{
    protected function entityLabel(): string
    {
        return 'Mensaje';
    }

    protected function model(): ContactoMensaje
    {
        return new ContactoMensaje();
    }

    protected function allowsCreate(): bool
    {
        return false;
    }

    protected function allowsUpdate(): bool
    {
        return false;
    }

    public function handle(?int $id): void
    {
        try {
            parent::handle($id);
        } catch (PDOException $e) {
            if (ContactoMensaje::esTablaAusente($e)) {
                $this->jsonError(
                    'La tabla contacto_mensajes no existe. Ejecutá database/migrations/001_contacto_mensajes.sql',
                    503
                );
                return;
            }
            throw $e;
        }
    }

    protected function onDelete(int $id): void
    {
        try {
            if (!$this->model()->adminEliminar($id)) {
                $this->jsonNotFound('Mensaje no encontrado');
                return;
            }
        } catch (PDOException $e) {
            if (ContactoMensaje::esTablaAusente($e)) {
                $this->jsonError('La tabla contacto_mensajes no existe.', 503);
                return;
            }
            throw $e;
        }
        $this->jsonOk();
    }
}
