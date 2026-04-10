<?php
/**
 * Adiciona colunas Stripe na tabela produtos (banco já existente)
 * Run: php database/migrations/run_add_stripe.php
 */

require_once dirname(dirname(__DIR__)) . '/bootstrap/app.php';

use App\Core\Database;

echo "🔧 Adicionando colunas Stripe na tabela produtos...\n\n";

try {
    $db = Database::getInstance();

    // Verifica se as colunas já existem
    $cols = $db->query("SHOW COLUMNS FROM produtos LIKE 'stripe_product_id'")->fetchAll();

    if (!empty($cols)) {
        echo "✅ Colunas Stripe já existem na tabela produtos.\n";
        exit(0);
    }

    // Adiciona as colunas
    $db->exec("
        ALTER TABLE `produtos`
            ADD COLUMN `stripe_product_id` VARCHAR(100) DEFAULT NULL
                COMMENT 'Stripe Product ID (prod_...)' AFTER `imagem_tipo`,
            ADD COLUMN `stripe_price_id` VARCHAR(100) DEFAULT NULL
                COMMENT 'Stripe Price ID (price_...)' AFTER `stripe_product_id`
    ");

    echo "✅ Colunas adicionadas com sucesso:\n";
    echo "   - stripe_product_id VARCHAR(100)\n";
    echo "   - stripe_price_id   VARCHAR(100)\n";
    echo "\nAgora você pode criar produtos e eles serão sincronizados automaticamente com o Stripe!\n";

} catch (\Throwable $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
