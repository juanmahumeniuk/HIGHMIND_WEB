<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\Session;

final class UsuarioController
{
    public function handle(): void
    {
        Session::start();
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        match ($action) {
            'csrf'   => $this->csrf(),
            'check'  => $this->check(),
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

    private function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return;
        }
        if (isset($_SESSION['usuario_id'])) {
            JsonResponse::send([
                'ok' => true,
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
            ]);
            return;
        }
        JsonResponse::send(['ok' => false]);
    }

    private function assertMutatingPostWithCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return false;
        }
        $token = Input::postCsrfToken();
        if ($token === '' || !Csrf::validate($token)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Token de seguridad inválido'], 403);
            return false;
        }
        return true;
    }

    private function logout(): void
    {
        if (!$this->assertMutatingPostWithCsrf()) {
            return;
        }
        session_destroy();
        JsonResponse::send(['ok' => true, 'msg' => 'Sesión cerrada']);
    }
}
