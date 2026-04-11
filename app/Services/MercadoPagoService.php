<?php

namespace App\Services;

use App\Core\Logger;
use App\Models\Venda;

/**
 * MercadoPagoService - Integração com Mercado Pago via API REST
 * Suporta OAuth individual e pagamentos Pix via QR Code Dinâmico.
 */
class MercadoPagoService
{
    private string $appId;
    private string $clientSecret;
    private string $redirectUri;
    private ?string $accessToken;

    public function __construct(?string $accessToken = null)
    {
        $this->appId        = config('mercadopago.app_id');
        $this->clientSecret = config('mercadopago.client_secret');
        $this->redirectUri  = config('mercadopago.redirect_uri');
        $this->accessToken  = $accessToken;
    }

    /**
     * Gera a URL de autorização OAuth do Mercado Pago
     */
    public function getAuthUrl(string $state): string
    {
        return "https://auth.mercadopago.com/authorization" .
               "?client_id=" . $this->appId .
               "&response_type=code" .
               "&platform_id=mp" .
               "&state=" . $state .
               "&redirect_uri=" . urlencode($this->redirectUri);
    }

    /**
     * Troca o código de autorização pelo Access Token
     */
    public function exchangeCode(string $code): ?array
    {
        $url = 'https://api.mercadopago.com/oauth/token';
        
        $data = [
            'client_id'     => $this->appId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri
        ];

        $response = $this->post($url, $data, false);
        
        if (isset($response['access_token'])) {
            return $response;
        }

        Logger::error('Mercado Pago OAuth error: ' . json_encode($response));
        return null;
    }

    /**
     * Cria um pagamento Pix e retorna os dados do QR Code
     */
    public function criarPagamentoPix(int $vendaId, float $valor, string $emailPagador = 'cliente@exemplo.com'): ?array
    {
        if (!$this->accessToken) {
            throw new \RuntimeException('Access Token do Mercado Pago não configurado para este usuário.');
        }

        $url = 'https://api.mercadopago.com/v1/payments';
        
        $data = [
            'transaction_amount' => (float)$valor,
            'description'        => "Venda #{$vendaId} - Capitania PDV",
            'payment_method_id'  => 'pix',
            'payer' => [
                'email' => $emailPagador,
            ],
            'external_reference' => (string)$vendaId,
            'notification_url'   => env('APP_URL') . '/webhooks/mercadopago',
        ];

        // Header Idempotency
        $headers = [
            'X-Idempotency-Key: ' . uniqid('venda_' . $vendaId . '_', true)
        ];

        $response = $this->post($url, $data, true, $headers);

        if (isset($response['id'])) {
            $pixData = $response['point_of_interaction']['transaction_data'] ?? [];
            return [
                'id'            => $response['id'],
                'status'        => $response['status'],
                'qr_code'       => $pixData['qr_code'] ?? '',
                'qr_code_base64'=> $pixData['qr_code_base64'] ?? '',
                'ticket_url'    => $pixData['ticket_url'] ?? '',
            ];
        }

        Logger::error("Erro Mercado Pago Pix Venda #{$vendaId}: " . json_encode($response));
        return null;
    }

    /**
     * Consulta status de um pagamento
     */
    public function consultarPagamento(string $paymentId): ?array
    {
        $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";
        return $this->get($url);
    }

    // ------- Métodos Privados de Requisição -------

    private function post(string $url, array $data, bool $auth = true, array $additionalHeaders = []): array
    {
        $ch = curl_init($url);
        $headers = array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
        ], $additionalHeaders);

        if ($auth && $this->accessToken) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? ['error' => 'curl_failed', 'status' => $status];
    }

    private function get(string $url): array
    {
        $ch = curl_init($url);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
