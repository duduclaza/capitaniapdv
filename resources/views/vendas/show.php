<?php $pageTitle = 'Venda #' . $venda['id']; ?>
<div class="max-w-2xl mx-auto fade-in-up">
    <div class="flex items-center gap-4 mb-6">
        <a href="/relatorios/vendas" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-lg font-bold text-white">Venda #<?= $venda['id'] ?></h2>
        <div class="flex-1"></div>
        <a href="/vendas/<?= $venda['id'] ?>/imprimir" target="_blank"
           class="flex items-center gap-2 bg-white/5 hover:bg-white/10 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all">
            <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Cupom
        </a>
    </div>

    <div class="glass-card rounded-2xl p-6 mb-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                    <?= match($venda['status']) { 'paga' => 'bg-emerald-900/40 text-emerald-400', 'cancelada' => 'bg-red-900/40 text-red-400', default => 'bg-amber-900/40 text-amber-400' } ?>">
                    <?= ucfirst($venda['status']) ?>
                </span>
                <p class="text-sm text-gray-400 mt-2"><?= formatDate($venda['created_at']) ?></p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-primary-400"><?= formatMoney($venda['valor_final']) ?></p>
                <?php if ($venda['desconto'] > 0): ?>
                <p class="text-xs text-gray-500">Desconto: <?= formatMoney($venda['desconto']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 text-sm border-t border-white/10 pt-4">
            <div>
                <p class="text-xs text-gray-500">Pagamento</p>
                <p class="text-white font-medium capitalize"><?= e($venda['forma_pagamento']) ?><?= $venda['subforma_pagamento'] ? ' / '.e($venda['subforma_pagamento']) : '' ?></p>
            </div>
            <?php if ($venda['troco']): ?>
            <div>
                <p class="text-xs text-gray-500">Troco</p>
                <p class="text-white font-medium"><?= formatMoney($venda['troco']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10">
            <p class="text-sm font-semibold text-white">Itens</p>
        </div>
        <div class="divide-y divide-white/5">
            <?php foreach ($itens as $item): ?>
            <div class="flex justify-between items-center px-4 py-3">
                <div>
                    <p class="text-sm text-white"><?= e($item['produto_nome']) ?></p>
                    <p class="text-xs text-gray-500"><?= number_format($item['quantidade'], 1) ?> × <?= formatMoney($item['preco_unitario']) ?></p>
                </div>
                <span class="text-sm font-semibold text-white"><?= formatMoney($item['total_item']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
