<?php $pageTitle = 'Histórico - ' . ($produto['nome'] ?? ''); ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center gap-4">
        <a href="/estoque" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-lg font-bold text-white">Histórico: <?= e($produto['nome']) ?></h2>
            <p class="text-sm text-gray-400">Estoque atual: <strong class="text-white"><?= number_format($produto['estoque_atual'], 1) ?> <?= e($produto['unidade']) ?></strong></p>
        </div>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Data</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Tipo</th>
                <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Qtd</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Observação</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Operador</th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($historico)): ?>
                <tr><td colspan="5" class="text-center py-8 text-gray-500">Nenhuma movimentação</td></tr>
                <?php else: foreach ($historico as $h): 
                    $typeColors = [
                        'entrada' => 'text-emerald-400 bg-emerald-900/30',
                        'saida'   => 'text-red-400 bg-red-900/30',
                        'ajuste'  => 'text-blue-400 bg-blue-900/30',
                        'perda'   => 'text-amber-400 bg-amber-900/30',
                    ][$h['tipo']] ?? 'text-gray-400 bg-gray-800';
                ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3 text-xs text-gray-400"><?= formatDate($h['created_at']) ?></td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full <?= $typeColors ?>">
                            <?= ucfirst($h['tipo']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-right <?= in_array($h['tipo'], ['saida','perda']) ? 'text-red-400' : 'text-emerald-400' ?>">
                        <?= in_array($h['tipo'], ['saida','perda']) ? '-' : '+' ?><?= number_format(abs($h['quantidade']), 1) ?>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400"><?= e($h['observacao'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400"><?= e($h['usuario_nome'] ?? '—') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
