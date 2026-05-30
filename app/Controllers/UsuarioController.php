<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\PostCsrfGuard;
use App\Core\Session;
use App\Models\Usuario;

final class UsuarioController
{
    use PostCsrfGuard;

    public function handle(): void
    {
        Session::start();
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $model = new Usuario();

        match ($action) {
            'csrf'   => $this->csrf(),
            'check'  => $this->check($model),
            'logout' => $this->logout(),
            default  => JsonResponse::send(['error' => 'Acción no válida']),
        };
    }

    private function csrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return;
        }
        JsonResponse::send(['ok' => true, 'csrf_token' => Csrf::token()]);
    }

    private function check(Usuario $model): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
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
        if (!$this->assertPostCsrf()) {
            return;
        }
        session_destroy();
        JsonResponse::send(['ok' => true, 'msg' => 'Sesión cerrada']);
    }
}
