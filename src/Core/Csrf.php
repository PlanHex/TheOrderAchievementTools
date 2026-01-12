<?php
namespace Core;

class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(16));
        }

        return $_SESSION[self::KEY];
    }

    public static function input(): string
    {
        $t = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($token)) return false;
        return hash_equals($_SESSION[self::KEY] ?? '', $token);
    }
}
