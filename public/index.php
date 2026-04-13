<?php
/**
 * Public entry point
 * Works on both:
 *   - Shared hosting (doc root = project root, /public/ exposed by .htaccess)
 *   - VPS/local (doc root = /public directly)
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Router;

$router = new Router();

// Load routes
require BASE_PATH . '/routes/web.php';

// Get URI and strip query string
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /public prefix if it appears in the URI
// (happens on some shared hosting when mod_rewrite strips it partially)
$uri = preg_replace('#^/public#', '', $uri);

// Ensure URI is at least "/"
if (empty($uri)) {
    $uri = '/';
}

// Normalize trailing slashes so /config/ resolves to the /config route.
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
