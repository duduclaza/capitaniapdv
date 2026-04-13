<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Produto;
use App\Models\Cliente;
use App\Services\VendaService;

class ComandaController extends Controller
{
    private Comanda $comanda;
    private Mesa $mesa;
    private Produto $produto;
    private VendaService $vendaService;

    public function __construct()
    {
        $this->comanda     = new Comanda();
        $this->mesa        = new Mesa();
        $this->produto     = new Produto();
        $this->vendaService = new VendaService();
    }

    public function index(): void
    {
        $comandas = $this->comanda->findAbertas();
        $mesas    = $this->mesa->findAll('numero');
        $clientes = (new Cliente())->findAll('nome');
        $this->view('comandas/index', compact('comandas', 'mesas', 'clientes'));
    }

    public function abrir(): void
    {
        $this->validateCsrf();
        $mesaId    = (int)$this->input('mesa_id');
        $clienteId = $this->input('cliente_id') ? (int)$this->input('cliente_id') : null;
        $user      = auth();

        // Check mesa is free
        $mesa = $this->mesa->findById($mesaId);
        if (!$mesa || $mesa['status'] !== 'livre') {
            $this->flash('error', 'Mesa não disponível.');
            $this->redirect('/comandas');
            return;
        }

        $comandaId = $this->comanda->insert([
            'mesa_id'    => $mesaId,
            'cliente_id' => $clienteId,
            'status'     => 'aberta',
            'subtotal'   => 0,
            'desconto'   => 0,
            'total'      => 0,
            'opened_by'  => $user['id'],
            'closed_by'  => null,
            'opened_at'  => now(),
            'closed_at'  => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->mesa->setStatus($mesaId, 'ocupada');

        $this->flash('success', 'Comanda aberta!');
        $this->redirect("/comandas/{$comandaId}");
    }

    public function show(string $id): void
    {
        $comanda = $this->comanda->findById((int)$id);
        if (!$comanda) {
            $this->flash('error', 'Comanda não encontrada.');
            $this->redirect('/comandas');
            return;
        }

        $itens    = $this->comanda->getItens((int)$id);
        $produtos = $this->produto->findWhere(['ativo' => 1], 'nome');
        $this->view('comandas/show', compact('comanda', 'itens', 'produtos'));
    }

    public function addItem(string $id): void
    {
        $this->validateCsrf();
        $produtoId   = (int)$this->input('produto_id');
        $quantidade  = (float)str_replace(',', '.', $this->input('quantidade', 1));
        $observacao  = $this->input('observacao', '');

        $produto = $this->produto->findById($produtoId);
        if (!$produto) {
            $this->flash('error', 'Produto não encontrado.');
            $this->redirect("/comandas/{$id}");
            return;
        }

        $this->comanda->addItem((int)$id, $produtoId, $quantidade, $produto['preco_venda'], $observacao, $produto['nome'], $produto['unidade']);
        $this->flash('success', 'Item adicionado!');
        $this->redirect("/comandas/{$id}");
    }

    public function removeItem(string $id, string $itemId): void
    {
        $this->validateCsrf();
        $this->comanda->removeItem((int)$itemId);
        $this->flash('success', 'Item removido.');
        $this->redirect("/comandas/{$id}");
    }

    public function fechar(string $id): void
    {
        $comanda = $this->comanda->findById((int)$id);
        if (!$comanda || $comanda['status'] !== 'aberta') {
            $this->flash('error', 'Comanda inválida.');
            $this->redirect('/comandas');
            return;
        }

        $itens = $this->comanda->getItens((int)$id);
        $this->view('comandas/fechar', compact('comanda', 'itens'));
    }

    public function processarFechamento(string $id): void
    {
        $this->validateCsrf();
        $user = auth();

        $pagamento = [
            'forma_pagamento'    => $this->input('forma_pagamento'),
            'subforma_pagamento' => $this->input('subforma_pagamento'),
            'valor_recebido'     => $this->input('valor_recebido') ? (float)$this->input('valor_recebido') : null,
            'troco'              => $this->input('troco') ? (float)$this->input('troco') : null,
            'desconto'           => (float)str_replace(',', '.', $this->input('desconto', 0)),
        ];

        try {
            $venda = $this->vendaService->criarVendaDeComanda((int)$id, $pagamento, $user['id']);

            if ($pagamento['forma_pagamento'] === 'stripe_qr') {
                $stripeService = new \App\Services\StripeService();
                $stripeData = $stripeService->criarPagamentoPix($venda['id'], $venda['valor_final']);
                $this->redirect("/vendas/{$venda['id']}/aguardando-pagamento");
                return;
            }

            $this->flash('success', 'Venda finalizada com sucesso!');
            $this->redirect("/vendas/{$venda['id']}");
        } catch (\Exception $e) {
            $this->flash('error', 'Erro ao fechar comanda: ' . $e->getMessage());
            $this->redirect("/comandas/{$id}/fechar");
        }
    }

    public function cancelar(string $id): void
    {
        $this->validateCsrf();
        $user = auth();
        $comanda = $this->comanda->findById((int)$id);

        if ($comanda) {
            $this->comanda->cancelar((int)$id, $user['id']);
            $this->mesa->setStatus($comanda['mesa_id'], 'livre');
        }

        $this->flash('success', 'Comanda cancelada.');
        $this->redirect('/comandas');
    }
}
