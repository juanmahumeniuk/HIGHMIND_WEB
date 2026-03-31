<?php
declare(strict_types=1);

$appRoot = __DIR__;
define('APP_ROOT', $appRoot);

require_once APP_ROOT . '/Core/Env.php';
\App\Core\Env::load(dirname(APP_ROOT) . '/.env');

require_once APP_ROOT . '/Core/Https.php';
\App\Core\Https::requireIfEnabled();

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = APP_ROOT . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
