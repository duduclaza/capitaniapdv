<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Controller;
use App\Services\MercadoPagoService;
use App\Models\Funcionario;
use App\Models\FuncionarioPagamento;
use App\Models\Usuario;
use App\Models\Venda;

class ConfigController extends Controller
{
    private Usuario $usuarioModel;
    private Funcionario $funcionarioModel;
    private FuncionarioPagamento $funcionarioPagamentoModel;
    private Venda $vendaModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        $this->funcionarioModel = new Funcionario();
        $this->funcionarioPagamentoModel = new FuncionarioPagamento();
        $this->vendaModel = new Venda();
    }

    /**
     * Página principal de configurações
     */
    public function index(): void
    {
        $user = auth();
        // Recarrega os dados do usuário do banco para ter os tokens atualizados
        $userData = $this->usuarioModel->findById($user['id']);
        $dataInicio = $this->input('data_inicio', date('Y-m-d', strtotime('monday this week')));
        $dataFim = $this->input('data_fim', date('Y-m-d', strtotime('sunday this week')));
        $funcionarios = $this->funcionarioModel->findAllOrdered();
        $funcionariosAtivos = array_values(array_filter($funcionarios, fn($f) => (int)$f['ativo'] === 1));
        $totalMaoObra = $this->vendaModel->getTotalMaoObra($dataInicio, $dataFim);
        $valorPorFuncionario = count($funcionariosAtivos) > 0 ? $totalMaoObra / count($funcionariosAtivos) : 0.0;
        $pagosPorFuncionario = $this->funcionarioPagamentoModel->getTotaisPorFuncionarioPeriodo($dataInicio, $dataFim);
        $fechamentosFuncionarios = [];

        foreach ($funcionariosAtivos as $funcionario) {
            $recebido = $pagosPorFuncionario[(int)$funcionario['id']] ?? 0.0;
            $fechamentosFuncionarios[] = [
                'funcionario' => $funcionario,
                'previsto' => $valorPorFuncionario,
                'recebido' => $recebido,
                'a_receber' => max(0, $valorPorFuncionario - $recebido),
            ];
        }

        $pagamentosRecentes = $this->funcionarioPagamentoModel->getRecentes(8);
        $resetStats = $this->getResetStats();
        
        $this->view('config/index', [
            'user' => $userData,
            'funcionarios' => $funcionarios,
            'fechamentosFuncionarios' => $fechamentosFuncionarios,
            'pagamentosRecentes' => $pagamentosRecentes,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'totalMaoObra' => $totalMaoObra,
            'valorPorFuncionario' => $valorPorFuncionario,
            'resetStats' => $resetStats,
        ]);
    }

    public function storeFuncionario(): void
    {
        $this->validateCsrf();

        $nome = trim((string)$this->input('nome', ''));
        if ($nome === '') {
            $this->flash('error', 'Informe o nome do funcionário.');
            $this->redirect('/config#funcionarios');
            return;
        }

        $this->funcionarioModel->createFuncionario([
            'nome' => $nome,
            'cargo' => $this->input('cargo', ''),
            'telefone' => $this->input('telefone', ''),
        ]);

        $this->flash('success', 'Funcionário cadastrado.');
        $this->redirect('/config#funcionarios');
    }

    public function updateFuncionario(string $id): void
    {
        $this->validateCsrf();

        $nome = trim((string)$this->input('nome', ''));
        if ($nome === '') {
            $this->flash('error', 'Informe o nome do funcionário.');
            $this->redirect('/config#funcionarios');
            return;
        }

        $this->funcionarioModel->updateFuncionario((int)$id, [
            'nome' => $nome,
            'cargo' => $this->input('cargo', ''),
            'telefone' => $this->input('telefone', ''),
        ]);

        $this->flash('success', 'Funcionário atualizado.');
        $this->redirect('/config#funcionarios');
    }

    public function toggleFuncionario(string $id): void
    {
        $this->validateCsrf();
        $ativo = (int)$this->input('ativo', 0) === 1;
        $this->funcionarioModel->setAtivo((int)$id, $ativo);

        $this->flash('success', $ativo ? 'Funcionário ativado.' : 'Funcionário desativado.');
        $this->redirect('/config#funcionarios');
    }

    public function baixarPagamentoFuncionario(): void
    {
        $this->validateCsrf();

        $funcionarioId = (int)$this->input('funcionario_id', 0);
        $dataInicio = (string)$this->input('data_inicio', today());
        $dataFim = (string)$this->input('data_fim', today());
        $funcionario = $this->funcionarioModel->findById($funcionarioId);

        if (!$funcionario || !(int)$funcionario['ativo']) {
            $this->flash('error', 'Funcionário ativo não encontrado.');
            $this->redirect('/config#fechamento-funcionarios');
            return;
        }

        $funcionariosAtivos = $this->funcionarioModel->findAtivos();
        $totalMaoObra = $this->vendaModel->getTotalMaoObra($dataInicio, $dataFim);
        $valorPorFuncionario = count($funcionariosAtivos) > 0 ? $totalMaoObra / count($funcionariosAtivos) : 0.0;
        $recebido = $this->funcionarioPagamentoModel->getTotalFuncionarioPeriodo($funcionarioId, $dataInicio, $dataFim);
        $pendente = max(0, $valorPorFuncionario - $recebido);
        $valor = min($this->decimal($this->input('valor', $pendente)), $pendente);

        if ($valor <= 0) {
            $this->flash('error', 'Não há valor pendente para baixar nesse período.');
            $this->redirect('/config?data_inicio=' . urlencode($dataInicio) . '&data_fim=' . urlencode($dataFim) . '#fechamento-funcionarios');
            return;
        }

        $user = auth();
        $this->funcionarioPagamentoModel->registrar(
            $funcionarioId,
            $dataInicio,
            $dataFim,
            $valor,
            (int)$user['id'],
            $this->input('observacao', 'Baixa de pagamento')
        );

        $this->flash('success', 'Baixa de pagamento registrada.');
        $this->redirect('/config?data_inicio=' . urlencode($dataInicio) . '&data_fim=' . urlencode($dataFim) . '#fechamento-funcionarios');
    }

    public function zerarVendas(): void
    {
        $this->validateCsrf();

        $user = auth();
        $userData = $this->usuarioModel->findById((int)$user['id']);
        $senha = (string)$this->input('senha_admin', '');

        if (!$userData || ($userData['perfil'] ?? '') !== 'admin') {
            $this->flash('error', 'Somente administrador pode zerar os dados de vendas.');
            $this->redirect('/config#zerar-vendas');
            return;
        }

        if (!password_verify($senha, $userData['senha_hash'])) {
            $this->flash('error', 'Senha do administrador inválida.');
            $this->redirect('/config#zerar-vendas');
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $db->exec("DELETE FROM funcionario_pagamentos");
            $db->exec("DELETE FROM caixa_movimentos");
            $db->exec("DELETE FROM venda_itens");
            $db->exec("DELETE FROM vendas");
            $db->exec("DELETE FROM comanda_itens");
            $db->exec("DELETE FROM comandas");
            $db->exec("UPDATE mesas SET status = 'livre', updated_at = NOW()");

            $db->commit();

            foreach (['funcionario_pagamentos', 'caixa_movimentos', 'venda_itens', 'vendas', 'comanda_itens', 'comandas'] as $table) {
                try {
                    $db->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
                } catch (\Throwable) {
                    // O reset do contador nao deve impedir a limpeza dos dados.
                }
            }

            $this->flash('success', 'Dados de vendas zerados com sucesso.');
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->flash('error', 'Erro ao zerar vendas: ' . $e->getMessage());
        }

        $this->redirect('/config#zerar-vendas');
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

    private function getResetStats(): array
    {
        $db = Database::getInstance();
        $tables = [
            'vendas' => 'Vendas',
            'venda_itens' => 'Itens de venda',
            'comandas' => 'Comandas',
            'comanda_itens' => 'Itens de comanda',
            'caixa_movimentos' => 'Movimentos de caixa',
            'funcionario_pagamentos' => 'Baixas de funcionários',
        ];

        $stats = [];
        foreach ($tables as $table => $label) {
            try {
                $stats[$table] = [
                    'label' => $label,
                    'count' => (int)$db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn(),
                ];
            } catch (\Throwable) {
                $stats[$table] = [
                    'label' => $label,
                    'count' => 0,
                ];
            }
        }

        return $stats;
    }

    private function decimal(mixed $value): float
    {
        return (float)str_replace(',', '.', (string)($value ?? '0'));
    }
}
