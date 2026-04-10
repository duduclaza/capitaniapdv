<?php $isEdit = $mesa !== null; $pageTitle = $isEdit ? 'Editar Mesa' : 'Nova Mesa'; ?>
<div class="max-w-md mx-auto fade-in-up">
    <div class="flex items-center gap-4 mb-6">
        <a href="/mesas" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-lg font-bold text-white"><?= $pageTitle ?></h2>
    </div>
    <form method="POST" action="<?= $isEdit ? '/mesas/'.$mesa['id'] : '/mesas' ?>" class="glass-card rounded-2xl p-6 space-y-5">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-2">Número da Mesa *</label>
            <input type="number" name="numero" required min="1" value="<?= e($mesa['numero'] ?? '') ?>"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-2">Descrição</label>
            <input type="text" name="descricao" value="<?= e($mesa['descricao'] ?? '') ?>"
                   placeholder="Ex: Mesa da varanda"
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
        </div>
        <div class="flex gap-3 pt-2">
            <a href="/mesas" class="flex-1 text-center px-4 py-2.5 text-sm text-gray-400 border border-white/10 rounded-xl hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">Salvar</button>
        </div>
    </form>
</div>
