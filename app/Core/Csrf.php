<?php

namespace App\Core;

/**
 * CSRF protection token manager
 */
class Csrf
{
    public static function generate(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(string $token): bool
    {
        $expected = $_SESSION['csrf_token'] ?? '';
        return hash_equals($expected, $token);
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token) . '">';
    }

    public static function token(): string
    {
        return self::generate();
    }
}
