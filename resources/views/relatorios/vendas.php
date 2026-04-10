<?php $pageTitle = 'Relatório de Vendas'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Relatório de Vendas</h2>
        <div class="flex items-center gap-3">
            <a href="/relatorios/mais-vendidos" class="text-sm text-gray-400 hover:text-white transition-colors">Mais Vendidos →</a>
            <a href="/relatorios/estoque-baixo" class="text-sm text-gray-400 hover:text-white transition-colors">Estoque Baixo →</a>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="glass-card rounded-2xl p-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 flex-shrink-0">De:</label>
            <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 flex-shrink-0">Até:</label>
            <input type="date" name="data_fim" value="<?= e($dataFim) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all">Filtrar</button>
        <a href="/relatorios/exportar-vendas?data_inicio=<?= e($dataInicio) ?>&data_fim=<?= e($dataFim) ?>"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-white border border-white/10 px-4 py-2 rounded-xl transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i> Exportar CSV
        </a>
    </form>

    <!-- Summary by payment -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <?php
        $totaisResumo = [];
        foreach ($resumo as $r) {
            $key = $r['forma_pagamento'] . '_' . ($r['subforma_pagamento'] ?? '');
            $totaisResumo[$key] = ['label' => '', 'total' => $r['total'], 'qnt' => $r['quantidade']];
        }
        $totalGeral = array_sum(array_column($resumo, 'total'));
        ?>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Total Geral</p>
            <p class="text-xl font-bold text-primary-400"><?= formatMoney($totalGeral) ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= count($vendas) ?> vendas</p>
        </div>
        <?php foreach ($resumo as $r): 
            $label = match($r['forma_pagamento']) {
                'dinheiro' => '💵 Dinheiro',
                'maquininha' => match($r['subforma_pagamento'] ?? '') {
                    'debito'  => '💳 Débito',
                    'credito' => '💳 Crédito',
                    'pix_maquininha' => '📱 Pix Máquina',
                    default => '💳 Maquininha',
                },
                'stripe_qr' => '📲 Pix Stripe',
                default => $r['forma_pagamento'],
            };
        ?>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1"><?= $label ?></p>
            <p class="text-xl font-bold text-white"><?= formatMoney($r['total']) ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= $r['quantidade'] ?> vendas</p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Chart -->
    <?php if (!empty($grafico)): ?>
    <div class="glass-card rounded-2xl p-5">
        <h3 class="font-semibold text-white mb-4">Faturamento por Dia</h3>
        <div class="relative h-48">
            <canvas id="chart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10">
            <p class="text-sm font-semibold text-white"><?= count($vendas) ?> vendas no período</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="border-b border-white/5">
                    <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">ID</th>
                    <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Data/Hora</th>
                    <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Operador</th>
                    <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Pagamento</th>
                    <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Total</th>
                    <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($vendas)): ?>
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">Nenhuma venda no período</td></tr>
                    <?php else: foreach ($vendas as $v): ?>
                    <tr class="hover:bg-white/3 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400">#<?= $v['id'] ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= formatDate($v['created_at']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= e($v['operador_nome'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            <?= e($v['forma_pagamento']) ?>
                            <?= $v['subforma_pagamento'] ? ' / '.e($v['subforma_pagamento']) : '' ?>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-white text-right"><?= formatMoney($v['valor_final']) ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium 
                                <?= match($v['status']) {
                                    'paga' => 'bg-emerald-900/40 text-emerald-400',
                                    'cancelada' => 'bg-red-900/40 text-red-400',
                                    default => 'bg-amber-900/40 text-amber-400'
                                } ?>">
                                <?= ucfirst($v['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
<?php if (!empty($grafico)): ?>
const g = <?= json_encode($grafico) ?>;
new Chart(document.getElementById('chart').getContext('2d'), {
    type: 'line',
    data: {
        labels: g.map(d => new Date(d.data+'T00:00:00').toLocaleDateString('pt-BR', {day:'2-digit',month:'2-digit'})),
        datasets: [{
            data: g.map(d => parseFloat(d.total)),
            borderColor: '#c026d3', backgroundColor: 'rgba(192,38,211,0.1)',
            borderWidth: 2, fill: true, tension: 0.3, pointRadius: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { size: 11 } } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af', font: { size: 11 },
                callback: v => 'R$' + v.toLocaleString('pt-BR') } }
        }
    }
});
<?php endif; ?>
</script>
