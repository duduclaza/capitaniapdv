<?php

namespace App\Middleware;

/**
 * Auth Middleware - Redirects unauthenticated users to login
 */
class AuthMiddleware
{
    public function handle(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}
