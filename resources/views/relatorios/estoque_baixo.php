<?php $pageTitle = 'Estoque Crítico'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Produtos com Estoque Crítico</h2>
        <a href="/relatorios/mais-vendidos" class="text-sm text-gray-400 hover:text-white transition-colors">Mais Vendidos →</a>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Produto</th>
                <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Atual</th>
                <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Mínimo</th>
                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Ação</th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($produtos)): ?>
                <tr>
                    <td colspan="4" class="text-center py-10 text-gray-500">
                        <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-3 text-emerald-500 opacity-40"></i>
                        <p>Todos os produtos estão com estoque OK!</p>
                    </td>
                </tr>
                <?php else: foreach ($produtos as $p): ?>
                <tr class="hover:bg-white/3 transition-colors bg-red-900/5">
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-white"><?= e($p['nome']) ?></p>
                        <p class="text-xs text-gray-500"><?= e($p['unidade']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-bold text-red-400"><?= number_format($p['estoque_atual'], 1) ?></span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-400"><?= number_format($p['estoque_minimo'], 0) ?></td>
                    <td class="px-4 py-3 text-center">
                        <a href="/estoque/<?= $p['id'] ?>/entrada" class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg transition-colors">
                            + Entrada
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
