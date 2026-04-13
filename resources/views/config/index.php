<?php $pageTitle = 'Configurações'; ?>

<div class="max-w-6xl mx-auto space-y-6 fade-in-up">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white">Configurações do Sistema</h2>
            <p class="text-gray-400">Gerencie funcionários, fechamentos, integrações e manutenção do sistema.</p>
        </div>
    </div>

    <!-- Funcionarios -->
    <div id="funcionarios" class="glass-card rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 rounded-2xl bg-primary-900/40 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-primary-400"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Funcionários</h3>
                    <p class="text-sm text-gray-400">Cadastre quem participa do fechamento de mão de obra.</p>
                </div>
            </div>

            <?php if (isAdmin()): ?>
            <form method="POST" action="/config/funcionarios" class="grid grid-cols-1 md:grid-cols-[1.2fr_1fr_1fr_auto] gap-3">
                <?= csrf_field() ?>
                <input type="text" name="nome" required placeholder="Nome do funcionário"
                       class="px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-primary-500">
                <input type="text" name="cargo" placeholder="Cargo ou função"
                       class="px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-primary-500">
                <input type="text" name="telefone" placeholder="Telefone"
                       class="px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-primary-500">
                <button type="submit" class="px-5 py-3 bg-primary-600 hover:bg-primary-500 rounded-xl text-sm font-semibold text-white transition-colors">
                    Cadastrar
                </button>
            </form>
            <?php else: ?>
                <p class="text-sm text-gray-500">Apenas administradores e gerentes podem alterar funcionários.</p>
            <?php endif; ?>
        </div>

        <div class="divide-y divide-white/5">
            <?php if (empty($funcionarios)): ?>
                <div class="p-6 text-center text-gray-500 text-sm">Nenhum funcionário cadastrado.</div>
            <?php else: foreach ($funcionarios as $funcionario): ?>
                <form method="POST" action="/config/funcionarios/<?= $funcionario['id'] ?>/atualizar"
                      class="p-4 grid grid-cols-1 lg:grid-cols-[1.2fr_1fr_1fr_auto] gap-3 items-center hover:bg-white/3 transition-colors">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Nome</label>
                        <input type="text" name="nome" required value="<?= e($funcionario['nome']) ?>"
                               class="w-full px-3 py-2.5 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Cargo</label>
                        <input type="text" name="cargo" value="<?= e($funcionario['cargo'] ?? '') ?>"
                               class="w-full px-3 py-2.5 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold tracking-wider text-gray-500 mb-1">Telefone</label>
                        <input type="text" name="telefone" value="<?= e($funcionario['telefone'] ?? '') ?>"
                               class="w-full px-3 py-2.5 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                    </div>
                    <div class="flex items-end gap-2 lg:pt-5">
                        <span class="px-2 py-2 rounded-lg text-xs font-semibold <?= $funcionario['ativo'] ? 'bg-emerald-900/40 text-emerald-300' : 'bg-gray-800 text-gray-400' ?>">
                            <?= $funcionario['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                        <?php if (isAdmin()): ?>
                            <button type="submit" class="px-3 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg text-xs font-semibold text-white transition-colors">
                                Salvar
                            </button>
                            <button type="submit"
                                    formaction="/config/funcionarios/<?= $funcionario['id'] ?>/status"
                                    name="ativo"
                                    value="<?= $funcionario['ativo'] ? 0 : 1 ?>"
                                    class="px-3 py-2 <?= $funcionario['ativo'] ? 'bg-red-900/20 hover:bg-red-900/40 text-red-300 border-red-500/30' : 'bg-emerald-900/20 hover:bg-emerald-900/40 text-emerald-300 border-emerald-500/30' ?> border rounded-lg text-xs font-semibold transition-colors">
                                <?= $funcionario['ativo'] ? 'Desativar' : 'Ativar' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Fechamento Funcionarios -->
    <div id="fechamento-funcionarios" class="glass-card rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-900/40 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-5 h-5 text-emerald-400"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Fechamento dos funcionários</h3>
                        <p class="text-sm text-gray-400">Valor de mão de obra dividido entre os funcionários ativos no período.</p>
                    </div>
                </div>

                <form method="GET" action="/config#fechamento-funcionarios" class="flex flex-wrap items-center gap-2">
                    <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>"
                           class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                    <input type="date" name="data_fim" value="<?= e($dataFim) ?>"
                           class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-500 rounded-xl text-sm font-semibold text-white transition-colors">
                        Atualizar
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-5">
                <div class="rounded-2xl bg-white/3 border border-white/5 p-4">
                    <p class="text-xs text-gray-400 mb-1">Mão de obra no período</p>
                    <p class="text-xl font-bold text-primary-400"><?= formatMoney((float)$totalMaoObra) ?></p>
                </div>
                <div class="rounded-2xl bg-white/3 border border-white/5 p-4">
                    <p class="text-xs text-gray-400 mb-1">Funcionários ativos</p>
                    <p class="text-xl font-bold text-white"><?= count($fechamentosFuncionarios) ?></p>
                </div>
                <div class="rounded-2xl bg-white/3 border border-white/5 p-4">
                    <p class="text-xs text-gray-400 mb-1">Valor por funcionário</p>
                    <p class="text-xl font-bold text-emerald-400"><?= formatMoney((float)$valorPorFuncionario) ?></p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Funcionário</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">A receber</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Já recebeu</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Falta receber</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($fechamentosFuncionarios)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Cadastre ao menos um funcionário ativo para gerar o fechamento.</td>
                        </tr>
                    <?php else: foreach ($fechamentosFuncionarios as $linha): ?>
                        <?php $funcionario = $linha['funcionario']; ?>
                        <tr class="hover:bg-white/3 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-white"><?= e($funcionario['nome']) ?></p>
                                <p class="text-xs text-gray-500"><?= e($funcionario['cargo'] ?? 'Sem cargo') ?></p>
                            </td>
                            <td class="px-4 py-3 text-sm text-white text-right"><?= formatMoney((float)$linha['previsto']) ?></td>
                            <td class="px-4 py-3 text-sm text-emerald-300 text-right"><?= formatMoney((float)$linha['recebido']) ?></td>
                            <td class="px-4 py-3 text-sm font-semibold text-amber-300 text-right"><?= formatMoney((float)$linha['a_receber']) ?></td>
                            <td class="px-4 py-3 text-right">
                                <?php if ((float)$linha['a_receber'] > 0 && isAdmin()): ?>
                                    <form method="POST" action="/config/funcionarios/baixa" onsubmit="return confirm('Efetuar baixa do pagamento de <?= e($funcionario['nome']) ?>?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="funcionario_id" value="<?= $funcionario['id'] ?>">
                                        <input type="hidden" name="data_inicio" value="<?= e($dataInicio) ?>">
                                        <input type="hidden" name="data_fim" value="<?= e($dataFim) ?>">
                                        <input type="hidden" name="valor" value="<?= number_format((float)$linha['a_receber'], 2, '.', '') ?>">
                                        <input type="hidden" name="observacao" value="Fechamento <?= e($dataInicio) ?> a <?= e($dataFim) ?>">
                                        <button type="submit" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-lg text-xs font-semibold text-white transition-colors">
                                            Efetuar baixa do pagamento
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">Quitado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($pagamentosRecentes)): ?>
            <div class="p-6 border-t border-white/5">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Últimas baixas</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($pagamentosRecentes as $pagamento): ?>
                        <div class="rounded-2xl bg-white/3 border border-white/5 p-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-white"><?= e($pagamento['funcionario_nome']) ?></p>
                                <p class="text-xs text-gray-500"><?= formatDate($pagamento['data_inicio'], 'd/m/Y') ?> a <?= formatDate($pagamento['data_fim'], 'd/m/Y') ?></p>
                                <p class="text-[10px] text-gray-600">Baixa por <?= e($pagamento['pago_por_nome'] ?? '-') ?> em <?= formatDate($pagamento['paid_at'], 'd/m/Y H:i') ?></p>
                            </div>
                            <p class="text-sm font-bold text-emerald-300"><?= formatMoney((float)$pagamento['valor']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Zerar Vendas -->
    <div id="zerar-vendas" class="glass-card rounded-3xl overflow-hidden border-red-500/20">
        <div class="p-6 border-b border-red-500/20 bg-red-950/20">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-red-900/40 flex items-center justify-center">
                    <i data-lucide="trash-2" class="w-5 h-5 text-red-300"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Zerar dados de vendas</h3>
                    <p class="text-sm text-red-200/80">Apaga vendas, comandas, movimentos de caixa, saídas de estoque por venda e baixas de pagamento. Produtos, clientes, funcionários e usuários ficam cadastrados.</p>
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6">
            <div>
                <p class="text-sm text-gray-300 mb-4">
                    Use apenas antes de entregar o sistema para outro cliente ou quando precisar limpar o histórico de vendas. Esta ação não pode ser desfeita.
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach ($resetStats as $stat): ?>
                        <div class="rounded-2xl bg-white/3 border border-white/5 p-4">
                            <p class="text-xs text-gray-500 mb-1"><?= e($stat['label']) ?></p>
                            <p class="text-xl font-bold text-white"><?= number_format((int)$stat['count'], 0, ',', '.') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (($user['perfil'] ?? '') === 'admin'): ?>
                <form method="POST" action="/config/zerar-vendas"
                      class="rounded-2xl bg-red-950/20 border border-red-500/20 p-5 space-y-4"
                      onsubmit="return confirm('ATENÇÃO: isso vai apagar todos os registros de vendas, caixa, comandas, saídas de estoque por venda e baixas de funcionários. Deseja continuar?')">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-xs font-semibold text-red-200 mb-2">Senha do administrador</label>
                        <input type="password" name="senha_admin" required autocomplete="current-password"
                               placeholder="Digite a senha do admin"
                               class="w-full px-4 py-3 bg-dark-900 border border-red-500/30 rounded-xl text-sm text-white placeholder-gray-500 focus:outline-none focus:border-red-400">
                    </div>
                    <button type="submit" class="w-full px-4 py-3 bg-red-700 hover:bg-red-600 rounded-xl text-sm font-bold text-white transition-colors">
                        Zerar dados de vendas
                    </button>
                    <p class="text-xs text-red-200/70">Será solicitado confirmar no navegador antes da limpeza.</p>
                </form>
            <?php else: ?>
                <div class="rounded-2xl bg-red-950/20 border border-red-500/20 p-5">
                    <p class="text-sm text-red-200">Somente o usuário administrador pode zerar dados de vendas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mercado Pago Integration Card -->
    <div class="glass-card rounded-3xl overflow-hidden">
        <div class="p-8 border-b border-white/5 bg-gradient-to-r from-blue-600/10 to-transparent">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-[#009ee3] flex items-center justify-center shadow-lg shadow-blue-900/40">
                    <svg viewBox="0 0 32 32" class="w-7 h-7 fill-white"><path d="M22.067 11.233a7.333 7.333 0 01-7.334 7.334H11.4l-2.067 6.1s-.4.6 0 .6h3.4s.467 0 .6-.4l.6-1.8h.4c5.8 0 10.4-4.667 10.4-10.4s-3.2-10-8.933-10h-6.2c-.667 0-1.133.467-1.334 1.133L2.2 24.867s-.4.6 0 .6h3.4s.467 0 .6-.4L10.067 13.1c.333-1 1.2-1.867 2.266-1.867h9.734z"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Integração Mercado Pago</h3>
                    <p class="text-sm text-gray-400">Receba pagamentos via Pix diretamente no PDV.</p>
                </div>
            </div>

            <?php if (!empty($user['mp_access_token'])): ?>
                <div class="flex items-center gap-3 bg-emerald-900/20 border border-emerald-500/30 p-4 rounded-2xl mb-6">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-emerald-300">Conectado com sucesso</p>
                        <p class="text-xs text-emerald-500/80">ID do Usuário MP: <?= e($user['mp_user_id']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Expira em</p>
                        <p class="text-xs text-gray-300"><?= date('d/m/Y H:i', strtotime($user['mp_expires_at'])) ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="/config/mercadopago/auth" class="px-5 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-sm font-semibold transition-all">
                        Atualizar Conexão
                    </a>
                    <form action="/config/mercadopago/disconnect" method="POST" onsubmit="return confirm('Tem certeza que deseja desconectar sua conta do Mercado Pago?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="px-5 py-2.5 bg-red-900/20 hover:bg-red-900/40 border border-red-500/30 text-red-400 rounded-xl text-sm font-semibold transition-all">
                            Desconectar Conta
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-blue-900/10 border border-blue-500/20 p-6 rounded-2xl mb-6">
                    <p class="text-sm text-gray-300 leading-relaxed">
                        Conecte sua conta do Mercado Pago para habilitar o pagamento via Pix QR Code no PDV. 
                        O sistema obterá automaticamente as permissões necessárias para gerar os códigos e confirmar os pagamentos.
                    </p>
                </div>

                <a href="/config/mercadopago/auth" class="inline-flex items-center gap-3 px-8 py-4 bg-[#009ee3] hover:bg-[#008ac5] text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-900/20 group">
                    <span>Conectar Mercado Pago</span>
                    <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="p-6 bg-dark-900/50">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Informações de Segurança</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/3 border border-white/5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-primary-400 mt-0.5"></i>
                    <div>
                        <p class="text-xs font-semibold text-white">OAuth 2.0</p>
                        <p class="text-[10px] text-gray-500">Utilizamos o padrão oficial de autorização do Mercado Pago.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/3 border border-white/5">
                    <i data-lucide="lock" class="w-4 h-4 text-primary-400 mt-0.5"></i>
                    <div>
                        <p class="text-xs font-semibold text-white">Dados Criptografados</p>
                        <p class="text-[10px] text-gray-500">Seus tokens de acesso são armazenados com segurança no banco de dados.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
