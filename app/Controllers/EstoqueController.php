<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\MovimentacaoEstoque;
use App\Services\EstoqueService;

class EstoqueController extends Controller
{
    private Produto $produto;
    private MovimentacaoEstoque $movimentacao;
    private EstoqueService $estoqueService;

    public function __construct()
    {
        $this->produto        = new Produto();
        $this->movimentacao   = new MovimentacaoEstoque();
        $this->estoqueService = new EstoqueService();
    }

    public function index(): void
    {
        $produtos = $this->produto->findAllWithCategory();
        $baixo    = $this->produto->getEstoqueBaixo();
        $this->view('estoque/index', compact('produtos', 'baixo'));
    }

    public function historico(string $produtoId): void
    {
        $produto     = $this->produto->findById((int)$produtoId);
        $historico   = $this->movimentacao->getHistoricoByProduto((int)$produtoId);
        $this->view('estoque/historico', compact('produto', 'historico'));
    }

    public function formEntrada(string $produtoId): void
    {
        $produto = $this->produto->findById((int)$produtoId);
        $this->view('estoque/entrada', ['produto' => $produto, 'tipo' => 'entrada']);
    }

    public function entrada(string $produtoId): void
    {
        $this->validateCsrf();
        $user       = auth();
        $quantidade = (float)str_replace(',', '.', $this->input('quantidade', 0));
        $valor      = (float)str_replace(',', '.', $this->input('valor_unitario', 0));
        $obs        = $this->input('observacao', '');

        $this->estoqueService->entrada((int)$produtoId, $quantidade, $valor, $user['id'], $obs);
        $this->flash('success', 'Entrada de estoque registrada!');
        $this->redirect('/estoque');
    }

    public function formAjuste(string $produtoId): void
    {
        $produto = $this->produto->findById((int)$produtoId);
        $this->view('estoque/ajuste', compact('produto'));
    }

    public function ajuste(string $produtoId): void
    {
        $this->validateCsrf();
        $user           = auth();
        $novaQuantidade = (float)str_replace(',', '.', $this->input('quantidade', 0));
        $obs            = $this->input('observacao', 'Ajuste de inventário');

        $this->estoqueService->ajuste((int)$produtoId, $novaQuantidade, $user['id'], $obs);
        $this->flash('success', 'Estoque ajustado!');
        $this->redirect('/estoque');
    }

    public function formPerda(string $produtoId): void
    {
        $produto = $this->produto->findById((int)$produtoId);
        $this->view('estoque/perda', compact('produto'));
    }

    public function perda(string $produtoId): void
    {
        $this->validateCsrf();
        $user       = auth();
        $quantidade = (float)str_replace(',', '.', $this->input('quantidade', 0));
        $obs        = $this->input('observacao', 'Perda/Quebra');

        $this->estoqueService->perda((int)$produtoId, $quantidade, $user['id'], $obs);
        $this->flash('success', 'Perda registrada!');
        $this->redirect('/estoque');
    }

    public function movimentacoes(): void
    {
        $dataInicio = $this->input('data_inicio', date('Y-m-01'));
        $dataFim    = $this->input('data_fim', today());
        $historico  = $this->movimentacao->getHistoricoGeral($dataInicio, $dataFim);
        $this->view('estoque/movimentacoes', compact('historico', 'dataInicio', 'dataFim'));
    }
}
