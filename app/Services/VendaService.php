<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\MovimentacaoEstoque;
use App\Models\CaixaMovimento;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Core\Database;

/**
 * VendaService - Handles complete sale processing
 */
class VendaService
{
    private Venda $vendaModel;
    private Produto $produtoModel;
    private MovimentacaoEstoque $estoque;
    private CaixaMovimento $caixa;
    private Comanda $comanda;
    private Mesa $mesa;

    public function __construct()
    {
        $this->vendaModel = new Venda();
        $this->produtoModel = new Produto();
        $this->estoque = new MovimentacaoEstoque();
        $this->caixa = new CaixaMovimento();
        $this->comanda = new Comanda();
        $this->mesa = new Mesa();
    }

    /**
     * Cria uma venda a partir de itens do carrinho PDV
     */
    public function criarVendaPDV(array $itens, array $pagamento, int $usuarioId, ?int $clienteId = null): array
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Calcular totais
            $valorBruto = 0;
            foreach ($itens as $item) {
                $valorBruto += $item['quantidade'] * $item['preco_unitario'];
            }

            $desconto = (float)($pagamento['desconto'] ?? 0);
            $valorFinal = $valorBruto - $desconto;

            // Criar venda
            $vendaId = $this->vendaModel->insert([
                'comanda_id'            => null,
                'cliente_id'            => $clienteId,
                'valor_bruto'           => $valorBruto,
                'desconto'              => $desconto,
                'valor_final'           => $valorFinal,
                'status'                => 'pendente',
                'forma_pagamento'       => $pagamento['forma_pagamento'],
                'subforma_pagamento'    => $pagamento['subforma_pagamento'] ?? null,
                'valor_recebido'        => $pagamento['valor_recebido'] ?? null,
                'troco'                 => $pagamento['troco'] ?? null,
                'stripe_payment_intent_id' => null,
                'stripe_payment_status' => null,
                'qr_code_text'          => null,
                'qr_code_image'         => null,
                'paid_at'               => null,
                'created_by'            => $usuarioId,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            // Inserir itens
            foreach ($itens as $item) {
                $produto = $this->produtoModel->findById((int)$item['produto_id']);
                if (!$produto) {
                    throw new \RuntimeException('Produto nao encontrado.');
                }
                $custos = $this->produtoModel->getCustosVenda((int)$item['produto_id']);

                $this->vendaModel->addItem(
                    $vendaId,
                    (int)$item['produto_id'],
                    $item['quantidade'],
                    $item['preco_unitario'],
                    0,
                    $produto['nome'],
                    $custos['custo_unitario'],
                    $custos['mao_obra_unitaria'],
                    $custos['taxa_maquininha_percent'],
                    $custos['taxa_governo_percent']
                );

                // Baixar estoque
                if ($produto && $produto['controla_estoque']) {
                    $this->produtoModel->decrementarEstoque($item['produto_id'], $item['quantidade']);
                    $this->estoque->registrar([
                        'produto_id'      => $item['produto_id'],
                        'tipo'            => 'saida',
                        'quantidade'      => $item['quantidade'],
                        'valor_unitario'  => $item['preco_unitario'],
                        'observacao'      => "Venda #{$vendaId}",
                        'usuario_id'      => $usuarioId,
                        'referencia_tipo' => 'venda',
                        'referencia_id'   => $vendaId,
                    ]);
                }
            }

            // Para dinheiro e maquininha, confirmar automaticamente
            if (in_array($pagamento['forma_pagamento'], ['dinheiro', 'maquininha'])) {
                $this->vendaModel->update($vendaId, [
                    'status'  => 'paga',
                    'paid_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->caixa->registrar('venda', $valorFinal, $usuarioId, $vendaId);
            }

            $db->commit();
            return ['id' => $vendaId, 'valor_final' => $valorFinal];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Cria venda a partir de comanda fechada
     */
    public function criarVendaDeComanda(int $comandaId, array $pagamento, int $usuarioId): array
    {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $comanda = $this->comanda->findById($comandaId);
            if (!$comanda || $comanda['status'] !== 'aberta') {
                throw new \RuntimeException('Comanda inválida ou já fechada.');
            }

            $itens = $this->comanda->getItens($comandaId);
            $desconto = (float)($pagamento['desconto'] ?? $comanda['desconto'] ?? 0);
            $valorFinal = $comanda['subtotal'] - $desconto;

            $vendaId = $this->vendaModel->insert([
                'comanda_id'            => $comandaId,
                'cliente_id'            => $comanda['cliente_id'],
                'valor_bruto'           => $comanda['subtotal'],
                'desconto'              => $desconto,
                'valor_final'           => $valorFinal,
                'status'                => 'pendente',
                'forma_pagamento'       => $pagamento['forma_pagamento'],
                'subforma_pagamento'    => $pagamento['subforma_pagamento'] ?? null,
                'valor_recebido'        => $pagamento['valor_recebido'] ?? null,
                'troco'                 => $pagamento['troco'] ?? null,
                'stripe_payment_intent_id' => null,
                'stripe_payment_status' => null,
                'qr_code_text'          => null,
                'qr_code_image'         => null,
                'paid_at'               => null,
                'created_by'            => $usuarioId,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            foreach ($itens as $item) {
                $produtoId = !empty($item['produto_id']) ? (int)$item['produto_id'] : null;

                $this->vendaModel->addItem(
                    $vendaId,
                    $produtoId,
                    $item['quantidade'],
                    $item['preco_unitario'],
                    0,
                    $item['produto_nome'] ?? null,
                    (float)($item['custo_unitario'] ?? 0),
                    (float)($item['mao_obra_unitaria'] ?? 0),
                    (float)($item['taxa_maquininha_percent'] ?? 0),
                    (float)($item['taxa_governo_percent'] ?? 0)
                );

                // Baixar estoque
                $produto = $produtoId ? $this->produtoModel->findById($produtoId) : null;
                if ($produto && $produto['controla_estoque']) {
                    $this->produtoModel->decrementarEstoque($produtoId, $item['quantidade']);
                    $this->estoque->registrar([
                        'produto_id'      => $produtoId,
                        'tipo'            => 'saida',
                        'quantidade'      => $item['quantidade'],
                        'valor_unitario'  => $item['preco_unitario'],
                        'observacao'      => "Comanda #{$comandaId} / Venda #{$vendaId}",
                        'usuario_id'      => $usuarioId,
                        'referencia_tipo' => 'venda',
                        'referencia_id'   => $vendaId,
                    ]);
                }
            }

            // Fechar comanda
            $this->comanda->fechar($comandaId, $usuarioId);

            // Liberar mesa
            $this->mesa->setStatus($comanda['mesa_id'], 'livre');

            if (in_array($pagamento['forma_pagamento'], ['dinheiro', 'maquininha'])) {
                $this->vendaModel->update($vendaId, [
                    'status'     => 'paga',
                    'paid_at'    => now(),
                    'updated_at' => now(),
                ]);
                $this->caixa->registrar('venda', $valorFinal, $usuarioId, $vendaId);
            }

            $db->commit();
            return ['id' => $vendaId, 'valor_final' => $valorFinal];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
