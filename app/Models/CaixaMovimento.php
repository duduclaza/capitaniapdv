<?php

namespace App\Models;

use App\Core\Model;

class CaixaMovimento extends Model
{
    protected string $table = 'caixa_movimentos';

    public function registrar(string $tipo, float $valor, ?int $userId = null, ?int $vendaId = null, string $obs = ''): int
    {
        return $this->insert([
            'tipo'       => $tipo,
            'valor'      => $valor,
            'observacao' => $obs,
            'usuario_id' => $userId,
            'venda_id'   => $vendaId,
            'created_at' => now(),
        ]);
    }

    public function getResumoDia(string $data = ''): array
    {
        $data = $data ?: today();
        return $this->raw(
            "SELECT tipo, SUM(valor) as total FROM caixa_movimentos
             WHERE DATE(created_at) = ?
             GROUP BY tipo",
            [$data]
        );
    }

    public function getResumoPorFormaPagamento(string $data = ''): array
    {
        $data = $data ?: today();
        return $this->raw(
            "SELECT v.forma_pagamento, v.subforma_pagamento, SUM(cm.valor) as total
             FROM caixa_movimentos cm
             JOIN vendas v ON v.id = cm.venda_id
             WHERE cm.tipo = 'venda' AND DATE(cm.created_at) = ?
             GROUP BY v.forma_pagamento, v.subforma_pagamento",
            [$data]
        );
    }

    public function getMovimentosDia(string $data = ''): array
    {
        $data = $data ?: today();
        return $this->raw(
            "SELECT cm.*, u.nome AS usuario_nome
             FROM caixa_movimentos cm
             LEFT JOIN usuarios u ON u.id = cm.usuario_id
             WHERE DATE(cm.created_at) = ?
             ORDER BY cm.created_at DESC",
            [$data]
        );
    }
}
