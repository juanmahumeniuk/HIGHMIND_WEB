<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller\AbstractController;
use App\Core\Input;
use App\Models\ContactoMensaje;
use PDOException;

final class ContactoController extends AbstractController
{
    public function handle(): void
    {
        $this->startSession();
        if (!$this->requireMethod('POST')) {
            return;
        }
        if (!$this->requirePostCsrf()) {
            return;
        }

        $nombre = Input::postRegex('nombre', '/^[\p{L}\s\'-]+$/u', 120);
        $email = Input::postEmail('email');
        $mensaje = Input::postPlainString('mensaje', 2000);

        if ($nombre === null || $email === null || strlen($nombre) < 2 || strlen($mensaje) < 5) {
            $this->jsonError('Datos inválidos o formato de nombre incorrecto', 400);
            return;
        }

        try {
            (new ContactoMensaje())->guardar($nombre, $email, $mensaje);
        } catch (PDOException) {
            $this->jsonError('No se pudo guardar el mensaje. ¿Existe la tabla contacto_mensajes?', 500);
            return;
        }

        $this->jsonOk(['msg' => 'Mensaje recibido. Te responderemos pronto.']);
    }
}
