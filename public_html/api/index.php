<?php
/**
 * Front controller (API). La aplicación MVC vive en /app, fuera del document root.
 */
declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

$path = isset($_GET['path']) ? (string) $_GET['path'] : '';
$path = trim($path, '/');
$segment = $path !== '' ? explode('/', $path, 2)[0] : '';

if ($segment === '') {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Recurso no encontrado'], JSON_UNESCAPED_UNICODE);
    exit;
}

(new \App\Core\Router())->dispatch($segment);
