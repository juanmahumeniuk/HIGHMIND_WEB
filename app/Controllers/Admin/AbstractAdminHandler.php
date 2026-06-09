<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller\AbstractController;
use App\Core\Input;
use App\Models\Contracts\AdminListable;
use App\Models\Contracts\AdminReadable;

abstract class AbstractAdminHandler extends AbstractController
{
    abstract protected function entityLabel(): string;

    /** @return AdminListable&AdminReadable */
    abstract protected function model(): AdminListable&AdminReadable;

    protected function allowsCreate(): bool
    {
        return true;
    }

    protected function allowsUpdate(): bool
    {
        return true;
    }

    protected function allowsDelete(): bool
    {
        return true;
    }

    public function handle(?int $id): void
    {
        $method = $this->method();
        $model = $this->model();

        if ($method === 'GET' && $id === null) {
            $this->jsonOk(['items' => $model->adminListar()]);
            return;
        }

        if ($method === 'GET' && $id !== null) {
            $row = $model->adminObtener($id);
            if ($row === null) {
                $this->jsonNotFound($this->entityLabel() . ' no encontrado');
                return;
            }
            $this->jsonOk(['item' => $row]);
            return;
        }

        if ($method === 'POST' && $id === null) {
            if (!$this->allowsCreate()) {
                $this->jsonError('Método no permitido', 405);
                return;
            }
            if (!$this->requirePostCsrf()) {
                return;
            }
            $this->onCreate();
            return;
        }

        if ($method === 'POST' && $id !== null) {
            if (!$this->requirePostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 24);
            if ($action === 'delete') {
                if (!$this->allowsDelete()) {
                    $this->jsonError('Acción no válida', 400);
                    return;
                }
                $this->onDelete($id);
                return;
            }
            if ($action === 'update') {
                if (!$this->allowsUpdate()) {
                    $this->jsonError('Acción no válida', 400);
                    return;
                }
                $this->onUpdate($id);
                return;
            }
            $this->jsonError('Acción no válida', 400);
            return;
        }

        $this->jsonError('Método no permitido', 405);
    }

    protected function onCreate(): void
    {
        $this->jsonError('Método no permitido', 405);
    }

    protected function onUpdate(int $id): void
    {
        $this->jsonError('Método no permitido', 405);
    }

    protected function onDelete(int $id): void
    {
        $this->jsonError('Método no permitido', 405);
    }
}
