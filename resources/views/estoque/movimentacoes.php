<?php $pageTitle = 'Movimentações de Estoque'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Movimentações de Estoque</h2>
        <a href="/estoque" class="text-sm text-gray-400 hover:text-white transition-colors">← Voltar</a>
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
        <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all">Filtrar</button>
    </form>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Data</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Produto</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Tipo</th>
                <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Qtd</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Obs</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Operador</th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($historico)): ?>
                <tr><td colspan="6" class="text-center py-8 text-gray-500">Nenhuma movimentação no período</td></tr>
                <?php else: foreach ($historico as $h): 
                    $c = $h['tipo'] === 'entrada' ? 'text-emerald-400' : ($h['tipo'] === 'saida' ? 'text-red-400' : ($h['tipo'] === 'ajuste' ? 'text-blue-400' : 'text-amber-400'));
                ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3 text-xs text-gray-400"><?= formatDate($h['created_at']) ?></td>
                    <td class="px-4 py-3 text-sm text-white"><?= e($h['produto_nome']) ?></td>
                    <td class="px-4 py-3"><span class="text-xs <?= $c ?>"><?= ucfirst($h['tipo']) ?></span></td>
                    <td class="px-4 py-3 text-sm font-semibold text-right <?= $c ?>"><?= number_format($h['quantidade'], 1) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?= e($h['observacao'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400"><?= e($h['usuario_nome'] ?? '—') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
