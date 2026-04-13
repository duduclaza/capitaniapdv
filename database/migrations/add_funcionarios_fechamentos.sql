-- Adiciona cadastro de funcionarios e baixas de pagamento por fechamento.

CREATE TABLE IF NOT EXISTS `funcionarios` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`       VARCHAR(150) NOT NULL,
    `cargo`      VARCHAR(100),
    `telefone`   VARCHAR(30),
    `ativo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `funcionario_pagamentos` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `funcionario_id` INT UNSIGNED NOT NULL,
    `data_inicio`    DATE NOT NULL,
    `data_fim`       DATE NOT NULL,
    `valor`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `observacao`     TEXT,
    `paid_by`        INT UNSIGNED,
    `paid_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_funcionario_periodo` (`funcionario_id`, `data_inicio`, `data_fim`),
    CONSTRAINT `fk_fp_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fp_usuario`     FOREIGN KEY (`paid_by`)        REFERENCES `usuarios`     (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
