<?php

/**
 * Global helper functions
 */

use App\Core\Csrf;

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('getFlash')) {
    function getFlash(string $key): ?string
    {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        $user = auth();
        return $user && in_array($user['perfil'], ['admin', 'gerente']);
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string|array $roles): bool
    {
        $user = auth();
        if (!$user) return false;
        $roles = (array) $roles;
        return in_array($user['perfil'], $roles);
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    function formatDate(string $date, string $format = 'd/m/Y H:i'): string
    {
        if (empty($date)) return '-';
        $dt = new DateTime($date);
        return $dt->format($format);
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('today')) {
    function today(): string
    {
        return date('Y-m-d');
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        return strtolower(trim($text, '-'));
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): void
    {
        echo '<pre style="background:#1a1a2e;color:#eee;padding:20px;font-size:13px;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        [$file, $subKey] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($configs[$file])) {
            $path = CONFIG_PATH . "/{$file}.php";
            $configs[$file] = file_exists($path) ? require $path : [];
        }

        if ($subKey === null) {
            return $configs[$file] ?? $default;
        }

        return $configs[$file][$subKey] ?? $default;
    }
}

if (!function_exists('isActive')) {
    function isActive(string $path): string
    {
        $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return (str_starts_with($current, $path)) ? 'active' : '';
    }
}

if (!function_exists('percentToPrice')) {
    function percentToPrice(float $cost, float $percentLucro): float
    {
        if ($percentLucro >= 100) return $cost * 2;
        return $cost / (1 - ($percentLucro / 100));
    }
}
