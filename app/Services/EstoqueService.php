<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\MovimentacaoEstoque;

/**
 * EstoqueService - Handles stock movements
 */
class EstoqueService
{
    private Produto $produto;
    private MovimentacaoEstoque $movimentacao;

    public function __construct()
    {
        $this->produto = new Produto();
        $this->movimentacao = new MovimentacaoEstoque();
    }

    public function entrada(int $produtoId, float $quantidade, float $valorUnitario, int $userId, string $obs = ''): void
    {
        $this->produto->incrementarEstoque($produtoId, $quantidade);
        $this->movimentacao->registrar([
            'produto_id'      => $produtoId,
            'tipo'            => 'entrada',
            'quantidade'      => $quantidade,
            'valor_unitario'  => $valorUnitario,
            'observacao'      => $obs,
            'usuario_id'      => $userId,
            'referencia_tipo' => 'manual',
            'referencia_id'   => null,
        ]);
    }

    public function saida(int $produtoId, float $quantidade, float $valorUnitario, int $userId, string $obs = ''): void
    {
        $this->produto->decrementarEstoque($produtoId, $quantidade);
        $this->movimentacao->registrar([
            'produto_id'      => $produtoId,
            'tipo'            => 'saida',
            'quantidade'      => $quantidade,
            'valor_unitario'  => $valorUnitario,
            'observacao'      => $obs,
            'usuario_id'      => $userId,
            'referencia_tipo' => 'manual',
            'referencia_id'   => null,
        ]);
    }

    public function ajuste(int $produtoId, float $novaQuantidade, int $userId, string $obs = 'Ajuste de inventário'): void
    {
        $produto = $this->produto->findById($produtoId);
        $this->produto->ajustarEstoque($produtoId, $novaQuantidade);
        $this->movimentacao->registrar([
            'produto_id'      => $produtoId,
            'tipo'            => 'ajuste',
            'quantidade'      => $novaQuantidade - ($produto['estoque_atual'] ?? 0),
            'valor_unitario'  => 0,
            'observacao'      => $obs,
            'usuario_id'      => $userId,
            'referencia_tipo' => 'manual',
            'referencia_id'   => null,
        ]);
    }

    public function perda(int $produtoId, float $quantidade, int $userId, string $obs = 'Perda/Quebra'): void
    {
        $this->produto->decrementarEstoque($produtoId, $quantidade);
        $this->movimentacao->registrar([
            'produto_id'      => $produtoId,
            'tipo'            => 'perda',
            'quantidade'      => $quantidade,
            'valor_unitario'  => 0,
            'observacao'      => $obs,
            'usuario_id'      => $userId,
            'referencia_tipo' => 'manual',
            'referencia_id'   => null,
        ]);
    }
}
