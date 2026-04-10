<?php

namespace App\Models;

use App\Core\Model;

class MovimentacaoEstoque extends Model
{
    protected string $table = 'movimentacoes_estoque';

    public function registrar(array $data): int
    {
        $data['created_at'] = now();
        return $this->insert($data);
    }

    public function getHistoricoByProduto(int $produtoId): array
    {
        return $this->raw(
            "SELECT me.*, u.nome AS usuario_nome
             FROM movimentacoes_estoque me
             LEFT JOIN usuarios u ON u.id = me.usuario_id
             WHERE me.produto_id = ?
             ORDER BY me.created_at DESC",
            [$produtoId]
        );
    }

    public function getHistoricoGeral(string $dataInicio = '', string $dataFim = ''): array
    {
        $sql = "SELECT me.*, p.nome AS produto_nome, u.nome AS usuario_nome
                FROM movimentacoes_estoque me
                JOIN produtos p ON p.id = me.produto_id
                LEFT JOIN usuarios u ON u.id = me.usuario_id";

        $params = [];
        if ($dataInicio && $dataFim) {
            $sql .= " WHERE DATE(me.created_at) BETWEEN ? AND ?";
            $params = [$dataInicio, $dataFim];
        }

        $sql .= " ORDER BY me.created_at DESC";
        return $this->raw($sql, $params);
    }
}
