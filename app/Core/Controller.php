<?php

namespace App\Core;

/**
 * Base Controller with view rendering and helpers
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        // Extract data to local scope
        extract($data);

        // Capture content
        ob_start();
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        include $viewFile;
        $content = ob_get_clean();

        // Render layout
        if ($layout !== null) {
            $layoutFile = VIEWS_PATH . '/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$layoutFile}");
            }
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($ref);
    }

    protected function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): ?string
    {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    protected function validateCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!Csrf::validate($token)) {
            $this->flash('error', 'Token de segurança inválido. Tente novamente.');
            $this->back();
            exit;
        }
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $_POST[$key] ?? null;
        }
        return $result;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function abort(int $code = 403): void
    {
        http_response_code($code);
        echo "<h1>{$code} - Acesso negado</h1>";
        exit;
    }
}
