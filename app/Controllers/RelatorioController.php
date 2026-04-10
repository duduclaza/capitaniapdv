<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;
use App\Models\Produto;
use App\Models\MovimentacaoEstoque;
use App\Models\CaixaMovimento;

class RelatorioController extends Controller
{
    private Venda $venda;
    private Produto $produto;
    private MovimentacaoEstoque $movimentacao;
    private CaixaMovimento $caixa;

    public function __construct()
    {
        $this->venda       = new Venda();
        $this->produto     = new Produto();
        $this->movimentacao = new MovimentacaoEstoque();
        $this->caixa       = new CaixaMovimento();
    }

    public function vendas(): void
    {
        $dataInicio = $this->input('data_inicio', date('Y-m-01'));
        $dataFim    = $this->input('data_fim', today());
        $vendas     = $this->venda->findAllWithDetails($dataInicio, $dataFim);
        $resumo     = $this->venda->getResumoFormaPagamento($dataInicio, $dataFim);
        $grafico    = $this->venda->getFaturamentoPorDia($dataInicio, $dataFim);
        $this->view('relatorios/vendas', compact('vendas', 'resumo', 'grafico', 'dataInicio', 'dataFim'));
    }

    public function estoqueBaixo(): void
    {
        $produtos = $this->produto->getEstoqueBaixo();
        $this->view('relatorios/estoque_baixo', compact('produtos'));
    }

    public function maisVendidos(): void
    {
        $dataInicio = $this->input('data_inicio', date('Y-m-01'));
        $dataFim    = $this->input('data_fim', today());
        $produtos   = $this->produto->getMaisVendidos($dataInicio, $dataFim, 20);
        $this->view('relatorios/mais_vendidos', compact('produtos', 'dataInicio', 'dataFim'));
    }

    public function movimentacoes(): void
    {
        $dataInicio = $this->input('data_inicio', date('Y-m-01'));
        $dataFim    = $this->input('data_fim', today());
        $historico  = $this->movimentacao->getHistoricoGeral($dataInicio, $dataFim);
        $this->view('relatorios/movimentacoes', compact('historico', 'dataInicio', 'dataFim'));
    }

    public function exportarVendas(): void
    {
        $dataInicio = $this->input('data_inicio', date('Y-m-01'));
        $dataFim    = $this->input('data_fim', today());
        $vendas     = $this->venda->findAllWithDetails($dataInicio, $dataFim);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="vendas_' . $dataInicio . '_' . $dataFim . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel

        fputcsv($out, ['ID', 'Data', 'Operador', 'Cliente', 'Valor Bruto', 'Desconto', 'Valor Final', 'Forma Pagamento', 'Subforma', 'Status'], ';');

        foreach ($vendas as $v) {
            fputcsv($out, [
                $v['id'],
                formatDate($v['created_at']),
                $v['operador_nome'],
                $v['cliente_nome'] ?? '-',
                number_format($v['valor_bruto'], 2, ',', '.'),
                number_format($v['desconto'], 2, ',', '.'),
                number_format($v['valor_final'], 2, ',', '.'),
                $v['forma_pagamento'],
                $v['subforma_pagamento'] ?? '-',
                $v['status'],
            ], ';');
        }

        fclose($out);
        exit;
    }
}
