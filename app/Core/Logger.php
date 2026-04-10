<?php

namespace App\Core;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

/**
 * Application Logger using Monolog
 */
class Logger
{
    private static ?MonoLogger $instance = null;

    private static function getInstance(): MonoLogger
    {
        if (self::$instance === null) {
            $config = require CONFIG_PATH . '/app.php';
            $logPath = $config['log']['path'];

            // Ensure log directory exists
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            self::$instance = new MonoLogger('capitania');
            self::$instance->pushHandler(
                new RotatingFileHandler($logPath, 14, MonoLogger::DEBUG)
            );
        }

        return self::$instance;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }
}
