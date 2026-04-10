<?php

namespace App\Middleware;

/**
 * Role Middleware - Checks if user has required role
 */
class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user || !in_array($user['perfil'], $this->allowedRoles)) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }
}
