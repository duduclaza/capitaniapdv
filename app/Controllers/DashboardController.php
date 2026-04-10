<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Produto;
use App\Models\CaixaMovimento;

class DashboardController extends Controller
{
    public function index(): void
    {
        $venda = new Venda();
        $comanda = new Comanda();
        $mesa = new Mesa();
        $produto = new Produto();
        $caixa = new CaixaMovimento();

        $data = [
            'faturamento_dia'    => $venda->getFaturamentoDia(),
            'vendas_dia'         => $venda->getQuantidadeDia(),
            'comandas_abertas'   => $comanda->countAbertas(),
            'mesas_ocupadas'     => count(array_filter($mesa->findAll(), fn($m) => $m['status'] === 'ocupada')),
            'estoque_baixo'      => $produto->getEstoqueBaixo(),
            'ultimas_vendas'     => $venda->findAllWithDetails(today(), today()),
            'resumo_caixa'       => $caixa->getResumoPorFormaPagamento(),
            'faturamento_semana' => $venda->getFaturamentoPorDia(
                date('Y-m-d', strtotime('-6 days')),
                today()
            ),
        ];

        $this->view('dashboard/index', $data);
    }
}
