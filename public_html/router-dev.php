<?php
/**
 * Solo para desarrollo: php -S … -t public_html public_html/router-dev.php
 * Reproduce las rutas /api/* del .htaccess.
 */
declare(strict_types=1);

$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

if (preg_match('#^/api(?:/(.*))?$#', $uri, $m)) {
    // El servidor embebido de PHP no siempre rellena $_GET; la API usa ?action=… en GET.
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if ($qs !== '') {
        parse_str($qs, $query);
        foreach ($query as $k => $v) {
            $_GET[$k] = $v;
        }
    }
    $_GET['path'] = $m[1] ?? '';
    require __DIR__ . '/api/index.php';
    return true;
}

return false;
