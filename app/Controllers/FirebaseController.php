<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller\AbstractController;
use App\Core\FirebaseClient;
use App\Core\Input;
use App\Models\Usuario;
use PDOException;

final class FirebaseController extends AbstractController
{
    public function handle(): void
    {
        $this->startSession();

        match ($this->action()) {
            'verify' => $this->verify(),
            default  => $this->jsonError('Acción no válida', 404),
        };
    }

    private function verify(): void
    {
        if (!$this->requireMethod('POST')) {
            return;
        }
        if (!$this->requirePostCsrf()) {
            return;
        }

        $idToken = trim($_POST['id_token'] ?? '');
        if ($idToken === '') {
            $this->jsonError('Token de Firebase ausente');
            return;
        }

        $firebaseUser = (new FirebaseClient())->verifyIdToken($idToken);
        if ($firebaseUser === null) {
            $this->jsonError('Token de Firebase inválido o expirado', 401);
            return;
        }

        $uid = $firebaseUser['uid'];
        $email = $firebaseUser['email'];
        $nombre = $firebaseUser['name'];

        try {
            $model = new Usuario();
            $usuario = $model->buscarPorFirebaseUid($uid);

            if ($usuario === null) {
                $existente = $model->buscarPorEmail($email);
                if ($existente !== null) {
                    $model->vincularFirebaseUid((int) $existente['id'], $uid);
                    $usuario = $existente;
                } else {
                    $id = $model->crearDesdeFirebase($uid, $email, $nombre);
                    $usuario = ['id' => $id, 'nombre' => $nombre, 'email' => $email];
                }
            }

            session_regenerate_id(true);
            $uidSession = (int) $usuario['id'];
            $_SESSION['usuario_id'] = $uidSession;
            $_SESSION['usuario_nombre'] = (string) ($usuario['nombre'] ?? $nombre);
            $_SESSION['usuario_email'] = (string) ($usuario['email'] ?? $email);
            $_SESSION['usuario_es_admin'] = $model->esAdminPorId($uidSession);
        } catch (PDOException) {
            $this->jsonError(
                'Error de base de datos al registrar el usuario. Verificá que la tabla usuarios tenga la columna firebase_uid.',
                500
            );
            return;
        }

        $this->jsonOk([
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email'],
        ]);
    }
}
