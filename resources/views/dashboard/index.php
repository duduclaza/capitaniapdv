<?php $pageTitle = 'Dashboard'; ?>

<div class="space-y-6 fade-in-up">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="glass-card rounded-2xl p-5 card-hover">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Faturamento Hoje</p>
                    <p class="text-2xl font-bold text-white mt-1"><?= formatMoney($faturamento_dia) ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?= $vendas_dia ?> vendas realizadas</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="trending-up" class="w-5 h-5 text-emerald-400"></i>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 card-hover">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Comandas Abertas</p>
                    <p class="text-2xl font-bold text-white mt-1"><?= $comandas_abertas ?></p>
                    <p class="text-xs text-gray-500 mt-1">em andamento</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-amber-400"></i>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 card-hover">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Mesas Ocupadas</p>
                    <p class="text-2xl font-bold text-white mt-1"><?= $mesas_ocupadas ?></p>
                    <p class="text-xs text-gray-500 mt-1">em uso agora</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="grid-3x3" class="w-5 h-5 text-blue-400"></i>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-5 card-hover">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Estoque Baixo</p>
                    <p class="text-2xl font-bold text-white mt-1"><?= count($estoque_baixo) ?></p>
                    <p class="text-xs <?= count($estoque_baixo) > 0 ? 'text-red-400' : 'text-gray-500' ?> mt-1">
                        <?= count($estoque_baixo) > 0 ? 'produtos críticos!' : 'tudo ok' ?>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-red-500/10 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Ações Rápidas</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="/pdv" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-primary-600/10 hover:bg-primary-600/20 border border-primary-500/20 transition-all group">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-primary-400 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-semibold text-white text-center">Abrir PDV</span>
            </a>
            <a href="/comandas" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 transition-all group">
                <i data-lucide="clipboard-list" class="w-6 h-6 text-amber-400 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-semibold text-white text-center">Ver Comandas</span>
            </a>
            <a href="/caixa" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 transition-all group">
                <i data-lucide="landmark" class="w-6 h-6 text-emerald-400 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-semibold text-white text-center">Fluxo de Caixa</span>
            </a>
            <a href="/produtos/criar" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 transition-all group">
                <i data-lucide="plus-circle" class="w-6 h-6 text-blue-400 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-semibold text-white text-center">Novo Produto</span>
            </a>
            <a href="/relatorios/vendas" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/20 transition-all group">
                <i data-lucide="bar-chart-2" class="w-6 h-6 text-purple-400 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-semibold text-white text-center">Relatórios</span>
            </a>
            <a href="/estoque" class="flex flex-col items-center gap-2 p-4 rounded-2xl bg-gray-500/10 hover:bg-gray-500/20 border border-gray-500/20 transition-all group">
                <i data-lucide="boxes" class="w-6 h-6 text-gray-400 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-semibold text-white text-center">Estoque</span>
            </a>
        </div>
    </div>

    <!-- Middle Row -->
    <div class="grid lg:grid-cols-3 gap-6">
        
        <!-- Sales Chart -->
        <div class="lg:col-span-2 glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-white">Faturamento dos Últimos 7 Dias</h2>
            </div>
            <div class="relative h-48">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="glass-card rounded-2xl p-6">
            <h2 class="font-semibold text-white mb-4">Formas de Pagamento (Hoje)</h2>
            <div class="space-y-3">
                <?php if (empty($resumo_caixa)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">Nenhuma venda hoje</p>
                <?php else: ?>
                    <?php foreach ($resumo_caixa as $r): 
                        $label = match($r['forma_pagamento']) {
                            'dinheiro'   => '💵 Dinheiro',
                            'maquininha' => match($r['subforma_pagamento']) {
                                'debito'         => '💳 Débito',
                                'credito'        => '💳 Crédito',
                                'pix_maquininha' => '📱 Pix Maquininha',
                                default          => '💳 Maquininha',
                            },
                            'stripe_qr'  => '📲 Pix QR Stripe',
                            default      => $r['forma_pagamento'],
                        };
                    ?>
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-sm text-gray-400"><?= $label ?></span>
                        <span class="text-sm font-semibold text-white"><?= formatMoney($r['total']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid lg:grid-cols-2 gap-6">
        
        <!-- Recent Sales -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-white">Últimas Vendas</h2>
                <a href="/relatorios/vendas" class="text-xs text-primary-400 hover:text-primary-300">Ver todas →</a>
            </div>
            <div class="space-y-2">
                <?php if (empty($ultimas_vendas)): ?>
                    <p class="text-sm text-gray-500 text-center py-6">Nenhuma venda hoje</p>
                <?php else: ?>
                    <?php foreach (array_slice($ultimas_vendas, 0, 6) as $v): ?>
                    <div class="flex items-center justify-between py-2.5 border-b border-white/5 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg <?= $v['status'] === 'paga' ? 'bg-emerald-500/10' : 'bg-amber-500/10' ?> flex items-center justify-center">
                                <i data-lucide="<?= $v['status'] === 'paga' ? 'check' : 'clock' ?>" class="w-3.5 h-3.5 <?= $v['status'] === 'paga' ? 'text-emerald-400' : 'text-amber-400' ?>"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">#<?= $v['id'] ?></p>
                                <p class="text-xs text-gray-500"><?= formatDate($v['created_at'], 'H:i') ?> · <?= e($v['operador_nome'] ?? '-') ?></p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-white"><?= formatMoney($v['valor_final']) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-white">⚠️ Estoque Crítico</h2>
                <a href="/estoque" class="text-xs text-primary-400 hover:text-primary-300">Gerenciar →</a>
            </div>
            <div class="space-y-2">
                <?php if (empty($estoque_baixo)): ?>
                    <div class="flex items-center gap-3 py-4 text-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 mx-auto"></i>
                    </div>
                    <p class="text-sm text-emerald-400 text-center">Todos os produtos com estoque OK!</p>
                <?php else: ?>
                    <?php foreach (array_slice($estoque_baixo, 0, 6) as $p): ?>
                    <div class="flex items-center justify-between py-2.5 border-b border-white/5 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-white"><?= e($p['nome']) ?></p>
                            <p class="text-xs text-gray-500">SKU: <?= e($p['sku'] ?? '-') ?></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block bg-red-900/50 text-red-300 text-xs font-semibold px-2 py-0.5 rounded-lg">
                                <?= number_format($p['estoque_atual'], 0) ?> <?= e($p['unidade']) ?>
                            </span>
                            <p class="text-xs text-gray-500 mt-0.5">mín: <?= number_format($p['estoque_minimo'], 0) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const chartData = <?= json_encode($faturamento_semana) ?>;

const labels = chartData.map(d => {
    const dt = new Date(d.data + 'T00:00:00');
    return dt.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit' });
});
const values = chartData.map(d => parseFloat(d.total));

const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Faturamento',
            data: values,
            backgroundColor: 'rgba(192, 38, 211, 0.5)',
            borderColor: '#c026d3',
            borderWidth: 2,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { size: 11 },
                callback: v => 'R$' + v.toLocaleString('pt-BR') } }
        }
    }
});
</script>
