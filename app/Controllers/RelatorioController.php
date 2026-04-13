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

    public function fechamentoSemanal(): void
    {
        $dataInicio = $this->input('data_inicio', date('Y-m-d', strtotime('monday this week')));
        $dataFim = $this->input('data_fim', date('Y-m-d', strtotime('sunday this week')));
        $colaboradores = max(1, (int)$this->input('colaboradores', 4));
        $diaPagamento = (int)$this->input('dia_pagamento', 5);

        if ($diaPagamento < 1 || $diaPagamento > 7) {
            $diaPagamento = 5;
        }

        $produtos = $this->venda->getFechamentoSemanal($dataInicio, $dataFim);

        $totais = [
            'quantidade' => 0.0,
            'valor_recebido' => 0.0,
            'valor_custos' => 0.0,
            'valor_taxa_maquininha' => 0.0,
            'valor_taxa_governo' => 0.0,
            'valor_mao_obra' => 0.0,
            'valor_lucro' => 0.0,
        ];

        foreach ($produtos as &$produto) {
            $produto['mao_obra_por_colaborador'] = (float)$produto['valor_mao_obra'] / $colaboradores;

            foreach ($totais as $key => $value) {
                $totais[$key] += (float)($produto[$key] ?? 0);
            }
        }
        unset($produto);

        $totais['mao_obra_por_colaborador'] = $totais['valor_mao_obra'] / $colaboradores;
        $totais['despesas'] = $totais['valor_custos']
            + $totais['valor_taxa_maquininha']
            + $totais['valor_taxa_governo']
            + $totais['valor_mao_obra'];

        $diasPagamento = [
            1 => 'Segunda-feira',
            2 => 'Terca-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sabado',
            7 => 'Domingo',
        ];
        $dataPagamento = $this->proximoDiaPagamento($dataFim, $diaPagamento);

        $this->view(
            'relatorios/fechamento_semanal',
            compact('produtos', 'totais', 'dataInicio', 'dataFim', 'colaboradores', 'diaPagamento', 'diasPagamento', 'dataPagamento')
        );
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

    private function proximoDiaPagamento(string $dataReferencia, int $diaPagamento): string
    {
        $data = new \DateTime($dataReferencia);
        $diaAtual = (int)$data->format('N');
        $diasAtePagamento = ($diaPagamento - $diaAtual + 7) % 7;
        $data->modify("+{$diasAtePagamento} days");

        return $data->format('Y-m-d');
    }
}
