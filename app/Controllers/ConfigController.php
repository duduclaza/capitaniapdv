<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\MercadoPagoService;
use App\Models\Usuario;

class ConfigController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Página principal de configurações
     */
    public function index(): void
    {
        $user = auth();
        // Recarrega os dados do usuário do banco para ter os tokens atualizados
        $userData = $this->usuarioModel->findById($user['id']);
        
        $this->view('config/index', [
            'user' => $userData
        ]);
    }

    /**
     * Inicia o redirecionamento para o Mercado Pago
     */
    public function mercadoPagoConnect(): void
    {
        $mp = new MercadoPagoService();
        $state = bin2hex(random_bytes(16));
        $_SESSION['mp_oauth_state'] = $state;

        $url = $mp->getAuthUrl($state);
        $this->redirect($url);
    }

    /**
     * Callback do OAuth do Mercado Pago
     */
    public function mercadoPagoCallback(): void
    {
        $code  = $this->input('code');
        $state = $this->input('state');

        // Valida o state para prevenir CSRF
        if (!$state || $state !== ($_SESSION['mp_oauth_state'] ?? '')) {
            $this->flash('error', 'Erro de validação de segurança (state inválido).');
            $this->redirect('/config');
            return;
        }
        unset($_SESSION['mp_oauth_state']);

        if (!$code) {
            $this->flash('error', 'Autorização cancelada pelo usuário ou falhou.');
            $this->redirect('/config');
            return;
        }

        $mp = new MercadoPagoService();
        $tokens = $mp->exchangeCode($code);

        if ($tokens && isset($tokens['access_token'])) {
            $user = auth();
            $expiresAt = date('Y-m-d H:i:s', time() + $tokens['expires_in']);

            $this->usuarioModel->update($user['id'], [
                'mp_access_token'  => $tokens['access_token'],
                'mp_refresh_token' => $tokens['refresh_token'],
                'mp_user_id'       => $tokens['user_id'],
                'mp_expires_at'    => $expiresAt,
                'updated_at'       => now()
            ]);

            $this->flash('success', 'Mercado Pago conectado com sucesso!');
        } else {
            $this->flash('error', 'Falha ao obter token do Mercado Pago.');
        }

        $this->redirect('/config');
    }

    /**
     * Desconecta o Mercado Pago do usuário
     */
    public function mercadoPagoDisconnect(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/config');
            return;
        }

        $this->validateCsrf();
        $user = auth();

        $this->usuarioModel->update($user['id'], [
            'mp_access_token'  => null,
            'mp_refresh_token' => null,
            'mp_user_id'       => null,
            'mp_expires_at'    => null,
            'updated_at'       => now()
        ]);

        $this->flash('info', 'Mercado Pago desconectado.');
        $this->redirect('/config');
    }
}
