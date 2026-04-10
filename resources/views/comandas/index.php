<?php $pageTitle = 'Comandas'; ?>
<div class="space-y-5 fade-in-up">
    
    <!-- Header + Open Comanda -->
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Comandas Abertas</h2>
        <button onclick="document.getElementById('modalAbrirComanda').classList.remove('hidden')"
                class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Abrir Comanda
        </button>
    </div>

    <!-- Comandas Grid -->
    <?php if (empty($comandas)): ?>
    <div class="glass-card rounded-2xl p-12 text-center">
        <i data-lucide="clipboard-list" class="w-12 h-12 mx-auto mb-4 text-gray-600 opacity-40"></i>
        <p class="text-gray-400">Nenhuma comanda aberta</p>
        <p class="text-sm text-gray-600 mt-1">Abra uma nova comanda para começar</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($comandas as $c): ?>
        <a href="/comandas/<?= $c['id'] ?>" class="glass-card rounded-2xl p-5 card-hover block border border-amber-500/20 hover:border-amber-500/50 transition-all">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-lg font-bold text-white">Mesa <?= $c['mesa_numero'] ?></p>
                    <p class="text-xs text-gray-500"><?= $c['cliente_nome'] ? 'Cliente: '.e($c['cliente_nome']) : 'Sem cliente' ?></p>
                </div>
                <span class="text-xs bg-amber-900/40 text-amber-300 px-2 py-1 rounded-lg">Aberta</span>
            </div>
            <div class="border-t border-white/10 pt-3 mt-3">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500">Total atual</p>
                        <p class="text-xl font-bold text-primary-400"><?= formatMoney($c['subtotal']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Aberta às</p>
                        <p class="text-sm text-gray-300"><?= formatDate($c['opened_at'], 'H:i') ?></p>
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-2">Operador: <?= e($c['operador_nome']) ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Abrir Comanda -->
<div id="modalAbrirComanda" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-dark-800 border border-white/10 rounded-3xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b border-white/10">
            <h3 class="text-lg font-bold text-white">Abrir Nova Comanda</h3>
            <button onclick="document.getElementById('modalAbrirComanda').classList.add('hidden')" 
                    class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="/comandas/abrir" class="p-6 space-y-5">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">Mesa *</label>
                <select name="mesa_id" required class="w-full px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-white focus:outline-none focus:border-primary-500 text-sm">
                    <option value="">Selecione a mesa</option>
                    <?php foreach ($mesas as $m): if ($m['status'] === 'livre'): ?>
                    <option value="<?= $m['id'] ?>">Mesa <?= $m['numero'] ?><?= $m['descricao'] ? ' - '.$m['descricao'] : '' ?></option>
                    <?php endif; endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">Cliente (opcional)</label>
                <select name="cliente_id" class="w-full px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-white focus:outline-none focus:border-primary-500 text-sm">
                    <option value="">-- Sem cliente --</option>
                    <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modalAbrirComanda').classList.add('hidden')"
                        class="flex-1 px-4 py-3 text-sm text-gray-400 border border-white/10 rounded-xl hover:text-white transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-3 rounded-xl transition-all">
                    Abrir Comanda
                </button>
            </div>
        </form>
    </div>
</div>
