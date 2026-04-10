<?php

return [
    'name'      => env('APP_NAME', 'Capitania PDV'),
    'env'       => env('APP_ENV', 'production'),
    'debug'     => env('APP_DEBUG', false),
    'url'       => env('APP_URL', 'http://localhost:8000'),
    'key'       => env('APP_KEY', 'change-me'),
    'timezone'  => env('TIMEZONE', 'America/Sao_Paulo'),
    'session'   => [
        'lifetime' => (int)env('SESSION_LIFETIME', 120),
        'name'     => env('SESSION_NAME', 'capitania_session'),
    ],
    'log' => [
        'channel' => env('LOG_CHANNEL', 'file'),
        'level'   => env('LOG_LEVEL', 'debug'),
        'path'    => dirname(__DIR__) . '/storage/logs/app.log',
    ],
];
