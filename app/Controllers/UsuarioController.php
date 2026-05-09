<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\Session;
use App\Models\Usuario;

final class UsuarioController
{
    public function handle(): void
    {
        Session::start();
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $model = new Usuario();

        match ($action) {
            'csrf' => $this->csrf(),
            'check' => $this->check($model),
            'register' => $this->register($model),
            'login' => $this->login($model),
            'logout' => $this->logout(),
            default => JsonResponse::send(['error' => 'Acción no válida']),
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
        if (isset($_SESSION['usuario_id'])) {
            $uid = (int) $_SESSION['usuario_id'];
            $esAdmin = $model->esAdminPorId($uid);
            $_SESSION['usuario_es_admin'] = $esAdmin;
            JsonResponse::send([
                'ok' => true,
                'id' => $uid,
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'es_admin' => $esAdmin,
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

    private static function hashPassword(string $plain): string
    {
        if (in_array('argon2id', password_algos(), true)) {
            return password_hash($plain, PASSWORD_ARGON2ID);
        }
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    private function register(Usuario $model): void
    {
        if (!$this->assertMutatingPostWithCsrf()) {
            return;
        }

        $email = Input::postEmail('email');
        $nombre = Input::postPlainString('nombre', 60);
        $password = Input::postPassword('password');

        if ($email === null || strlen($password) < 6 || strlen($nombre) < 2) {
            JsonResponse::send(['ok' => false, 'msg' => 'Datos inválidos']);
            return;
        }
        if ($model->existeEmail($email)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Email ya registrado']);
            return;
        }
        $model->crear($email, $nombre, self::hashPassword($password));
        JsonResponse::send(['ok' => true, 'msg' => 'Usuario registrado']);
    }

    private function login(Usuario $model): void
    {
        if (!$this->assertMutatingPostWithCsrf()) {
            return;
        }

        $email = Input::postEmail('email');
        $password = Input::postPassword('password');
        if ($email === null) {
            JsonResponse::send(['ok' => false, 'msg' => 'Email o contraseña incorrectos']);
            return;
        }

        $user = $model->buscarPorEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_email'] = $email;
            $_SESSION['usuario_es_admin'] = (int) ($user['es_admin'] ?? 0) === 1;
            JsonResponse::send([
                'ok' => true,
                'nombre' => $user['nombre'],
                'email' => $email,
                'es_admin' => (int) ($user['es_admin'] ?? 0) === 1,
            ]);
            return;
        }
        JsonResponse::send(['ok' => false, 'msg' => 'Email o contraseña incorrectos']);
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
