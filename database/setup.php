<?php
/**
 * Setup completo do banco de dados
 * Executa o schema.sql completo (com colunas Stripe já incluídas)
 *
 * Run: php database/setup.php
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

echo "🚀 Capitania PDV — Setup do Banco de Dados\n";
echo str_repeat("=", 50) . "\n\n";

try {
    $db = Database::getInstance();

    echo "✅ Conexão com banco estabelecida!\n\n";

    // --- Executa o schema SQL ---
    $sql = file_get_contents(__DIR__ . '/schema.sql');

    // Remove USE `database`; e CREATE DATABASE para rodar no banco já conectado
    $sql = preg_replace('/CREATE DATABASE.*?;/si', '', $sql);
    $sql = preg_replace('/USE `[^`]+`;/si', '', $sql);

    // Divide por ; e executa cada statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s) && !preg_match('/^\s*(--|#)/', trim($s)) && strlen(trim($s)) > 5
    );

    $ok = 0; $skip = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        try {
            $db->exec($stmt);
            $ok++;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            // Ignora erros de "já existe"
            if (str_contains($msg, 'already exists') || str_contains($msg, 'Duplicate')) {
                $skip++;
            } else {
                echo "  ⚠  SQL Aviso [{$e->getCode()}]: " . substr($msg, 0, 100) . "\n";
                echo "     → " . substr($stmt, 0, 80) . "\n";
            }
        }
    }

    echo "✅ Schema executado ({$ok} ok, {$skip} já existiam)\n\n";

    // --- Verifica/adiciona colunas Stripe se necessário ---
    $hasStripe = $db->query("SHOW COLUMNS FROM produtos LIKE 'stripe_product_id'")->rowCount();
    if (!$hasStripe) {
        $db->exec("ALTER TABLE `produtos`
            ADD COLUMN `stripe_product_id` VARCHAR(100) DEFAULT NULL
                COMMENT 'Stripe Product ID (prod_...)' AFTER `imagem_tipo`,
            ADD COLUMN `stripe_price_id` VARCHAR(100) DEFAULT NULL
                COMMENT 'Stripe Price ID (price_...)' AFTER `stripe_product_id`
        ");
        echo "✅ Colunas Stripe adicionadas à tabela produtos\n";
    } else {
        echo "✅ Colunas Stripe já existem\n";
    }

    // --- Roda o seed ---
    echo "\n🌱 Executando seed...\n";
    require __DIR__ . '/seed.php';

} catch (\Throwable $e) {
    echo "\n❌ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " linha " . $e->getLine() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 Setup concluído! Inicie o servidor com:\n";
echo "   php -S 0.0.0.0:8000 -t public\n";
