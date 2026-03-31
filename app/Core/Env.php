<?php
declare(strict_types=1);

namespace App\Core;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if ($value !== '' && ($value[0] === '"' && str_ends_with($value, '"'))) {
                $value = stripslashes(substr($value, 1, -1));
            } elseif ($value !== '' && ($value[0] === "'" && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }
        $v = getenv($key);
        return $v !== false ? $v : $default;
    }
}
