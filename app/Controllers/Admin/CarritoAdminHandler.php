<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller\AbstractController;
use App\Core\Input;
use App\Models\Carrito;

final class CarritoAdminHandler extends AbstractController
{
    public function handle(?int $id): void
    {
        $model = new Carrito();
        $method = $this->method();

        if ($method === 'GET' && $id === null) {
            $this->jsonOk(['items' => $model->adminListar()]);
            return;
        }

        if ($method === 'POST' && $id !== null) {
            if (!$this->requirePostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 24);
            if ($action === 'delete') {
                if (!$model->adminEliminarLinea($id)) {
                    $this->jsonNotFound('Ítem no encontrado');
                    return;
                }
                $this->jsonOk();
                return;
            }
            if ($action === 'update') {
                $qty = Input::postNonNegativeInt('cantidad', 0);
                if ($qty < 1) {
                    $this->jsonError('Cantidad inválida', 400);
                    return;
                }
                if (!$model->adminActualizarCantidadPorItemId($id, $qty)) {
                    $this->jsonNotFound('Ítem no encontrado');
                    return;
                }
                $this->jsonOk();
                return;
            }
            $this->jsonError('Acción no válida', 400);
            return;
        }

        if ($method === 'POST' && $id === null) {
            if (!$this->requirePostCsrf()) {
                return;
            }
            if (Input::postPlainString('action', 24) !== 'vaciar_usuario') {
                $this->jsonError('Acción no válida', 400);
                return;
            }
            $uid = Input::postPositiveInt('usuario_id', 0);
            if ($uid < 1) {
                $this->jsonError('Usuario inválido', 400);
                return;
            }
            $model->adminVaciarUsuario($uid);
            $this->jsonOk();
            return;
        }

        $this->jsonError('Método no permitido', 405);
    }
}
