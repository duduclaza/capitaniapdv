-- Permite excluir produtos de verdade preservando historico.
-- Rode uma vez no banco da hospedagem antes de tentar excluir produtos com historico.

ALTER TABLE `comanda_itens`
    ADD COLUMN IF NOT EXISTS `produto_nome` VARCHAR(200) NULL AFTER `produto_id`,
    ADD COLUMN IF NOT EXISTS `produto_unidade` VARCHAR(20) NULL AFTER `produto_nome`;

ALTER TABLE `venda_itens`
    ADD COLUMN IF NOT EXISTS `produto_nome` VARCHAR(200) NULL AFTER `produto_id`;

ALTER TABLE `movimentacoes_estoque`
    ADD COLUMN IF NOT EXISTS `produto_nome` VARCHAR(200) NULL AFTER `produto_id`;

UPDATE `comanda_itens` ci
LEFT JOIN `produtos` p ON p.id = ci.produto_id
SET
    ci.produto_nome = COALESCE(ci.produto_nome, p.nome),
    ci.produto_unidade = COALESCE(ci.produto_unidade, p.unidade);

UPDATE `venda_itens` vi
LEFT JOIN `produtos` p ON p.id = vi.produto_id
SET vi.produto_nome = COALESCE(vi.produto_nome, p.nome);

UPDATE `movimentacoes_estoque` me
LEFT JOIN `produtos` p ON p.id = me.produto_id
SET me.produto_nome = COALESCE(me.produto_nome, p.nome);

ALTER TABLE `comanda_itens` DROP FOREIGN KEY `fk_ci_produto`;
ALTER TABLE `venda_itens` DROP FOREIGN KEY `fk_vi_produto`;
ALTER TABLE `movimentacoes_estoque` DROP FOREIGN KEY `fk_me_produto`;

ALTER TABLE `comanda_itens` MODIFY `produto_id` INT UNSIGNED NULL;
ALTER TABLE `venda_itens` MODIFY `produto_id` INT UNSIGNED NULL;
ALTER TABLE `movimentacoes_estoque` MODIFY `produto_id` INT UNSIGNED NULL;

ALTER TABLE `comanda_itens`
    ADD CONSTRAINT `fk_ci_produto`
    FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL;

ALTER TABLE `venda_itens`
    ADD CONSTRAINT `fk_vi_produto`
    FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL;

ALTER TABLE `movimentacoes_estoque`
    ADD CONSTRAINT `fk_me_produto`
    FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL;
