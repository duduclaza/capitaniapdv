-- =======================================================
-- Migration: Adicionar colunas Stripe na tabela produtos
-- Execute este script no banco já existente
-- =======================================================

ALTER TABLE `produtos`
    ADD COLUMN IF NOT EXISTS `stripe_product_id` VARCHAR(100) DEFAULT NULL
        COMMENT 'Stripe Product ID (prod_...)' AFTER `imagem_tipo`,
    ADD COLUMN IF NOT EXISTS `stripe_price_id` VARCHAR(100) DEFAULT NULL
        COMMENT 'Stripe Price ID (price_...)' AFTER `stripe_product_id`,
    ADD INDEX IF NOT EXISTS `idx_stripe_product` (`stripe_product_id`);

SELECT 'Migration aplicada com sucesso!' AS status;
