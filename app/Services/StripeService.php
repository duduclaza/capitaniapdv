<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\CaixaMovimento;
use App\Core\Logger;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price as StripePrice;
use Stripe\PaymentIntent;
use Stripe\Webhook;

/**
 * StripeService - Gestão de produtos e pagamentos Pix via Stripe
 */
class StripeService
{
    private Venda $vendaModel;
    private CaixaMovimento $caixa;

    public function __construct()
    {
        $this->vendaModel = new Venda();
        $this->caixa      = new CaixaMovimento();

        Stripe::setApiKey(config('stripe.secret_key'));
    }

    // ==========================================================================
    //  GESTÃO DE PRODUTOS NO STRIPE
    // ==========================================================================

    /**
     * Cria um produto + price no Stripe ao cadastrar produto no sistema.
     * Retorna [ 'stripe_product_id' => '...', 'stripe_price_id' => '...' ]
     */
    public function criarProduto(array $produto): array
    {
        try {
            // 1) Cria o produto no Stripe
            $stripeProduct = StripeProduct::create([
                'name'        => $produto['nome'],
                'description' => $produto['nome'] . ' — Capitania PDV',
                'active'      => (bool)($produto['ativo'] ?? true),
                'metadata'    => [
                    'sistema_id' => $produto['id'] ?? '',
                    'sku'        => $produto['sku'] ?? '',
                    'unidade'    => $produto['unidade'] ?? 'un',
                ],
            ]);

            // 2) Cria o price (em centavos BRL) vinculado ao produto
            $stripePrice = StripePrice::create([
                'product'    => $stripeProduct->id,
                'unit_amount'=> (int) round(floatval($produto['preco_venda']) * 100),
                'currency'   => 'brl',
                'metadata'   => ['produto_local_id' => $produto['id'] ?? ''],
            ]);

            Logger::info("Produto criado no Stripe: product={$stripeProduct->id} price={$stripePrice->id}");

            return [
                'stripe_product_id' => $stripeProduct->id,
                'stripe_price_id'   => $stripePrice->id,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe criarProduto error: ' . $e->getMessage());
            // Não interrompe — falha silenciosa, o produto local ainda é criado
            return [
                'stripe_product_id' => null,
                'stripe_price_id'   => null,
            ];
        }
    }

    /**
     * Atualiza nome/ativo do produto no Stripe quando o produto local é editado.
     * Se o preço mudou, arquiva o price antigo e cria um novo.
     */
    public function atualizarProduto(array $produtoLocal, ?string $stripeProductId, ?string $stripePriceIdAtual): array
    {
        if (!$stripeProductId) {
            // Produto nunca foi sincronizado — cria agora
            return $this->criarProduto($produtoLocal);
        }

        try {
            // Atualiza nome e status do produto
            StripeProduct::update($stripeProductId, [
                'name'   => $produtoLocal['nome'],
                'active' => (bool)($produtoLocal['ativo'] ?? true),
                'metadata' => [
                    'sku'     => $produtoLocal['sku'] ?? '',
                    'unidade' => $produtoLocal['unidade'] ?? 'un',
                ],
            ]);

            // Verifica se o preço mudou
            $newPriceId = $stripePriceIdAtual;
            if ($stripePriceIdAtual) {
                $currentPrice = StripePrice::retrieve($stripePriceIdAtual);
                $novoValorCentavos = (int) round(floatval($produtoLocal['preco_venda']) * 100);

                if ($currentPrice->unit_amount !== $novoValorCentavos) {
                    // Arquiva o price antigo
                    StripePrice::update($stripePriceIdAtual, ['active' => false]);

                    // Cria novo price com o valor atualizado
                    $newPrice = StripePrice::create([
                        'product'     => $stripeProductId,
                        'unit_amount' => $novoValorCentavos,
                        'currency'    => 'brl',
                        'metadata'    => ['produto_local_id' => $produtoLocal['id'] ?? ''],
                    ]);
                    $newPriceId = $newPrice->id;

                    Logger::info("Price atualizado no Stripe para o produto {$stripeProductId}: {$newPriceId}");
                }
            } else {
                // Sem price anterior — cria
                $newPrice = StripePrice::create([
                    'product'     => $stripeProductId,
                    'unit_amount' => (int) round(floatval($produtoLocal['preco_venda']) * 100),
                    'currency'    => 'brl',
                ]);
                $newPriceId = $newPrice->id;
            }

            return [
                'stripe_product_id' => $stripeProductId,
                'stripe_price_id'   => $newPriceId,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe atualizarProduto error: ' . $e->getMessage());
            return [
                'stripe_product_id' => $stripeProductId,
                'stripe_price_id'   => $stripePriceIdAtual,
            ];
        }
    }

    /**
     * Arquiva (desativa) o produto no Stripe ao excluir localmente.
     */
    public function arquivarProduto(?string $stripeProductId): void
    {
        if (!$stripeProductId) return;

        try {
            StripeProduct::update($stripeProductId, ['active' => false]);
            Logger::info("Produto arquivado no Stripe: {$stripeProductId}");
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe arquivarProduto error: ' . $e->getMessage());
        }
    }

    // ==========================================================================
    //  PAGAMENTOS PIX QR CODE
    // ==========================================================================

    /**
     * Cria um PaymentIntent Pix para uma venda.
     * Se forem passados os itens com stripe_price_id, são enviados como line_items metadata.
     */
    public function criarPagamentoPix(int $vendaId, float $valor, array $itens = []): array
    {
        $valorCentavos = (int) round($valor * 100);

        // Monta descrição dos itens para o metadata
        $descricaoItens = implode(', ', array_map(
            fn($i) => "{$i['produto_nome']} x{$i['quantidade']}",
            $itens
        ));

        try {
            Logger::info("Iniciando criação de PaymentIntent Pix para Venda #{$vendaId} no valor de R$ " . number_format($valor, 2));
            
            $paymentIntent = PaymentIntent::create([
                'amount'               => $valorCentavos,
                'currency'             => 'brl',
                'payment_method_types' => ['pix'],
                'metadata'             => [
                    'venda_id' => (string)$vendaId,
                    'itens'    => (string)mb_substr($descricaoItens, 0, 500),
                ],
                'payment_method_options' => [
                    'pix' => [
                        'expires_after_seconds' => 3600, // QR expira em 1 hora
                    ],
                ],
            ]);

            Logger::info("PaymentIntent criado com sucesso: id={$paymentIntent->id} status={$paymentIntent->status}");

            // Extrai dados do QR Code Pix
            $pixData     = $paymentIntent->next_action?->pix_display_qr_code ?? null;
            $qrCodeText  = $pixData?->data        ?? '';
            $qrCodeImage = $pixData?->image_url_svg ?? '';

            if (empty($qrCodeText)) {
                Logger::warning("Stripe criou o PI, mas nenhum QR Code foi retornado para Venda #{$vendaId}. Status: {$paymentIntent->status}");
            }

            // Persiste na venda
            $this->vendaModel->update($vendaId, [
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_payment_status'    => $paymentIntent->status,
                'qr_code_text'             => $qrCodeText,
                'qr_code_image'            => $qrCodeImage,
                'updated_at'               => now(),
            ]);

            return [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret'     => $paymentIntent->client_secret,
                'qr_code_text'      => $qrCodeText,
                'qr_code_image'     => $qrCodeImage,
                'status'            => $paymentIntent->status,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe criarPagamentoPix error: ' . $e->getMessage());
            throw new \RuntimeException('Erro ao criar pagamento Stripe Pix: ' . $e->getMessage());
        }
    }

    /**
     * Verifica o status de um PaymentIntent
     */
    public function verificarStatus(string $paymentIntentId): array
    {
        try {
            $pi = PaymentIntent::retrieve($paymentIntentId);
            return [
                'status'   => $pi->status,
                'amount'   => $pi->amount / 100,
                'currency' => $pi->currency,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe verificarStatus error: ' . $e->getMessage());
            throw new \RuntimeException('Erro ao verificar pagamento.');
        }
    }

    /**
     * Processa eventos de webhook do Stripe
     */
    public function processarWebhook(string $payload, string $signature): void
    {
        $webhookSecret = config('stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Logger::error('Stripe webhook payload inválido: ' . $e->getMessage());
            throw $e;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Logger::error('Stripe webhook assinatura inválida: ' . $e->getMessage());
            throw $e;
        }

        Logger::info('Stripe event: ' . $event->type);

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->confirmarPagamento($event->data->object->id);
                break;

            case 'payment_intent.payment_failed':
                $this->vendaModel->updateStripeStatus($event->data->object->id, 'failed');
                break;

            case 'payment_intent.canceled':
                $this->vendaModel->updateStripeStatus($event->data->object->id, 'canceled');
                break;
        }
    }

    private function confirmarPagamento(string $paymentIntentId): void
    {
        $venda = $this->vendaModel->findByPaymentIntent($paymentIntentId);
        if (!$venda) {
            Logger::warning("Venda não encontrada para PI: {$paymentIntentId}");
            return;
        }

        if ($venda['status'] === 'paga') {
            Logger::info("Venda #{$venda['id']} já confirmada — ignorando duplicidade.");
            return;
        }

        $this->vendaModel->update($venda['id'], [
            'status'                => 'paga',
            'stripe_payment_status' => 'succeeded',
            'paid_at'               => now(),
            'updated_at'            => now(),
        ]);

        $this->caixa->registrar('venda', $venda['valor_final'], $venda['created_by'], $venda['id'], 'Stripe Pix QR Code');

        Logger::info("Venda #{$venda['id']} confirmada via Stripe Pix.");
    }
}
