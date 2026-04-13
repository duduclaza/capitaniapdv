-- Adiciona custos fixos separados para precificacao de produtos.

ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `custo_energia_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `mao_obra_valor`,
    ADD COLUMN IF NOT EXISTS `custo_agua_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `custo_energia_valor`,
    ADD COLUMN IF NOT EXISTS `custo_aluguel_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `custo_agua_valor`,
    ADD COLUMN IF NOT EXISTS `custo_gas_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `custo_aluguel_valor`;
