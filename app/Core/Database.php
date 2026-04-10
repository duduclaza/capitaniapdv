<?php

namespace App\Core;

/**
 * Database - PDO Singleton connection manager
 */
class Database
{
    private static ?\PDO $instance = null;
    private static array $config = [];

    public static function connect(): \PDO
    {
        if (self::$instance === null) {
            $config = require CONFIG_PATH . '/database.php';
            self::$config = $config;

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new \PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (\PDOException $e) {
                Logger::error('Database connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection failed. Check your .env configuration.');
            }
        }

        return self::$instance;
    }

    public static function getInstance(): \PDO
    {
        return self::connect();
    }
}
