<?php

return [
    'name'      => $_ENV['APP_NAME'] ?? 'Capitania PDV',
    'env'       => $_ENV['APP_ENV'] ?? 'production',
    'debug'     => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'       => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'key'       => $_ENV['APP_KEY'] ?? 'change-me',
    'timezone'  => $_ENV['TIMEZONE'] ?? 'America/Sao_Paulo',
    'session'   => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
        'name'     => $_ENV['SESSION_NAME'] ?? 'capitania_session',
    ],
    'log' => [
        'channel' => $_ENV['LOG_CHANNEL'] ?? 'file',
        'level'   => $_ENV['LOG_LEVEL'] ?? 'debug',
        'path'    => dirname(__DIR__) . '/storage/logs/app.log',
    ],
];
