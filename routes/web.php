<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProdutoController;
use App\Controllers\CategoriaController;
use App\Controllers\ClienteController;
use App\Controllers\FornecedorController;
use App\Controllers\MesaController;
use App\Controllers\ComandaController;
use App\Controllers\PdvController;
use App\Controllers\EstoqueController;
use App\Controllers\CaixaController;
use App\Controllers\RelatorioController;
use App\Controllers\UsuarioController;
use App\Controllers\WebhookController;
use App\Controllers\VendaController;
use App\Controllers\ConfigController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;

// --- Auth ---
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);

// Redirect root to dashboard
$router->get('/', fn() => redirect('/dashboard'));

// --- Dashboard ---
$router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

// --- PDV ---
$router->get('/pdv', [PdvController::class, 'index'], [AuthMiddleware::class]);
$router->get('/pdv/buscar', [PdvController::class, 'buscarProduto'], [AuthMiddleware::class]);
$router->post('/pdv/finalizar', [PdvController::class, 'finalizarVenda'], [AuthMiddleware::class]);
$router->get('/pdv/verificar/{id}', [PdvController::class, 'verificarPagamento'], [AuthMiddleware::class]);

// --- Produtos ---
$router->get('/produtos', [ProdutoController::class, 'index'], [AuthMiddleware::class]);
$router->get('/produtos/criar', [ProdutoController::class, 'create'], [AuthMiddleware::class]);
$router->post('/produtos', [ProdutoController::class, 'store'], [AuthMiddleware::class]);
$router->get('/produtos/{id}/editar', [ProdutoController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/produtos/{id}', [ProdutoController::class, 'update'], [AuthMiddleware::class]);
$router->post('/produtos/{id}/deletar', [ProdutoController::class, 'destroy'], [AuthMiddleware::class]);
$router->get('/produtos/{id}/imagem', [ProdutoController::class, 'imagem'], [AuthMiddleware::class]);


// --- Categorias ---
$router->get('/categorias', [CategoriaController::class, 'index'], [AuthMiddleware::class]);
$router->get('/categorias/criar', [CategoriaController::class, 'create'], [AuthMiddleware::class]);
$router->post('/categorias', [CategoriaController::class, 'store'], [AuthMiddleware::class]);
$router->get('/categorias/{id}/editar', [CategoriaController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/categorias/{id}', [CategoriaController::class, 'update'], [AuthMiddleware::class]);
$router->post('/categorias/{id}/deletar', [CategoriaController::class, 'destroy'], [AuthMiddleware::class]);

// --- Clientes ---
$router->get('/clientes', [ClienteController::class, 'index'], [AuthMiddleware::class]);
$router->get('/clientes/criar', [ClienteController::class, 'create'], [AuthMiddleware::class]);
$router->post('/clientes', [ClienteController::class, 'store'], [AuthMiddleware::class]);
$router->get('/clientes/{id}/editar', [ClienteController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/clientes/{id}', [ClienteController::class, 'update'], [AuthMiddleware::class]);
$router->post('/clientes/{id}/deletar', [ClienteController::class, 'destroy'], [AuthMiddleware::class]);

// --- Fornecedores ---
$router->get('/fornecedores', [FornecedorController::class, 'index'], [AuthMiddleware::class]);
$router->get('/fornecedores/criar', [FornecedorController::class, 'create'], [AuthMiddleware::class]);
$router->post('/fornecedores', [FornecedorController::class, 'store'], [AuthMiddleware::class]);
$router->get('/fornecedores/{id}/editar', [FornecedorController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/fornecedores/{id}', [FornecedorController::class, 'update'], [AuthMiddleware::class]);
$router->post('/fornecedores/{id}/deletar', [FornecedorController::class, 'destroy'], [AuthMiddleware::class]);

// --- Mesas ---
$router->get('/mesas', [MesaController::class, 'index'], [AuthMiddleware::class]);
$router->get('/mesas/criar', [MesaController::class, 'create'], [AuthMiddleware::class]);
$router->post('/mesas', [MesaController::class, 'store'], [AuthMiddleware::class]);
$router->get('/mesas/{id}/editar', [MesaController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/mesas/{id}', [MesaController::class, 'update'], [AuthMiddleware::class]);
$router->post('/mesas/{id}/deletar', [MesaController::class, 'destroy'], [AuthMiddleware::class]);

// --- Comandas ---
$router->get('/comandas', [ComandaController::class, 'index'], [AuthMiddleware::class]);
$router->post('/comandas/abrir', [ComandaController::class, 'abrir'], [AuthMiddleware::class]);
$router->get('/comandas/{id}', [ComandaController::class, 'show'], [AuthMiddleware::class]);
$router->post('/comandas/{id}/item', [ComandaController::class, 'addItem'], [AuthMiddleware::class]);
$router->post('/comandas/{id}/item/{itemId}/remover', [ComandaController::class, 'removeItem'], [AuthMiddleware::class]);
$router->get('/comandas/{id}/fechar', [ComandaController::class, 'fechar'], [AuthMiddleware::class]);
$router->post('/comandas/{id}/fechar', [ComandaController::class, 'processarFechamento'], [AuthMiddleware::class]);
$router->post('/comandas/{id}/cancelar', [ComandaController::class, 'cancelar'], [AuthMiddleware::class]);

// --- Estoque ---
$router->get('/estoque', [EstoqueController::class, 'index'], [AuthMiddleware::class]);
$router->get('/estoque/movimentacoes', [EstoqueController::class, 'movimentacoes'], [AuthMiddleware::class]);
$router->get('/estoque/{id}/historico', [EstoqueController::class, 'historico'], [AuthMiddleware::class]);
$router->get('/estoque/{id}/entrada', [EstoqueController::class, 'formEntrada'], [AuthMiddleware::class]);
$router->post('/estoque/{id}/entrada', [EstoqueController::class, 'entrada'], [AuthMiddleware::class]);
$router->get('/estoque/{id}/ajuste', [EstoqueController::class, 'formAjuste'], [AuthMiddleware::class]);
$router->post('/estoque/{id}/ajuste', [EstoqueController::class, 'ajuste'], [AuthMiddleware::class]);
$router->get('/estoque/{id}/perda', [EstoqueController::class, 'formPerda'], [AuthMiddleware::class]);
$router->post('/estoque/{id}/perda', [EstoqueController::class, 'perda'], [AuthMiddleware::class]);

// --- Caixa ---
$router->get('/caixa', [CaixaController::class, 'index'], [AuthMiddleware::class]);
$router->post('/caixa/abertura', [CaixaController::class, 'abertura'], [AuthMiddleware::class]);
$router->post('/caixa/sangria', [CaixaController::class, 'sangria'], [AuthMiddleware::class]);
$router->post('/caixa/suprimento', [CaixaController::class, 'suprimento'], [AuthMiddleware::class]);
$router->post('/caixa/fechamento', [CaixaController::class, 'fechamento'], [AuthMiddleware::class]);

// --- Vendas ---
$router->get('/vendas/{id}', [VendaController::class, 'show'], [AuthMiddleware::class]);
$router->get('/vendas/{id}/imprimir', [VendaController::class, 'imprimir'], [AuthMiddleware::class]);
$router->get('/vendas/{id}/aguardando-pagamento', [VendaController::class, 'aguardandoPagamento'], [AuthMiddleware::class]);

// --- Relatórios ---
$router->get('/relatorios/vendas', [RelatorioController::class, 'vendas'], [AuthMiddleware::class]);
$router->get('/relatorios/estoque-baixo', [RelatorioController::class, 'estoqueBaixo'], [AuthMiddleware::class]);
$router->get('/relatorios/mais-vendidos', [RelatorioController::class, 'maisVendidos'], [AuthMiddleware::class]);
$router->get('/relatorios/fechamento-semanal', [RelatorioController::class, 'fechamentoSemanal'], [AuthMiddleware::class]);
$router->get('/relatorios/movimentacoes', [RelatorioController::class, 'movimentacoes'], [AuthMiddleware::class]);
$router->get('/relatorios/exportar-vendas', [RelatorioController::class, 'exportarVendas'], [AuthMiddleware::class]);

// --- Configurações ---
$router->get('/config', [ConfigController::class, 'index'], [AuthMiddleware::class]);
$router->post('/config/funcionarios', [ConfigController::class, 'storeFuncionario'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/config/funcionarios/{id}/atualizar', [ConfigController::class, 'updateFuncionario'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/config/funcionarios/{id}/status', [ConfigController::class, 'toggleFuncionario'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/config/funcionarios/baixa', [ConfigController::class, 'baixarPagamentoFuncionario'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/config/zerar-vendas', [ConfigController::class, 'zerarVendas'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/config/mercadopago/auth', [ConfigController::class, 'mercadoPagoConnect'], [AuthMiddleware::class]);
$router->get('/config/mercadopago/callback', [ConfigController::class, 'mercadoPagoCallback'], [AuthMiddleware::class]);
$router->post('/config/mercadopago/disconnect', [ConfigController::class, 'mercadoPagoDisconnect'], [AuthMiddleware::class]);

// --- Usuários ---
$router->get('/usuarios', [UsuarioController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/usuarios/criar', [UsuarioController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/usuarios', [UsuarioController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/usuarios/{id}', [UsuarioController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/usuarios/{id}/deletar', [UsuarioController::class, 'destroy'], [AuthMiddleware::class, AdminMiddleware::class]);

// --- Webhooks ---
$router->post('/webhooks/mercadopago', [WebhookController::class, 'mercadopago']);
