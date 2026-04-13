-- Adiciona custos opcionais, composicao de produtos e snapshots para fechamento semanal.
-- Se ainda nao rodou a migration de exclusao real, rode ela antes desta.

ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `mao_obra_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `percent_lucro`,
    ADD COLUMN IF NOT EXISTS `taxa_maquininha_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `mao_obra_valor`,
    ADD COLUMN IF NOT EXISTS `taxa_governo_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `taxa_maquininha_percent`,
    ADD COLUMN IF NOT EXISTS `requer_preparo` TINYINT(1) NOT NULL DEFAULT 0 AFTER `controla_estoque`;

CREATE TABLE IF NOT EXISTS `produto_composicoes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `comanda_itens`
    ADD COLUMN IF NOT EXISTS `produto_nome` VARCHAR(200) NULL AFTER `produto_id`;

ALTER TABLE `comanda_itens`
    ADD COLUMN IF NOT EXISTS `produto_unidade` VARCHAR(20) NULL AFTER `produto_nome`;

ALTER TABLE `comanda_itens`
    ADD COLUMN IF NOT EXISTS `custo_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `produto_unidade`,
    ADD COLUMN IF NOT EXISTS `mao_obra_unitaria` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `custo_unitario`,
    ADD COLUMN IF NOT EXISTS `taxa_maquininha_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `mao_obra_unitaria`,
    ADD COLUMN IF NOT EXISTS `taxa_governo_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `taxa_maquininha_percent`;

ALTER TABLE `venda_itens`
    ADD COLUMN IF NOT EXISTS `produto_nome` VARCHAR(200) NULL AFTER `produto_id`;

ALTER TABLE `venda_itens`
    ADD COLUMN IF NOT EXISTS `custo_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `produto_nome`,
    ADD COLUMN IF NOT EXISTS `mao_obra_unitaria` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `custo_unitario`,
    ADD COLUMN IF NOT EXISTS `taxa_maquininha_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `mao_obra_unitaria`,
    ADD COLUMN IF NOT EXISTS `taxa_governo_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `taxa_maquininha_percent`;

ALTER TABLE `movimentacoes_estoque`
    ADD COLUMN IF NOT EXISTS `produto_nome` VARCHAR(200) NULL AFTER `produto_id`;

UPDATE `comanda_itens` ci
LEFT JOIN `produtos` p ON p.id = ci.produto_id
LEFT JOIN (
    SELECT pc.produto_id, SUM(pc.quantidade * cp.preco_custo) AS custo_composicao
    FROM `produto_composicoes` pc
    JOIN `produtos` cp ON cp.id = pc.componente_produto_id
    GROUP BY pc.produto_id
) comp ON comp.produto_id = ci.produto_id
SET
    ci.produto_nome = COALESCE(ci.produto_nome, p.nome),
    ci.produto_unidade = COALESCE(ci.produto_unidade, p.unidade),
    ci.custo_unitario = COALESCE(NULLIF(ci.custo_unitario, 0), COALESCE(p.preco_custo, 0) + COALESCE(comp.custo_composicao, 0)),
    ci.mao_obra_unitaria = COALESCE(NULLIF(ci.mao_obra_unitaria, 0), COALESCE(p.mao_obra_valor, 0)),
    ci.taxa_maquininha_percent = COALESCE(NULLIF(ci.taxa_maquininha_percent, 0), COALESCE(p.taxa_maquininha_percent, 0)),
    ci.taxa_governo_percent = COALESCE(NULLIF(ci.taxa_governo_percent, 0), COALESCE(p.taxa_governo_percent, 0));

UPDATE `venda_itens` vi
LEFT JOIN `produtos` p ON p.id = vi.produto_id
LEFT JOIN (
    SELECT pc.produto_id, SUM(pc.quantidade * cp.preco_custo) AS custo_composicao
    FROM `produto_composicoes` pc
    JOIN `produtos` cp ON cp.id = pc.componente_produto_id
    GROUP BY pc.produto_id
) comp ON comp.produto_id = vi.produto_id
SET
    vi.produto_nome = COALESCE(vi.produto_nome, p.nome),
    vi.custo_unitario = COALESCE(NULLIF(vi.custo_unitario, 0), COALESCE(p.preco_custo, 0) + COALESCE(comp.custo_composicao, 0)),
    vi.mao_obra_unitaria = COALESCE(NULLIF(vi.mao_obra_unitaria, 0), COALESCE(p.mao_obra_valor, 0)),
    vi.taxa_maquininha_percent = COALESCE(NULLIF(vi.taxa_maquininha_percent, 0), COALESCE(p.taxa_maquininha_percent, 0)),
    vi.taxa_governo_percent = COALESCE(NULLIF(vi.taxa_governo_percent, 0), COALESCE(p.taxa_governo_percent, 0));

UPDATE `movimentacoes_estoque` me
LEFT JOIN `produtos` p ON p.id = me.produto_id
SET me.produto_nome = COALESCE(me.produto_nome, p.nome);
