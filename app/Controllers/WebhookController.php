<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;
use App\Services\StripeService;
use App\Core\Logger;

class WebhookController extends Controller
{
    public function mercadopago(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($payload['type']) || $payload['type'] !== 'payment') {
            http_response_code(200);
            return;
        }

        $paymentId = $payload['data']['id'] ?? null;
        $sellerId  = (string)($payload['user_id'] ?? '');

        if (!$paymentId || !$sellerId) {
            http_response_code(400);
            return;
        }

        try {
            // 1. Localizar o usuário dono desse seller_id (mp_user_id)
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare('SELECT id, mp_access_token FROM usuarios WHERE mp_user_id = ? LIMIT 1');
            $stmt->execute([$sellerId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user || empty($user['mp_access_token'])) {
                Logger::error("Webhook MP: Usuário não encontrado para seller_id {$sellerId}");
                http_response_code(200); // MP exige 200/201
                return;
            }

            // 2. Consultar o status real do pagamento no Mercado Pago
            $mp = new \App\Services\MercadoPagoService($user['mp_access_token']);
            $paymentInfo = $mp->consultarPagamento((string)$paymentId);

            if (!$paymentInfo || !isset($paymentInfo['status'])) {
                Logger::error("Webhook MP: Pagamento {$paymentId} não encontrado no MP.");
                http_response_code(200);
                return;
            }

            // 3. Atualizar a venda correspondente (external_reference armazena o venda_id)
            $vendaId = $paymentInfo['external_reference'] ?? null;
            if (!$vendaId) {
                Logger::warning("Webhook MP: Pagamento {$paymentId} sem external_reference.");
                http_response_code(200);
                return;
            }

            $vendaModel = new Venda();
            $venda = $vendaModel->findById((int)$vendaId);

            if ($venda) {
                $statusMap = [
                    'approved' => 'paga',
                    'rejected' => 'cancelada',
                    'cancelled'=> 'cancelada',
                    'pending'  => 'pendente'
                ];

                $novoStatus = $statusMap[$paymentInfo['status']] ?? $venda['status'];
                
                $updateData = [
                    'mp_payment_status' => $paymentInfo['status'],
                    'status' => $novoStatus,
                    'updated_at' => now()
                ];

                if ($paymentInfo['status'] === 'approved' && $venda['status'] !== 'paga') {
                    $updateData['paid_at'] = now();
                    
                    // Se for venda de comanda, o VendaService deve cuidar disso? 
                    // Para simplificar aqui, atualizamos o básico.
                    // Em um sistema real, aqui dispararíamos eventos de baixa de estoque se necessário.
                }

                $vendaModel->update($venda['id'], $updateData);
                
                Logger::info("Webhook MP: Venda #{$vendaId} atualizada para {$novoStatus} (Pagamento: {$paymentId})");
            }

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            Logger::error('Webhook MP error: ' . $e->getMessage());
            http_response_code(500);
        }

        exit;
    }
}
