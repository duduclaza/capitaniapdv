<?php

namespace App\Middleware;

/**
 * Admin-only middleware shortcut
 */
class AdminMiddleware
{
    public function handle(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || !in_array($user['perfil'], ['admin', 'gerente'])) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }
}
