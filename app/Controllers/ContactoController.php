<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\Session;
use App\Models\ContactoMensaje;
use PDOException;

final class ContactoController
{
    public function handle(): void
    {
        Session::start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
            return;
        }
        $csrf = Input::postCsrfToken();
        if ($csrf === '' || !Csrf::validate($csrf)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Token de seguridad inválido'], 403);
            return;
        }

        // Validar formato del nombre: solo letras, espacios, apóstrofes y guiones
        $nombre = Input::postRegex('nombre', '/^[\p{L}\s\'-]+$/u', 120);
        $email = Input::postEmail('email');
        // El mensaje ya es sanitizado con htmlspecialchars en postPlainString
        $mensaje = Input::postPlainString('mensaje', 2000);

        if ($nombre === null || $email === null || strlen($nombre) < 2 || strlen($mensaje) < 5) {
            JsonResponse::send(['ok' => false, 'msg' => 'Datos inválidos o formato de nombre incorrecto'], 400);
            return;
        }

        try {
            (new ContactoMensaje())->guardar($nombre, $email, $mensaje);
        } catch (PDOException) {
            JsonResponse::send(['ok' => false, 'msg' => 'No se pudo guardar el mensaje. ¿Existe la tabla contacto_mensajes?'], 500);
            return;
        }

        JsonResponse::send(['ok' => true, 'msg' => 'Mensaje recibido. Te responderemos pronto.']);
    }
}
