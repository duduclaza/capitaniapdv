<?php

return [
    'app_id'        => env('MP_APP_ID', ''),
    'client_secret' => env('MP_CLIENT_SECRET', ''),
    'redirect_uri'  => env('MP_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/config/mercadopago/callback'),
];
