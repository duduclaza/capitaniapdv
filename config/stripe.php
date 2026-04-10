<?php

return [
    'secret_key'     => $_ENV['STRIPE_SECRET_KEY'] ?? '',
    'public_key'     => $_ENV['STRIPE_PUBLIC_KEY'] ?? '',
    'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
];
