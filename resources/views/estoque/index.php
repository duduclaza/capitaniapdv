<?php $pageTitle = 'Estoque'; ?>
<div class="space-y-5 fade-in-up">
    
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Controle de Estoque</h2>
        <div class="flex items-center gap-3">
            <a href="/estoque/movimentacoes" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white border border-white/10 px-4 py-2 rounded-xl transition-colors">
                <i data-lucide="history" class="w-4 h-4"></i> Movimentações
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($baixo)): ?>
    <div class="bg-red-900/20 border border-red-500/30 rounded-2xl p-4 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-semibold text-red-300"><?= count($baixo) ?> produtos com estoque crítico!</p>
            <p class="text-xs text-red-400 mt-0.5"><?= implode(', ', array_map(fn($p) => e($p['nome']), array_slice($baixo, 0, 5))) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="border-b border-white/10">
                    <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Produto</th>
                    <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Categoria</th>
                    <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Atual</th>
                    <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Mínimo</th>
                    <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                    <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Ações</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($produtos as $p): 
                        $critico = $p['controla_estoque'] && $p['estoque_atual'] <= $p['estoque_minimo'];
                    ?>
                    <tr class="hover:bg-white/3 transition-colors <?= $critico ? 'bg-red-900/10' : '' ?>">
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-white"><?= e($p['nome']) ?></p>
                            <p class="text-xs text-gray-500">SKU: <?= e($p['sku'] ?? '-') ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-primary-900/40 text-primary-300 px-2 py-0.5 rounded-lg"><?= e($p['categoria_nome'] ?? '-') ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($p['controla_estoque']): ?>
                                <span class="text-sm font-bold <?= $critico ? 'text-red-400' : 'text-white' ?>">
                                    <?= number_format($p['estoque_atual'], 1) ?>
                                </span>
                                <span class="text-xs text-gray-500 ml-1"><?= e($p['unidade']) ?></span>
                            <?php else: ?>
                                <span class="text-xs text-gray-600">Não controla</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-400">
                            <?= $p['controla_estoque'] ? number_format($p['estoque_minimo'], 0) : '—' ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if (!$p['controla_estoque']): ?>
                                <span class="text-xs text-gray-500">—</span>
                            <?php elseif ($critico): ?>
                                <span class="text-xs bg-red-900/40 text-red-400 px-2 py-0.5 rounded-full font-medium">⚠ Crítico</span>
                            <?php else: ?>
                                <span class="text-xs bg-emerald-900/40 text-emerald-400 px-2 py-0.5 rounded-full font-medium">✓ OK</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1 justify-center">
                                <a href="/estoque/<?= $p['id'] ?>/entrada" title="Entrada" class="p-1.5 rounded-lg hover:bg-emerald-900/30 text-gray-400 hover:text-emerald-400 transition-colors">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                </a>
                                <a href="/estoque/<?= $p['id'] ?>/ajuste" title="Ajuste" class="p-1.5 rounded-lg hover:bg-blue-900/30 text-gray-400 hover:text-blue-400 transition-colors">
                                    <i data-lucide="sliders" class="w-4 h-4"></i>
                                </a>
                                <a href="/estoque/<?= $p['id'] ?>/perda" title="Perda/Quebra" class="p-1.5 rounded-lg hover:bg-amber-900/30 text-gray-400 hover:text-amber-400 transition-colors">
                                    <i data-lucide="trash" class="w-4 h-4"></i>
                                </a>
                                <a href="/estoque/<?= $p['id'] ?>/historico" title="Histórico" class="p-1.5 rounded-lg hover:bg-primary-900/30 text-gray-400 hover:text-primary-400 transition-colors">
                                    <i data-lucide="history" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
