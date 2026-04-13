-- Permite margem de lucro com ate 3 casas decimais.

ALTER TABLE `produtos`
    MODIFY COLUMN `percent_lucro` DECIMAL(6,3) NOT NULL DEFAULT 0.000;
