<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\FirebaseClient;
use App\Core\Input;
use App\Models\Usuario;

final class UsuarioAdminHandler extends AbstractAdminHandler
{
    protected function entityLabel(): string
    {
        return 'Usuario';
    }

    protected function model(): Usuario
    {
        return new Usuario();
    }

    protected function onCreate(): void
    {
        $model = $this->model();
        $email = Input::postEmail('email');
        $nombre = Input::postPlainString('nombre', 60);
        $password = Input::postPassword('password');
        $esAdmin = Input::postBit('es_admin');
        if ($email === null || strlen($nombre) < 2 || strlen($password) < 6) {
            $this->jsonError('Datos inválidos', 400);
            return;
        }
        if ($model->existeEmail($email)) {
            $this->jsonError('Email ya registrado', 400);
            return;
        }
        $fbUser = (new FirebaseClient())->signUp($email, $password);
        if ($fbUser === null) {
            $this->jsonError(
                'No se pudo crear el usuario en Firebase. Verificá FIREBASE_API_KEY y que Email/Password esté habilitado.',
                502
            );
            return;
        }
        $newId = $model->adminCrear($fbUser['uid'], $email, $nombre, $esAdmin);
        $this->jsonOk(['id' => $newId]);
    }

    protected function onUpdate(int $id): void
    {
        $model = $this->model();
        $email = Input::postEmail('email');
        $nombre = Input::postPlainString('nombre', 60);
        $esAdmin = Input::postBit('es_admin');
        if ($email === null || strlen($nombre) < 2) {
            $this->jsonError('Datos inválidos', 400);
            return;
        }
        if ($model->existeEmailExceptoId($email, $id)) {
            $this->jsonError('Email ya en uso', 400);
            return;
        }
        if ($model->adminObtener($id) === null) {
            $this->jsonNotFound('Usuario no encontrado');
            return;
        }
        $model->adminActualizarPerfil($id, $email, $nombre, $esAdmin);
        $this->jsonOk();
    }

    protected function onDelete(int $id): void
    {
        try {
            $this->model()->adminEliminar($id);
        } catch (\RuntimeException $e) {
            $this->jsonError($e->getMessage(), 400);
            return;
        }
        $this->jsonOk(['msg' => 'Usuario eliminado']);
    }
}
