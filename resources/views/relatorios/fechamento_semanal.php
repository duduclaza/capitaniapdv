<?php $pageTitle = 'Fechamento Semanal'; ?>

<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-white">Fechamento Semanal</h2>
            <p class="text-sm text-gray-400">
                Pagamento: <?= e($diasPagamento[$diaPagamento] ?? 'Sexta-feira') ?>, <?= formatDate($dataPagamento, 'd/m/Y') ?>
            </p>
        </div>
        <a href="/relatorios/vendas" class="text-sm text-gray-400 hover:text-white transition-colors">Relatorio de Vendas -></a>
    </div>

    <form method="GET" class="glass-card rounded-2xl p-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 flex-shrink-0">De:</label>
            <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 flex-shrink-0">Ate:</label>
            <input type="date" name="data_fim" value="<?= e($dataFim) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 flex-shrink-0">Colaboradores:</label>
            <input type="number" name="colaboradores" min="1" value="<?= e($colaboradores) ?>"
                   class="w-24 px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-400 flex-shrink-0">Pagamento:</label>
            <select name="dia_pagamento" class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                <?php foreach ($diasPagamento as $dia => $label): ?>
                <option value="<?= $dia ?>" <?= (int)$diaPagamento === (int)$dia ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all">Gerar</button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Itens vendidos</p>
            <p class="text-xl font-bold text-white"><?= number_format($totais['quantidade'], 0, ',', '.') ?></p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Recebido</p>
            <p class="text-xl font-bold text-primary-400"><?= formatMoney((float)$totais['valor_recebido']) ?></p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Custos</p>
            <p class="text-xl font-bold text-amber-400"><?= formatMoney((float)$totais['valor_custos']) ?></p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Taxas</p>
            <p class="text-xl font-bold text-red-300"><?= formatMoney((float)$totais['valor_taxa_maquininha'] + (float)$totais['valor_taxa_governo']) ?></p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Mao de obra</p>
            <p class="text-xl font-bold text-blue-300"><?= formatMoney((float)$totais['valor_mao_obra']) ?></p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Por colaborador</p>
            <p class="text-xl font-bold text-emerald-400"><?= formatMoney((float)$totais['mao_obra_por_colaborador']) ?></p>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
            <p class="text-sm font-semibold text-white">Resumo por produto</p>
            <p class="text-xs text-gray-500">Lucro: recebido - custos - taxas - mao de obra</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Produto</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Qtd</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Recebido</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Custos</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Taxa maq.</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Taxa gov.</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Mao de obra</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Por colab.</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Lucro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500">Nenhuma venda paga no periodo</td>
                    </tr>
                    <?php else: foreach ($produtos as $p): ?>
                    <tr class="hover:bg-white/3 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-white"><?= e($p['produto_nome']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300 text-right"><?= number_format((float)$p['quantidade'], 0, ',', '.') ?></td>
                        <td class="px-4 py-3 text-sm text-white text-right"><?= formatMoney((float)$p['valor_recebido']) ?></td>
                        <td class="px-4 py-3 text-sm text-amber-300 text-right"><?= formatMoney((float)$p['valor_custos']) ?></td>
                        <td class="px-4 py-3 text-sm text-red-300 text-right"><?= formatMoney((float)$p['valor_taxa_maquininha']) ?></td>
                        <td class="px-4 py-3 text-sm text-red-300 text-right"><?= formatMoney((float)$p['valor_taxa_governo']) ?></td>
                        <td class="px-4 py-3 text-sm text-blue-300 text-right"><?= formatMoney((float)$p['valor_mao_obra']) ?></td>
                        <td class="px-4 py-3 text-sm text-emerald-300 text-right"><?= formatMoney((float)$p['mao_obra_por_colaborador']) ?></td>
                        <td class="px-4 py-3 text-sm font-semibold text-primary-400 text-right"><?= formatMoney((float)$p['valor_lucro']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <?php if (!empty($produtos)): ?>
                <tfoot>
                    <tr class="border-t border-white/10 bg-white/5">
                        <td class="px-4 py-3 text-sm font-bold text-white">Total</td>
                        <td class="px-4 py-3 text-sm font-bold text-white text-right"><?= number_format((float)$totais['quantidade'], 0, ',', '.') ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-white text-right"><?= formatMoney((float)$totais['valor_recebido']) ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-amber-300 text-right"><?= formatMoney((float)$totais['valor_custos']) ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-red-300 text-right"><?= formatMoney((float)$totais['valor_taxa_maquininha']) ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-red-300 text-right"><?= formatMoney((float)$totais['valor_taxa_governo']) ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-blue-300 text-right"><?= formatMoney((float)$totais['valor_mao_obra']) ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-emerald-300 text-right"><?= formatMoney((float)$totais['mao_obra_por_colaborador']) ?></td>
                        <td class="px-4 py-3 text-sm font-bold text-primary-400 text-right"><?= formatMoney((float)$totais['valor_lucro']) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
