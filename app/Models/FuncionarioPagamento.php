<?php

namespace App\Models;

use App\Core\Model;

class FuncionarioPagamento extends Model
{
    protected string $table = 'funcionario_pagamentos';

    public function registrar(
        int $funcionarioId,
        string $dataInicio,
        string $dataFim,
        float $valor,
        int $paidBy,
        string $observacao = ''
    ): int {
        return $this->insert([
            'funcionario_id' => $funcionarioId,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'valor' => $valor,
            'observacao' => $observacao,
            'paid_by' => $paidBy,
            'paid_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function getTotaisPorFuncionarioPeriodo(string $dataInicio, string $dataFim): array
    {
        $rows = $this->raw(
            "SELECT funcionario_id, SUM(valor) AS total
             FROM funcionario_pagamentos
             WHERE data_inicio = ? AND data_fim = ?
             GROUP BY funcionario_id",
            [$dataInicio, $dataFim]
        );

        $totais = [];
        foreach ($rows as $row) {
            $totais[(int)$row['funcionario_id']] = (float)$row['total'];
        }

        return $totais;
    }

    public function getTotalFuncionarioPeriodo(int $funcionarioId, string $dataInicio, string $dataFim): float
    {
        return (float)$this->rawScalar(
            "SELECT COALESCE(SUM(valor), 0)
             FROM funcionario_pagamentos
             WHERE funcionario_id = ? AND data_inicio = ? AND data_fim = ?",
            [$funcionarioId, $dataInicio, $dataFim]
        );
    }

    public function getRecentes(int $limit = 10): array
    {
        return $this->raw(
            "SELECT fp.*, f.nome AS funcionario_nome, u.nome AS pago_por_nome
             FROM funcionario_pagamentos fp
             JOIN funcionarios f ON f.id = fp.funcionario_id
             LEFT JOIN usuarios u ON u.id = fp.paid_by
             ORDER BY fp.paid_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
