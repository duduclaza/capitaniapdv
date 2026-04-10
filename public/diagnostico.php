<?php
/**
 * Diagnóstico do ambiente — acesse via browser:
 * https://captaniabar.tiuai.com.br/diagnostico.php
 *
 * ATENÇÃO: Apague este arquivo após usar!
 */

// Proteção mínima
if (!empty($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP'] !== $_SERVER['REMOTE_ADDR']) {
    exit('Acesso não autorizado');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== CAPITANIA PDV - DIAGNÓSTICO DO SERVIDOR ===\n\n";

// PHP
echo "PHP Version   : " . PHP_VERSION . "\n";
echo "SAPI          : " . PHP_SAPI . "\n";
echo "Document Root : " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script File   : " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "Request URI   : " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "Script Name   : " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n\n";

// Paths
$base = dirname(__FILE__);
echo "Script Dir    : " . $base . "\n";
echo "Public Dir    : " . $base . "/public\n\n";

// mod_rewrite
echo "mod_rewrite   : " . (in_array('mod_rewrite', apache_get_modules() ?? [], true) ? 'ENABLED' : 'NOT FOUND / Unable to detect') . "\n\n";

// Extensions
$exts = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'fileinfo'];
echo "PHP Extensions:\n";
foreach ($exts as $ext) {
    echo "  " . str_pad($ext, 12) . " : " . (extension_loaded($ext) ? '✅ OK' : '❌ MISSING') . "\n";
}
echo "\n";

// Files check
$files = [
    '.htaccess'               => $base . '/.htaccess',
    'public/.htaccess'        => $base . '/public/.htaccess',
    'public/index.php'        => $base . '/public/index.php',
    'bootstrap/app.php'       => $base . '/bootstrap/app.php',
    '.env'                    => $base . '/.env',
    'vendor/autoload.php'     => $base . '/vendor/autoload.php',
];

echo "File Checks:\n";
foreach ($files as $label => $path) {
    $exists = file_exists($path);
    $perms  = $exists ? substr(sprintf('%o', fileperms($path)), -4) : '----';
    echo "  " . str_pad($label, 24) . " : " . ($exists ? "✅ EXISTS ($perms)" : "❌ NOT FOUND") . "\n";
}
echo "\n";

// DB connection test
echo "Database Test:\n";
try {
    $dotenv = dirname(__FILE__) . '/.env';
    if (file_exists($dotenv)) {
        $env = parse_ini_file($dotenv);
        $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_TIMEOUT => 5]);
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "  Connection    : ✅ OK\n";
        echo "  Tables found  : " . count($tables) . "\n";
        foreach ($tables as $t) echo "    - $t\n";
    } else {
        echo "  ❌ .env não encontrado\n";
    }
} catch (\Throwable $e) {
    echo "  ❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
echo "⚠️  APAGUE ESTE ARQUIVO APÓS USAR!\n";
