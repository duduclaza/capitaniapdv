<div class="max-w-4xl mx-auto space-y-6 fade-in-up">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white">Configurações do Sistema</h2>
            <p class="text-gray-400">Gerencie integrações e preferências da sua conta.</p>
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
                        Conecte sua conta do Mercado Pago para habilitar o pagamento via **Pix QR Code** no PDV. 
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
