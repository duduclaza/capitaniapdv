<?php $pageTitle = 'Entrada de Estoque'; ?>
<div class="max-w-lg mx-auto fade-in-up">
    <div class="flex items-center gap-4 mb-6">
        <a href="/estoque" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-lg font-bold text-white">Entrada de Estoque</h2>
            <p class="text-sm text-gray-400"><?= e($produto['nome']) ?></p>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-4 mb-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-900/30 flex items-center justify-center">
            <i data-lucide="package" class="w-6 h-6 text-emerald-400"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-white"><?= e($produto['nome']) ?></p>
            <p class="text-xs text-gray-400">Estoque atual: <strong class="text-white"><?= number_format($produto['estoque_atual'], 1) ?> <?= e($produto['unidade']) ?></strong></p>
        </div>
    </div>
    <form method="POST" action="/estoque/<?= $produto['id'] ?>/entrada" class="glass-card rounded-2xl p-6 space-y-5">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-2">Quantidade a Adicionar *</label>
            <input type="number" name="quantidade" required min="0.01" step="0.01"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-2xl font-bold focus:outline-none focus:border-emerald-500 text-center">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-2">Valor Unitário (R$)</label>
            <input type="number" name="valor_unitario" min="0" step="0.01" value="<?= $produto['preco_custo'] ?>"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-emerald-500 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-2">Observação</label>
            <input type="text" name="observacao" placeholder="Ex: Compra NF 123"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm">
        </div>
        <div class="flex gap-3">
            <a href="/estoque" class="flex-1 text-center px-4 py-3 text-sm text-gray-400 border border-white/10 rounded-xl hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Registrar Entrada
            </button>
        </div>
    </form>
</div>
