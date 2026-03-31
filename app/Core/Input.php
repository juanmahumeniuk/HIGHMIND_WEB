<?php
declare(strict_types=1);

namespace App\Core;

final class Input
{
    public static function postPlainString(string $key, int $maxLen): string
    {
        $v = $_POST[$key] ?? '';
        if (!is_string($v)) {
            return '';
        }
        $v = strip_tags($v);
        $v = trim($v);
        if ($maxLen < 1) {
            return '';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($v, 0, $maxLen, 'UTF-8');
        }
        return substr($v, 0, $maxLen);
    }

    public static function postEmail(string $key): ?string
    {
        $raw = $_POST[$key] ?? '';
        $v = strtolower(trim(is_string($raw) ? $raw : ''));
        if ($v === '') {
            return null;
        }
        return filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : null;
    }

    public static function postPassword(string $key): string
    {
        $v = $_POST[$key] ?? '';
        return is_string($v) ? $v : '';
    }

    public static function postPositiveInt(string $key, int $fallback = 0): int
    {
        $raw = $_POST[$key] ?? $fallback;
        $v = filter_var($raw, FILTER_VALIDATE_INT);
        if ($v === false || $v < 1) {
            return $fallback;
        }
        return $v;
    }

    /** Token hex (64 chars); no mutar con strip_tags. */
    public static function postCsrfToken(): string
    {
        $v = $_POST['csrf_token'] ?? '';
        if (!is_string($v)) {
            return '';
        }
        $v = trim($v);
        if (strlen($v) > 128 || strlen($v) < 32) {
            return '';
        }
        if (!ctype_xdigit($v)) {
            return '';
        }
        return $v;
    }
}
