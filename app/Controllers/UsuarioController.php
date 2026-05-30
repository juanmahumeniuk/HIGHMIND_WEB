<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller\AbstractController;
use App\Core\Csrf;
use App\Core\JsonResponse;
use App\Models\Usuario;

final class UsuarioController extends AbstractController
{
    public function handle(): void
    {
        $this->startSession();
        $model = new Usuario();

        match ($this->action()) {
            'csrf'   => $this->csrf(),
            'check'  => $this->check($model),
            'logout' => $this->logout(),
            default  => $this->jsonError('Acción no válida', 404),
        };
    }

    private function csrf(): void
    {
        if (!$this->requireMethod('GET')) {
            return;
        }
        $this->jsonOk(['csrf_token' => Csrf::token()]);
    }

    private function check(Usuario $model): void
    {
        if (!$this->requireMethod('GET')) {
            return;
        }
        if (!isset($_SESSION['usuario_id'])) {
            JsonResponse::send(['ok' => false]);
            return;
        }

        $uid = (int) $_SESSION['usuario_id'];
        $usuario = $model->buscarPorId($uid);
        if ($usuario === null) {
            session_destroy();
            JsonResponse::send(['ok' => false]);
            return;
        }

        $esAdmin = (int) $usuario['es_admin'] === 1;
        $_SESSION['usuario_nombre'] = (string) $usuario['nombre'];
        $_SESSION['usuario_email'] = (string) $usuario['email'];
        $_SESSION['usuario_es_admin'] = $esAdmin;

        JsonResponse::send([
            'ok' => true,
            'id' => $uid,
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'es_admin' => $esAdmin,
        ]);
    }

    private function logout(): void
    {
        if (!$this->requirePostCsrf()) {
            return;
        }
        session_destroy();
        $this->jsonOk(['msg' => 'Sesión cerrada']);
    }
}
