<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Produto;
use App\Models\Cliente;
use App\Services\VendaService;
use App\Services\StripeService;
use App\Models\Venda;

class PdvController extends Controller
{
    private Produto $produto;
    private VendaService $vendaService;

    public function __construct()
    {
        $this->produto     = new Produto();
        $this->vendaService = new VendaService();
    }

    public function index(): void
    {
        $clientes = (new Cliente())->findAll('nome');
        $this->view('pdv/index', ['clientes' => $clientes], 'layouts/pdv');
    }

    /**
     * Product search API for PDV
     */
    public function buscarProduto(): void
    {
        $term = $this->input('q', '');
        $produtos = $this->produto->searchForPDV($term);
        $this->json($produtos);
    }

    /**
     * Process a sale from PDV
     */
    public function finalizarVenda(): void
    {
        $this->validateCsrf();
        $user = auth();

        $itensJson = $this->input('itens', '[]');
        $itens = json_decode($itensJson, true);

        if (empty($itens)) {
            $this->json(['success' => false, 'message' => 'Carrinho vazio.'], 400);
            return;
        }

        $pagamento = [
            'forma_pagamento'    => $this->input('forma_pagamento', 'dinheiro'),
            'subforma_pagamento' => $this->input('subforma_pagamento'),
            'desconto'           => (float)str_replace(',', '.', $this->input('desconto', 0)),
            'valor_recebido'     => $this->input('valor_recebido') ? (float)$this->input('valor_recebido') : null,
            'troco'              => $this->input('troco') ? (float)$this->input('troco') : null,
        ];

        $clienteId = $this->input('cliente_id') ? (int)$this->input('cliente_id') : null;

        try {
            $venda = $this->vendaService->criarVendaPDV($itens, $pagamento, $user['id'], $clienteId);

            if ($pagamento['forma_pagamento'] === 'stripe_qr') {
                $stripe = new StripeService();
                $stripeData = $stripe->criarPagamentoPix($venda['id'], $venda['valor_final'], $itens);
                $this->json([
                    'success'           => true,
                    'venda_id'          => $venda['id'],
                    'stripe_data'       => $stripeData,
                    'awaiting_payment'  => true,
                ]);
                return;
            }

            $this->json(['success' => true, 'venda_id' => $venda['id'], 'valor_final' => $venda['valor_final']]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Check Stripe payment status
     */
    public function verificarPagamento(string $vendaId): void
    {
        $venda = (new Venda())->findById((int)$vendaId);
        if (!$venda) {
            $this->json(['status' => 'not_found'], 404);
            return;
        }

        $this->json([
            'status'     => $venda['status'],
            'paid_at'    => $venda['paid_at'],
            'stripe_status' => $venda['stripe_payment_status'],
        ]);
    }
}
