<?php
/**
 * Application Bootstrap
 * Loads environment, config, and bootstraps the app
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', BASE_PATH . '/resources/views');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Autoloader
require BASE_PATH . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Set timezone
$appConfig = require CONFIG_PATH . '/app.php';
date_default_timezone_set($appConfig['timezone']);

// Start session
session_name($appConfig['session']['name']);
session_start();

// Error handling
if ($appConfig['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Global error log handler
$isErrorHandling = false;
set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$isErrorHandling) {
    // Ignore suppressed errors (@ operator)
    if (!(error_reporting() & $errno)) return false;
    
    // Prevent infinite recursion if Logger fails
    if ($isErrorHandling) return false;
    
    $isErrorHandling = true;
    try {
        \App\Core\Logger::error("PHP Error [$errno]: $errstr in $errfile on line $errline");
    } catch (\Throwable $e) {
        // Fallback to native error log
        error_log("Logger failed during PHP Error handling: " . $e->getMessage());
    } finally {
        $isErrorHandling = false;
    }
    
    // In debug mode, we might want these to be fatal for development
    // (optional, keeping it simple for now)
    return false; // Let native handler continue
});

set_exception_handler(function (\Throwable $e) {
    try {
        \App\Core\Logger::error("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    } catch (\Throwable $logError) {
        error_log("Logger failed during Exception handling: " . $logError->getMessage());
    }

    if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<div style="background:#13111e; color:#ff4d4d; padding:30px; font-family:sans-serif; border-left: 5px solid #d946ef; height: 100vh; overflow: auto;">';
        echo '<h1 style="color:#e879f9; margin-top:0;">⚠ Erro de Aplicação (Debug Mode)</h1>';
        echo '<div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:10px; margin-bottom:20px; border: 1px solid rgba(255,255,255,0.1);">';
        echo '<strong style="display:block; margin-bottom:5px; color:#fff;">Mensagem:</strong> ' . htmlspecialchars($e->getMessage());
        echo '</div>';
        echo '<div><strong style="color:#fff;">Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</div>';
        echo '<h2 style="color:#fff; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px; margin-top:30px;">Stack Trace:</h2>';
        echo '<pre style="background:rgba(0,0,0,0.3); padding:15px; border-radius:10px; white-space:pre-wrap; font-size:13px; color:#aaa;">';
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
        echo '</div>';
    } else {
        http_response_code(500);
        if (file_exists(VIEWS_PATH . '/errors/500.php')) {
            include VIEWS_PATH . '/errors/500.php';
        } else {
            echo '500 - Internal Server Error';
        }
    }
    exit(1);
});
