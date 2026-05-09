<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\FirebaseClient;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\Session;
use App\Models\Usuario;
use PDOException;

final class FirebaseController
{
    public function handle(): void
    {
        Session::start();
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        match ($action) {
            'verify' => $this->verify(),
            default  => JsonResponse::send(['error' => 'Acción no válida'], 404),
        };
    }

    /**
     * Recibe el ID token de Firebase desde el frontend,
     * lo verifica y crea/busca el usuario en la BD, estableciendo la sesión PHP.
     */
    private function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return;
        }

        // Validar CSRF
        $csrfToken = Input::postCsrfToken();
        if ($csrfToken === '' || !Csrf::validate($csrfToken)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Token de seguridad inválido'], 403);
            return;
        }

        $idToken = trim($_POST['id_token'] ?? '');
        if ($idToken === '') {
            JsonResponse::send(['ok' => false, 'msg' => 'Token de Firebase ausente']);
            return;
        }

        // Verificar token con Firebase
        $firebase = new FirebaseClient();
        $firebaseUser = $firebase->verifyIdToken($idToken);

        if ($firebaseUser === null) {
            JsonResponse::send(['ok' => false, 'msg' => 'Token de Firebase inválido o expirado'], 401);
            return;
        }

        $uid    = $firebaseUser['uid'];
        $email  = $firebaseUser['email'];
        $nombre = $firebaseUser['name'];

        try {
            // Buscar o crear usuario en la BD
            $model   = new Usuario();
            $usuario = $model->buscarPorFirebaseUid($uid);

            if ($usuario === null) {
                // Si ya existe un usuario con ese email (registrado antes), vincularlo
                $existente = $model->buscarPorEmail($email);
                if ($existente !== null) {
                    $model->vincularFirebaseUid((int) $existente['id'], $uid);
                    $usuario = $existente;
                } else {
                    $id      = $model->crearDesdeFirebase($uid, $email, $nombre);
                    $usuario = ['id' => $id, 'nombre' => $nombre, 'email' => $email];
                }
            }

            // Establecer sesión PHP (misma estructura que usaba el login clásico)
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = (int) $usuario['id'];
            $_SESSION['usuario_nombre'] = (string) ($usuario['nombre'] ?? $nombre);
            $_SESSION['usuario_email']  = (string) ($usuario['email'] ?? $email);
        } catch (PDOException) {
            JsonResponse::send([
                'ok'  => false,
                'msg' => 'Error de base de datos al registrar el usuario. Ejecutá en MySQL la migración database/migrations/002_add_firebase_uid.sql (columna firebase_uid en usuarios).',
            ], 500);
            return;
        }

        JsonResponse::send([
            'ok'     => true,
            'nombre' => $_SESSION['usuario_nombre'],
            'email'  => $_SESSION['usuario_email'],
        ]);
    }
}
