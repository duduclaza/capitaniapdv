<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;

class VendaController extends Controller
{
    private Venda $venda;

    public function __construct()
    {
        $this->venda = new Venda();
    }

    public function show(string $id): void
    {
        $venda = $this->venda->findById((int)$id);
        if (!$venda) {
            $this->flash('error', 'Venda não encontrada.');
            $this->redirect('/dashboard');
            return;
        }
        $itens = $this->venda->getItens((int)$id);
        $this->view('vendas/show', compact('venda', 'itens'));
    }

    public function aguardandoPagamento(string $id): void
    {
        $venda = $this->venda->findById((int)$id);
        if (!$venda) {
            $this->redirect('/dashboard');
            return;
        }
        $this->view('vendas/aguardando_pagamento', compact('venda'));
    }

    public function imprimir(string $id): void
    {
        $venda = $this->venda->findById((int)$id);
        if (!$venda) exit('Venda não encontrada.');
        $itens = $this->venda->getItens((int)$id);
        
        // Render view without layout
        $this->view('vendas/imprimir', compact('venda', 'itens'), null);
    }
}

