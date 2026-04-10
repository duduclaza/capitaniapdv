<?php $pageTitle = 'Mais Vendidos'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Produtos Mais Vendidos</h2>
    </div>
    <form method="GET" class="glass-card rounded-2xl p-4 flex items-center gap-4">
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400">De:</label>
            <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400">Até:</label>
            <input type="date" name="data_fim" value="<?= e($dataFim) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white text-sm px-4 py-2 rounded-xl transition-all">Filtrar</button>
    </form>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">#</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Produto</th>
                <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Qtd Vendida</th>
                <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Faturado</th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($produtos)): ?>
                <tr><td colspan="4" class="text-center py-8 text-gray-500">Nenhuma venda no período</td></tr>
                <?php else: foreach ($produtos as $i => $p): ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3">
                        <span class="text-sm font-bold <?= $i < 3 ? 'text-primary-400' : 'text-gray-500' ?>"><?= $i + 1 ?></span>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-white"><?= e($p['nome']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-300 text-right"><?= number_format($p['total_vendido'], 0) ?></td>
                    <td class="px-4 py-3 text-sm font-semibold text-primary-400 text-right"><?= formatMoney($p['total_faturado']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
