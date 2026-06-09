<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Input;
use App\Core\JsonResponse;

final class ProductImageUploader
{
    /**
     * @return string|null ruta img/... o null si ya respondió error JSON
     */
    public function resolveForSave(bool $isCreate, ?string $existingInDb): ?string
    {
        $uploaded = $this->processUpload();
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

    /** @return string|false|null */
    private function processUpload(): string|false|null
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
}
