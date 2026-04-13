<?php

namespace App\Models;

use App\Core\Model;

class Venda extends Model
{
    protected string $table = 'vendas';

    public function findAllWithDetails(string $dataInicio = '', string $dataFim = ''): array
    {
        $sql = "SELECT v.*, u.nome AS operador_nome, cli.nome AS cliente_nome
                FROM vendas v
                LEFT JOIN usuarios u ON u.id = v.created_by
                LEFT JOIN clientes cli ON cli.id = v.cliente_id";

        $params = [];
        if ($dataInicio && $dataFim) {
            $sql .= " WHERE DATE(v.created_at) BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql .= " ORDER BY v.created_at DESC";
        return $this->raw($sql, $params);
    }

    public function getItens(int $vendaId): array
    {
        return $this->raw(
            "SELECT vi.*,
                    COALESCE(vi.produto_nome, p.nome, 'Produto excluido') AS produto_nome,
                    COALESCE(vi.produto_nome, p.nome, 'Produto excluido') AS nome
             FROM venda_itens vi
             LEFT JOIN produtos p ON p.id = vi.produto_id
             WHERE vi.venda_id = ?",
            [$vendaId]
        );
    }

    public function getFaturamentoDia(string $data = ''): float
    {
        $data = $data ?: today();
        return (float) $this->rawScalar(
            "SELECT COALESCE(SUM(valor_final), 0) FROM vendas WHERE status = 'paga' AND DATE(created_at) = ?",
            [$data]
        );
    }

    public function getQuantidadeDia(string $data = ''): int
    {
        $data = $data ?: today();
        return (int) $this->rawScalar(
            "SELECT COUNT(*) FROM vendas WHERE status = 'paga' AND DATE(created_at) = ?",
            [$data]
        );
    }

    public function getResumoFormaPagamento(string $dataInicio, string $dataFim): array
    {
        return $this->raw(
            "SELECT forma_pagamento, subforma_pagamento, SUM(valor_final) as total, COUNT(*) as quantidade
             FROM vendas
             WHERE status = 'paga' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY forma_pagamento, subforma_pagamento
             ORDER BY total DESC",
            [$dataInicio, $dataFim]
        );
    }

    public function updateStripeStatus(string $paymentIntentId, string $status, ?string $paidAt = null): void
    {
        $this->rawExec(
            "UPDATE vendas SET stripe_payment_status = ?, paid_at = ?, status = 'paga', updated_at = NOW()
             WHERE stripe_payment_intent_id = ?",
            [$status, $paidAt, $paymentIntentId]
        );
    }

    public function findByPaymentIntent(string $paymentIntentId): ?array
    {
        return $this->rawOne(
            "SELECT * FROM vendas WHERE stripe_payment_intent_id = ?",
            [$paymentIntentId]
        );
    }

    public function addItem(
        int $vendaId,
        ?int $produtoId,
        float $quantidade,
        float $preco,
        float $desconto = 0,
        ?string $produtoNome = null,
        float $custoUnitario = 0,
        float $maoObraUnitaria = 0,
        float $taxaMaquininhaPercent = 0,
        float $taxaGovernoPercent = 0
    ): int
    {
        $totalItem = ($quantidade * $preco) - $desconto;
        $this->rawExec(
            "INSERT INTO venda_itens (
                venda_id, produto_id, produto_nome, quantidade, preco_unitario, desconto_item, total_item,
                custo_unitario, mao_obra_unitaria, taxa_maquininha_percent, taxa_governo_percent
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $vendaId,
                $produtoId,
                $produtoNome,
                $quantidade,
                $preco,
                $desconto,
                $totalItem,
                $custoUnitario,
                $maoObraUnitaria,
                $taxaMaquininhaPercent,
                $taxaGovernoPercent,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function getFechamentoSemanal(string $dataInicio, string $dataFim): array
    {
        return $this->raw(
            "SELECT
                COALESCE(vi.produto_nome, p.nome, 'Produto excluido') AS produto_nome,
                SUM(vi.quantidade) AS quantidade,
                SUM(vi.total_item) AS valor_recebido,
                SUM(vi.quantidade * COALESCE(vi.custo_unitario, 0)) AS valor_custos,
                SUM(vi.quantidade * COALESCE(vi.mao_obra_unitaria, 0)) AS valor_mao_obra,
                SUM(vi.total_item * COALESCE(vi.taxa_maquininha_percent, 0) / 100) AS valor_taxa_maquininha,
                SUM(vi.total_item * COALESCE(vi.taxa_governo_percent, 0) / 100) AS valor_taxa_governo,
                SUM(
                    vi.total_item
                    - (vi.quantidade * COALESCE(vi.custo_unitario, 0))
                    - (vi.quantidade * COALESCE(vi.mao_obra_unitaria, 0))
                    - (vi.total_item * COALESCE(vi.taxa_maquininha_percent, 0) / 100)
                    - (vi.total_item * COALESCE(vi.taxa_governo_percent, 0) / 100)
                ) AS valor_lucro
             FROM venda_itens vi
             JOIN vendas v ON v.id = vi.venda_id
             LEFT JOIN produtos p ON p.id = vi.produto_id
             WHERE v.status = 'paga' AND DATE(v.created_at) BETWEEN ? AND ?
             GROUP BY COALESCE(vi.produto_nome, p.nome, 'Produto excluido')
             ORDER BY valor_recebido DESC",
            [$dataInicio, $dataFim]
        );
    }

    public function getFaturamentoPorDia(string $dataInicio, string $dataFim): array
    {
        return $this->raw(
            "SELECT DATE(created_at) as data, SUM(valor_final) as total, COUNT(*) as quantidade
             FROM vendas
             WHERE status = 'paga' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY data ASC",
            [$dataInicio, $dataFim]
        );
    }
}
