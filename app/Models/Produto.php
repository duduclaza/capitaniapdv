<?php

namespace App\Models;

use App\Core\Model;

class Produto extends Model
{
    protected string $table = 'produtos';

    public function findAllWithCategory(): array
    {
        return $this->raw(
            "SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             ORDER BY p.nome ASC"
        );
    }

    public function findByIdWithCategory(int $id): ?array
    {
        return $this->rawOne(
            "SELECT p.*, c.nome AS categoria_nome
             FROM produtos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.id = ?",
            [$id]
        );
    }

    public function searchForPDV(string $term): array
    {
        return $this->raw(
            "SELECT id, nome, sku, codigo_barras, preco_venda, estoque_atual, unidade, controla_estoque,
                    (imagem_blob IS NOT NULL) AS tem_imagem
             FROM produtos
             WHERE ativo = 1 AND (nome LIKE ? OR sku LIKE ? OR codigo_barras LIKE ?)
             ORDER BY nome ASC
             LIMIT 20",
            ["%{$term}%", "%{$term}%", "%{$term}%"]
        );
    }

    public function getEstoqueBaixo(): array
    {
        return $this->raw(
            "SELECT * FROM produtos
             WHERE controla_estoque = 1 AND estoque_atual <= estoque_minimo AND ativo = 1
             ORDER BY estoque_atual ASC"
        );
    }

    public function decrementarEstoque(int $id, float $quantidade): void
    {
        $this->rawExec(
            "UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?",
            [$quantidade, $id]
        );
    }

    public function incrementarEstoque(int $id, float $quantidade): void
    {
        $this->rawExec(
            "UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?",
            [$quantidade, $id]
        );
    }

    public function ajustarEstoque(int $id, float $quantidade): void
    {
        $this->rawExec(
            "UPDATE produtos SET estoque_atual = ? WHERE id = ?",
            [$quantidade, $id]
        );
    }

    public function getImagem(int $id): ?array
    {
        return $this->rawOne(
            "SELECT imagem_blob, imagem_tipo, imagem_nome FROM produtos WHERE id = ?",
            [$id]
        );
    }

    public function getMaisVendidos(string $dataInicio, string $dataFim, int $limit = 10): array
    {
        return $this->raw(
            "SELECT COALESCE(MAX(p.id), 0) AS id,
                    COALESCE(vi.produto_nome, p.nome, 'Produto excluido') AS nome,
                    SUM(vi.quantidade) as total_vendido,
                    SUM(vi.total_item) as total_faturado
             FROM venda_itens vi
             LEFT JOIN produtos p ON p.id = vi.produto_id
             JOIN vendas v ON v.id = vi.venda_id
             WHERE v.status = 'paga' AND DATE(v.created_at) BETWEEN ? AND ?
             GROUP BY COALESCE(vi.produto_nome, p.nome, 'Produto excluido')
             ORDER BY total_vendido DESC
             LIMIT ?",
            [$dataInicio, $dataFim, $limit]
        );
    }

    public function findAllForComposition(?int $excludeId = null): array
    {
        $sql = "SELECT id, nome, unidade, preco_custo
                FROM produtos
                WHERE ativo = 1";
        $params = [];

        if ($excludeId !== null) {
            $sql .= " AND id <> ?";
            $params[] = $excludeId;
        }

        $sql .= " ORDER BY nome ASC";
        return $this->raw($sql, $params);
    }

    public function findComposicao(int $produtoId): array
    {
        return $this->raw(
            "SELECT pc.*, p.nome AS componente_nome, p.unidade, p.preco_custo
             FROM produto_composicoes pc
             JOIN produtos p ON p.id = pc.componente_produto_id
             WHERE pc.produto_id = ?
             ORDER BY p.nome ASC",
            [$produtoId]
        );
    }

    public function syncComposicao(int $produtoId, array $componentes, array $quantidades): void
    {
        $this->rawExec("DELETE FROM produto_composicoes WHERE produto_id = ?", [$produtoId]);

        foreach ($componentes as $index => $componenteId) {
            $componenteId = (int)$componenteId;
            $quantidade = (float)str_replace(',', '.', (string)($quantidades[$index] ?? 0));

            if ($componenteId <= 0 || $quantidade <= 0 || $componenteId === $produtoId) {
                continue;
            }

            $this->rawExec(
                "INSERT INTO produto_composicoes (produto_id, componente_produto_id, quantidade, created_at, updated_at)
                 VALUES (?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade), updated_at = NOW()",
                [$produtoId, $componenteId, $quantidade]
            );
        }
    }

    public function getCustosVenda(int $produtoId): array
    {
        $produto = $this->findById($produtoId);
        if (!$produto) {
            return [
                'custo_unitario' => 0.0,
                'mao_obra_unitaria' => 0.0,
                'taxa_maquininha_percent' => 0.0,
                'taxa_governo_percent' => 0.0,
            ];
        }

        $custoComposicao = (float)$this->rawScalar(
            "SELECT COALESCE(SUM(pc.quantidade * p.preco_custo), 0)
             FROM produto_composicoes pc
             JOIN produtos p ON p.id = pc.componente_produto_id
             WHERE pc.produto_id = ?",
            [$produtoId]
        );

        $custosFixos = (float)($produto['custo_energia_valor'] ?? 0)
            + (float)($produto['custo_agua_valor'] ?? 0)
            + (float)($produto['custo_aluguel_valor'] ?? 0)
            + (float)($produto['custo_gas_valor'] ?? 0);

        return [
            'custo_unitario' => (float)($produto['preco_custo'] ?? 0) + $custoComposicao + $custosFixos,
            'mao_obra_unitaria' => (float)($produto['mao_obra_valor'] ?? 0),
            'taxa_maquininha_percent' => (float)($produto['taxa_maquininha_percent'] ?? 0),
            'taxa_governo_percent' => (float)($produto['taxa_governo_percent'] ?? 0),
        ];
    }

    public function possuiVinculos(int $id): bool
    {
        $emComandas = (int) $this->rawScalar(
            "SELECT COUNT(*) FROM comanda_itens WHERE produto_id = ? LIMIT 1",
            [$id]
        );
        if ($emComandas > 0) return true;

        $emVendas = (int) $this->rawScalar(
            "SELECT COUNT(*) FROM venda_itens WHERE produto_id = ? LIMIT 1",
            [$id]
        );
        if ($emVendas > 0) return true;

        $emMovimentacoes = (int) $this->rawScalar(
            "SELECT COUNT(*) FROM movimentacoes_estoque WHERE produto_id = ? LIMIT 1",
            [$id]
        );

        return $emMovimentacoes > 0;
    }

    public function inativar(int $id): bool
    {
        return $this->rawExec(
            "UPDATE produtos SET ativo = 0, updated_at = NOW() WHERE id = ?",
            [$id]
        );
    }
}
