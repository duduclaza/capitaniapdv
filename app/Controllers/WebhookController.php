<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venda;
use App\Services\StripeService;
use App\Core\Logger;

class WebhookController extends Controller
{
    public function stripe(): void
    {
        $payload   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $service = new StripeService();
            $service->processarWebhook($payload, $signature);
            http_response_code(200);
            echo json_encode(['received' => true]);
        } catch (\Throwable $e) {
            Logger::error('Webhook error: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit;
    }
}
