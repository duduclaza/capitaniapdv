<?php
/**
 * Capitania PDV - Setup Completo do Banco de Dados
 *
 * Cria todas as tabelas no banco configurado no .env,
 * sem depender do schema.sql (que contém USE outra_database).
 *
 * Run: php database/install.php
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

echo "🚀 Capitania PDV — Instalação do Banco de Dados\n";
echo str_repeat("=", 55) . "\n\n";

try {
    $db = Database::getInstance();
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    echo "✅ Conexão com banco estabelecida!\n\n";

    // ===============================================================
    // CRIAR TODAS AS TABELAS
    // ===============================================================
    $tables = [

'usuarios' => "CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`        VARCHAR(150) NOT NULL,
    `email`       VARCHAR(150) NOT NULL UNIQUE,
    `senha_hash`  VARCHAR(255) NOT NULL,
    `perfil`      ENUM('admin','gerente','caixa','estoque') NOT NULL DEFAULT 'caixa',
    `ativo`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'categorias' => "CREATE TABLE IF NOT EXISTS `categorias` (
    `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`  VARCHAR(100) NOT NULL,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'produtos' => "CREATE TABLE IF NOT EXISTS `produtos` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `categoria_id`      INT UNSIGNED,
    `nome`              VARCHAR(200) NOT NULL,
    `sku`               VARCHAR(50),
    `codigo_barras`     VARCHAR(50),
    `unidade`           VARCHAR(20) NOT NULL DEFAULT 'un',
    `preco_custo`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `preco_venda`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `percent_lucro`     DECIMAL(6,3) NOT NULL DEFAULT 0.000,
    `mao_obra_valor`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `custo_energia_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `custo_agua_valor`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `custo_aluguel_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `custo_gas_valor`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `taxa_maquininha_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `taxa_governo_percent`    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `estoque_atual`     DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `estoque_minimo`    DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    `controla_estoque`  TINYINT(1) NOT NULL DEFAULT 1,
    `requer_preparo`    TINYINT(1) NOT NULL DEFAULT 0,
    `ativo`             TINYINT(1) NOT NULL DEFAULT 1,
    `imagem_blob`       MEDIUMBLOB,
    `imagem_nome`       VARCHAR(255),
    `imagem_tipo`       VARCHAR(50),
    `stripe_product_id` VARCHAR(100) DEFAULT NULL COMMENT 'Stripe Product ID',
    `stripe_price_id`   VARCHAR(100) DEFAULT NULL COMMENT 'Stripe Price ID',
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_categoria` (`categoria_id`),
    INDEX `idx_sku` (`sku`),
    INDEX `idx_codigo_barras` (`codigo_barras`),
    CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'produto_composicoes' => "CREATE TABLE IF NOT EXISTS `produto_composicoes` (
    `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `produto_id`            INT UNSIGNED NOT NULL,
    `componente_produto_id` INT UNSIGNED NOT NULL,
    `quantidade`            DECIMAL(10,3) NOT NULL DEFAULT 1.000,
    `created_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_produto_componente` (`produto_id`, `componente_produto_id`),
    INDEX `idx_componente_produto` (`componente_produto_id`),
    CONSTRAINT `fk_pc_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_componente` FOREIGN KEY (`componente_produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'clientes' => "CREATE TABLE IF NOT EXISTS `clientes` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`        VARCHAR(150) NOT NULL,
    `telefone`    VARCHAR(20),
    `documento`   VARCHAR(20),
    `observacoes` TEXT,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'fornecedores' => "CREATE TABLE IF NOT EXISTS `fornecedores` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `razao_social`  VARCHAR(200) NOT NULL,
    `nome_fantasia` VARCHAR(200),
    `telefone`      VARCHAR(20),
    `email`         VARCHAR(150),
    `cnpj`          VARCHAR(20),
    `observacoes`   TEXT,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'mesas' => "CREATE TABLE IF NOT EXISTS `mesas` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `numero`      INT UNSIGNED NOT NULL,
    `descricao`   VARCHAR(100),
    `status`      ENUM('livre','ocupada','fechada') NOT NULL DEFAULT 'livre',
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_numero` (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'comandas' => "CREATE TABLE IF NOT EXISTS `comandas` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `mesa_id`     INT UNSIGNED NOT NULL,
    `cliente_id`  INT UNSIGNED,
    `status`      ENUM('aberta','paga','cancelada') NOT NULL DEFAULT 'aberta',
    `subtotal`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `desconto`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `opened_by`   INT UNSIGNED,
    `closed_by`   INT UNSIGNED,
    `opened_at`   DATETIME,
    `closed_at`   DATETIME,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_comanda_mesa`    FOREIGN KEY (`mesa_id`)    REFERENCES `mesas`    (`id`),
    CONSTRAINT `fk_comanda_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_comanda_opened`  FOREIGN KEY (`opened_by`)  REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_comanda_closed`  FOREIGN KEY (`closed_by`)  REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'comanda_itens' => "CREATE TABLE IF NOT EXISTS `comanda_itens` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `comanda_id`      INT UNSIGNED NOT NULL,
    `produto_id`      INT UNSIGNED,
    `produto_nome`    VARCHAR(200),
    `produto_unidade` VARCHAR(20),
    `custo_unitario`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `mao_obra_unitaria` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `taxa_maquininha_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `taxa_governo_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `quantidade`      DECIMAL(10,3) NOT NULL DEFAULT 1.000,
    `preco_unitario`  DECIMAL(10,2) NOT NULL,
    `observacao`      TEXT,
    `total_item`      DECIMAL(10,2) NOT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_ci_comanda` FOREIGN KEY (`comanda_id`) REFERENCES `comandas`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ci_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'vendas' => "CREATE TABLE IF NOT EXISTS `vendas` (
    `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `comanda_id`               INT UNSIGNED,
    `cliente_id`               INT UNSIGNED,
    `valor_bruto`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `desconto`                 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_final`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status`                   ENUM('pendente','paga','cancelada') NOT NULL DEFAULT 'pendente',
    `forma_pagamento`          ENUM('dinheiro','maquininha','stripe_qr') NOT NULL,
    `subforma_pagamento`       ENUM('debito','credito','pix_maquininha','pix_stripe') DEFAULT NULL,
    `valor_recebido`           DECIMAL(10,2) DEFAULT NULL,
    `troco`                    DECIMAL(10,2) DEFAULT NULL,
    `stripe_payment_intent_id` VARCHAR(255) DEFAULT NULL,
    `stripe_payment_status`    VARCHAR(50) DEFAULT NULL,
    `qr_code_text`             TEXT DEFAULT NULL,
    `qr_code_image`            TEXT DEFAULT NULL,
    `paid_at`                  DATETIME DEFAULT NULL,
    `created_by`               INT UNSIGNED,
    `created_at`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_venda_comanda` FOREIGN KEY (`comanda_id`) REFERENCES `comandas` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_venda_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_venda_user`    FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'venda_itens' => "CREATE TABLE IF NOT EXISTS `venda_itens` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `venda_id`        INT UNSIGNED NOT NULL,
    `produto_id`      INT UNSIGNED,
    `produto_nome`    VARCHAR(200),
    `custo_unitario`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `mao_obra_unitaria` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `taxa_maquininha_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `taxa_governo_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `quantidade`      DECIMAL(10,3) NOT NULL,
    `preco_unitario`  DECIMAL(10,2) NOT NULL,
    `desconto_item`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_item`      DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_vi_venda`   FOREIGN KEY (`venda_id`)   REFERENCES `vendas`   (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vi_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'movimentacoes_estoque' => "CREATE TABLE IF NOT EXISTS `movimentacoes_estoque` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `produto_id`       INT UNSIGNED,
    `produto_nome`     VARCHAR(200),
    `tipo`             ENUM('entrada','saida','ajuste','perda') NOT NULL,
    `quantidade`       DECIMAL(10,3) NOT NULL,
    `valor_unitario`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `observacao`       TEXT,
    `usuario_id`       INT UNSIGNED,
    `referencia_tipo`  VARCHAR(50),
    `referencia_id`    INT UNSIGNED,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_produto` (`produto_id`),
    CONSTRAINT `fk_me_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos`  (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_me_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'caixa_movimentos' => "CREATE TABLE IF NOT EXISTS `caixa_movimentos` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo`        ENUM('abertura','venda','sangria','suprimento','fechamento') NOT NULL,
    `valor`       DECIMAL(10,2) NOT NULL,
    `observacao`  TEXT,
    `usuario_id`  INT UNSIGNED,
    `venda_id`    INT UNSIGNED,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_cm_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_cm_venda`   FOREIGN KEY (`venda_id`)   REFERENCES `vendas`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    ];

    foreach ($tables as $tableName => $sql) {
        try {
            $db->exec($sql);
            echo "  ✅ Tabela `{$tableName}` criada/verificada\n";
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                echo "  ⏭  Tabela `{$tableName}` já existe\n";
            } else {
                echo "  ❌ Erro em `{$tableName}`: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✅ Todas as tabelas criadas com sucesso!\n\n";

    // ===============================================================
    // SEED INICIAL
    // ===============================================================
    echo "🌱 Populando dados iniciais...\n\n";
    require __DIR__ . '/seed.php';

} catch (\Throwable $e) {
    echo "\n❌ Erro fatal: " . $e->getMessage() . "\n";
    echo "Em: " . $e->getFile() . " linha " . $e->getLine() . "\n";
    exit(1);
}

echo "\n" . str_repeat("=", 55) . "\n";
echo "🎉 Instalação concluída!\n";
echo "🔑 Login: admin@capitania.pdv / admin123\n";
echo "\n▶ Inicie o servidor:\n";
echo "   php -S 0.0.0.0:8000 -t public\n\n";
