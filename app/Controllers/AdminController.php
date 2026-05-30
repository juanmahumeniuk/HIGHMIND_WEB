<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\FirebaseClient;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Core\PostCsrfGuard;
use App\Models\Carrito;
use App\Models\ContactoMensaje;
use App\Models\Producto;
use App\Models\Usuario;
use PDOException;

final class AdminController
{
    use PostCsrfGuard;

    public function handle(string $tail): void
    {
        $tail = trim($tail, '/');
        if ($tail === '') {
            JsonResponse::send(['ok' => false, 'msg' => 'Ruta admin inválida'], 404);
            return;
        }

        $segments = explode('/', $tail);
        $entity = $segments[0] ?? '';
        $id = isset($segments[1]) && ctype_digit((string) $segments[1]) ? (int) $segments[1] : null;

        if (!AdminAuth::require()) {
            return;
        }

        match ($entity) {
            'productos' => $this->productos($id),
            'usuarios' => $this->usuarios($id),
            'carrito_items' => $this->carritoItems($id),
            'contacto_mensajes' => $this->contactoMensajes($id),
            default => JsonResponse::send(['ok' => false, 'msg' => 'Recurso admin desconocido'], 404),
        };
    }

    private function productos(?int $id): void
    {
        $model = new Producto();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET' && $id === null) {
            JsonResponse::send(['ok' => true, 'items' => $model->adminListarTodos()]);
            return;
        }
        if ($method === 'GET' && $id !== null) {
            $row = $model->adminObtener($id);
            if (!$row) {
                JsonResponse::send(['ok' => false, 'msg' => 'Producto no encontrado'], 404);
                return;
            }
            JsonResponse::send(['ok' => true, 'item' => $row]);
            return;
        }

        if ($method === 'POST' && $id === null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $nombre = Input::postPlainString('nombre', 120);
            $descRaw = Input::postPlainString('descripcion', 65535);
            $descripcion = $descRaw === '' ? null : $descRaw;
            $precio = Input::postMoneyDecimal('precio');
            $stock = Input::postNonNegativeInt('stock', 0);
            $activo = Input::postBit('activo');
            if (strlen($nombre) < 2 || $precio === null) {
                JsonResponse::send(['ok' => false, 'msg' => 'Datos de producto inválidos'], 400);
                return;
            }
            $img = $this->resolveProductImageForSave(true, null);
            if ($img === null) {
                return;
            }
            $newId = $model->adminCrear($nombre, $descripcion, $precio, $img, $stock, $activo);
            JsonResponse::send(['ok' => true, 'id' => $newId]);
            return;
        }

        if ($method === 'POST' && $id !== null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 20);
            if ($action === 'delete') {
                $model->adminBajaLogica($id);
                JsonResponse::send(['ok' => true, 'msg' => 'Producto desactivado']);
                return;
            }
            if ($action !== 'update') {
                JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida'], 400);
                return;
            }
            $row = $model->adminObtener($id);
            if (!$row) {
                JsonResponse::send(['ok' => false, 'msg' => 'Producto no encontrado'], 404);
                return;
            }
            $nombre = Input::postPlainString('nombre', 120);
            $descRaw = Input::postPlainString('descripcion', 65535);
            $descripcion = $descRaw === '' ? null : $descRaw;
            $precio = Input::postMoneyDecimal('precio');
            $stock = Input::postNonNegativeInt('stock', 0);
            $activo = Input::postBit('activo');
            if (strlen($nombre) < 2 || $precio === null) {
                JsonResponse::send(['ok' => false, 'msg' => 'Datos de producto inválidos'], 400);
                return;
            }
            $existingImg = isset($row['img']) ? (string) $row['img'] : '';
            $img = $this->resolveProductImageForSave(false, $existingImg !== '' ? $existingImg : null);
            if ($img === null) {
                return;
            }
            $model->adminActualizar($id, $nombre, $descripcion, $precio, $img, $stock, $activo);
            JsonResponse::send(['ok' => true]);
            return;
        }

        JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
    }

    /**
     * Resuelve la ruta `img/...` guardada en BD: archivo subido (campo `imagen`), ruta enviada en `img`, o la existente al editar.
     *
     * @return string|null la ruta a guardar, o null si ya respondió error JSON
     */
    private function resolveProductImageForSave(bool $isCreate, ?string $existingInDb): ?string
    {
        $uploaded = $this->processProductImageUpload();
        if ($uploaded === false) {
            return null;
        }
        if (is_string($uploaded) && $uploaded !== '') {
            return $uploaded;
        }
        $posted = trim(Input::postPlainString('img', 200));
        if ($posted !== '') {
            if (str_contains($posted, '..') || str_starts_with($posted, '/')) {
                JsonResponse::send(['ok' => false, 'msg' => 'Ruta de imagen inválida'], 400);
                return null;
            }
            if (function_exists('mb_strlen') && mb_strlen($posted, 'UTF-8') > 200) {
                JsonResponse::send(['ok' => false, 'msg' => 'Ruta de imagen demasiado larga'], 400);
                return null;
            }
            return function_exists('mb_substr')
                ? mb_substr($posted, 0, 200, 'UTF-8')
                : substr($posted, 0, 200);
        }
        if (!$isCreate && $existingInDb !== null && $existingInDb !== '') {
            return $existingInDb;
        }
        JsonResponse::send([
            'ok' => false,
            'msg' => $isCreate
                ? 'Subí una imagen del producto (JPEG, PNG, WebP o GIF).'
                : 'Subí una imagen nueva o conservá la actual.',
        ], 400);
        return null;
    }

    /**
     * Procesa $_FILES['imagen'] y guarda en public_html/frontend/img/.
     *
     * @return string|false|null ruta tipo img/archivo.ext, false si hubo error (ya se envió JSON), null si no hubo archivo
     */
    private function processProductImageUpload(): string|false|null
    {
        $file = $_FILES['imagen'] ?? null;
        if (!is_array($file)) {
            return null;
        }
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($err !== UPLOAD_ERR_OK) {
            JsonResponse::send(['ok' => false, 'msg' => 'Error al subir el archivo (código ' . $err . ')'], 400);
            return false;
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            JsonResponse::send(['ok' => false, 'msg' => 'Archivo inválido'], 400);
            return false;
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > 6 * 1024 * 1024) {
            JsonResponse::send(['ok' => false, 'msg' => 'La imagen debe pesar menos de 6 MB'], 400);
            return false;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($map[$mime])) {
            JsonResponse::send(['ok' => false, 'msg' => 'Formato no permitido (JPEG, PNG, WebP o GIF)'], 400);
            return false;
        }
        $ext = $map[$mime];
        $base = dirname(APP_ROOT) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'frontend'
            . DIRECTORY_SEPARATOR . 'img';
        if (!is_dir($base) && !@mkdir($base, 0755, true)) {
            JsonResponse::send(['ok' => false, 'msg' => 'No se pudo crear la carpeta de imágenes'], 500);
            return false;
        }
        $name = 'prod_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $base . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $dest)) {
            JsonResponse::send(['ok' => false, 'msg' => 'No se pudo guardar la imagen'], 500);
            return false;
        }
        return 'img/' . $name;
    }

    private function usuarios(?int $id): void
    {
        $model = new Usuario();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET' && $id === null) {
            JsonResponse::send(['ok' => true, 'items' => $model->adminListar()]);
            return;
        }
        if ($method === 'GET' && $id !== null) {
            $row = $model->adminObtener($id);
            if (!$row) {
                JsonResponse::send(['ok' => false, 'msg' => 'Usuario no encontrado'], 404);
                return;
            }
            JsonResponse::send(['ok' => true, 'item' => $row]);
            return;
        }

        if ($method === 'POST' && $id === null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $email = Input::postEmail('email');
            $nombre = Input::postPlainString('nombre', 60);
            $password = Input::postPassword('password');
            $esAdmin = Input::postBit('es_admin');
            if ($email === null || strlen($nombre) < 2 || strlen($password) < 6) {
                JsonResponse::send(['ok' => false, 'msg' => 'Datos inválidos'], 400);
                return;
            }
            if ($model->existeEmail($email)) {
                JsonResponse::send(['ok' => false, 'msg' => 'Email ya registrado'], 400);
                return;
            }
            $firebase = new FirebaseClient();
            $fbUser = $firebase->signUp($email, $password);
            if ($fbUser === null) {
                JsonResponse::send([
                    'ok' => false,
                    'msg' => 'No se pudo crear el usuario en Firebase. Verificá FIREBASE_API_KEY y que Email/Password esté habilitado.',
                ], 502);
                return;
            }
            $newId = $model->adminCrear($fbUser['uid'], $email, $nombre, $esAdmin);
            JsonResponse::send(['ok' => true, 'id' => $newId]);
            return;
        }

        if ($method === 'POST' && $id !== null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 24);
            if ($action === 'delete') {
                try {
                    $model->adminEliminar($id);
                } catch (\RuntimeException $e) {
                    JsonResponse::send(['ok' => false, 'msg' => $e->getMessage()], 400);
                    return;
                }
                JsonResponse::send(['ok' => true, 'msg' => 'Usuario eliminado']);
                return;
            }
            if ($action !== 'update') {
                JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida'], 400);
                return;
            }
            $email = Input::postEmail('email');
            $nombre = Input::postPlainString('nombre', 60);
            $esAdmin = Input::postBit('es_admin');
            if ($email === null || strlen($nombre) < 2) {
                JsonResponse::send(['ok' => false, 'msg' => 'Datos inválidos'], 400);
                return;
            }
            if ($model->existeEmailExceptoId($email, $id)) {
                JsonResponse::send(['ok' => false, 'msg' => 'Email ya en uso'], 400);
                return;
            }
            if (!$model->adminObtener($id)) {
                JsonResponse::send(['ok' => false, 'msg' => 'Usuario no encontrado'], 404);
                return;
            }
            $model->adminActualizarPerfil($id, $email, $nombre, $esAdmin);
            JsonResponse::send(['ok' => true]);
            return;
        }

        JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
    }

    private function carritoItems(?int $id): void
    {
        $model = new Carrito();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET' && $id === null) {
            JsonResponse::send(['ok' => true, 'items' => $model->adminListarTodos()]);
            return;
        }

        if ($method === 'POST' && $id !== null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 24);
            if ($action === 'delete') {
                if (!$model->adminEliminarLinea($id)) {
                    JsonResponse::send(['ok' => false, 'msg' => 'Ítem no encontrado'], 404);
                    return;
                }
                JsonResponse::send(['ok' => true]);
                return;
            }
            if ($action === 'update') {
                $qty = Input::postNonNegativeInt('cantidad', 0);
                if ($qty < 1) {
                    JsonResponse::send(['ok' => false, 'msg' => 'Cantidad inválida'], 400);
                    return;
                }
                if (!$model->adminActualizarCantidadPorItemId($id, $qty)) {
                    JsonResponse::send(['ok' => false, 'msg' => 'Ítem no encontrado'], 404);
                    return;
                }
                JsonResponse::send(['ok' => true]);
                return;
            }
            JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida'], 400);
            return;
        }

        if ($method === 'POST' && $id === null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 24);
            if ($action !== 'vaciar_usuario') {
                JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida'], 400);
                return;
            }
            $uid = Input::postPositiveInt('usuario_id', 0);
            if ($uid < 1) {
                JsonResponse::send(['ok' => false, 'msg' => 'Usuario inválido'], 400);
                return;
            }
            $model->adminVaciarUsuario($uid);
            JsonResponse::send(['ok' => true]);
            return;
        }

        JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
    }

    private function contactoMensajes(?int $id): void
    {
        $model = new ContactoMensaje();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            if ($method === 'GET' && $id === null) {
                JsonResponse::send(['ok' => true, 'items' => $model->adminListar()]);
                return;
            }
            if ($method === 'GET' && $id !== null) {
                $row = $model->adminObtener($id);
                if (!$row) {
                    JsonResponse::send(['ok' => false, 'msg' => 'Mensaje no encontrado'], 404);
                    return;
                }
                JsonResponse::send(['ok' => true, 'item' => $row]);
                return;
            }
        } catch (PDOException $e) {
            if (ContactoMensaje::esTablaAusente($e)) {
                JsonResponse::send([
                    'ok' => false,
                    'msg' => 'La tabla contacto_mensajes no existe. Ejecutá database/migrations/001_contacto_mensajes.sql',
                ], 503);
                return;
            }
            throw $e;
        }

        if ($method === 'POST' && $id !== null) {
            if (!$this->assertPostCsrf()) {
                return;
            }
            $action = Input::postPlainString('action', 20);
            if ($action !== 'delete') {
                JsonResponse::send(['ok' => false, 'msg' => 'Acción no válida'], 400);
                return;
            }
            try {
                if (!$model->adminEliminar($id)) {
                    JsonResponse::send(['ok' => false, 'msg' => 'Mensaje no encontrado'], 404);
                    return;
                }
            } catch (PDOException $e) {
                if (ContactoMensaje::esTablaAusente($e)) {
                    JsonResponse::send([
                        'ok' => false,
                        'msg' => 'La tabla contacto_mensajes no existe.',
                    ], 503);
                    return;
                }
                throw $e;
            }
            JsonResponse::send(['ok' => true]);
            return;
        }

        JsonResponse::send(['ok' => false, 'msg' => 'Método no permitido'], 405);
    }
}
