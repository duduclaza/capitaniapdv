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
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    \App\Core\Logger::error("PHP Error [$errno]: $errstr in $errfile on line $errline");
});

set_exception_handler(function (\Throwable $e) {
    \App\Core\Logger::error("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    if ($_ENV['APP_DEBUG'] ?? false) {
        echo '<pre style="color:red;padding:20px;">';
        echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        echo '500 - Internal Server Error';
    }
    exit(1);
});
