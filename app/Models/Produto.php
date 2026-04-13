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
            "SELECT id, nome, sku, codigo_barras, preco_venda, estoque_atual, unidade, controla_estoque
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
            "SELECT p.id, p.nome, SUM(vi.quantidade) as total_vendido, SUM(vi.total_item) as total_faturado
             FROM venda_itens vi
             JOIN produtos p ON p.id = vi.produto_id
             JOIN vendas v ON v.id = vi.venda_id
             WHERE v.status = 'paga' AND DATE(v.created_at) BETWEEN ? AND ?
             GROUP BY p.id, p.nome
             ORDER BY total_vendido DESC
             LIMIT ?",
            [$dataInicio, $dataFim, $limit]
        );
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
