<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CaixaMovimento;
use App\Models\Venda;

class CaixaController extends Controller
{
    private CaixaMovimento $caixa;
    private Venda $venda;

    public function __construct()
    {
        $this->caixa = new CaixaMovimento();
        $this->venda = new Venda();
    }

    public function index(): void
    {
        $data      = $this->input('data', today());
        $resumo    = $this->caixa->getResumoDia($data);
        $formas    = $this->caixa->getResumoPorFormaPagamento($data);
        $movimentos = $this->caixa->getMovimentosDia($data);

        // Build totals
        $totais = ['abertura' => 0, 'venda' => 0, 'sangria' => 0, 'suprimento' => 0, 'fechamento' => 0];
        foreach ($resumo as $r) {
            $totais[$r['tipo']] = (float)$r['total'];
        }

        $this->view('caixa/index', compact('data', 'totais', 'formas', 'movimentos'));
    }

    public function abertura(): void
    {
        $this->validateCsrf();
        $user  = auth();
        $valor = (float)str_replace(',', '.', $this->input('valor', 0));
        $obs   = $this->input('observacao', 'Abertura de caixa');

        $this->caixa->registrar('abertura', $valor, $user['id'], null, $obs);
        $this->flash('success', 'Caixa aberto!');
        $this->redirect('/caixa');
    }

    public function sangria(): void
    {
        $this->validateCsrf();
        $user  = auth();
        $valor = (float)str_replace(',', '.', $this->input('valor', 0));
        $obs   = $this->input('observacao', 'Sangria');

        $this->caixa->registrar('sangria', -$valor, $user['id'], null, $obs);
        $this->flash('success', 'Sangria registrada!');
        $this->redirect('/caixa');
    }

    public function suprimento(): void
    {
        $this->validateCsrf();
        $user  = auth();
        $valor = (float)str_replace(',', '.', $this->input('valor', 0));
        $obs   = $this->input('observacao', 'Suprimento');

        $this->caixa->registrar('suprimento', $valor, $user['id'], null, $obs);
        $this->flash('success', 'Suprimento registrado!');
        $this->redirect('/caixa');
    }

    public function fechamento(): void
    {
        $this->validateCsrf();
        $user = auth();
        $obs  = $this->input('observacao', 'Fechamento de caixa');

        $resumo = $this->caixa->getResumoDia();
        $total  = array_sum(array_column($resumo, 'total'));

        $this->caixa->registrar('fechamento', $total, $user['id'], null, $obs);
        $this->flash('success', 'Caixa fechado!');
        $this->redirect('/caixa');
    }
}
