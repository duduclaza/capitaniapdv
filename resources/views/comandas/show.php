<?php $pageTitle = 'Comanda #' . $comanda['id']; ?>
<div class="space-y-5 fade-in-up">
    
    <!-- Header -->
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-4">
            <a href="/comandas" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-lg font-bold text-white">Mesa <?= $comanda['mesa_numero'] ?> · Comanda #<?= $comanda['id'] ?></h2>
                <p class="text-sm text-gray-400">Aberta às <?= formatDate($comanda['opened_at'], 'H:i') ?> por <?= e($comanda['operador_nome']) ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($comanda['status'] === 'aberta'): ?>
            <a href="/comandas/<?= $comanda['id'] ?>/fechar"
               class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Fechar & Pagar
            </a>
            <form method="POST" action="/comandas/<?= $comanda['id'] ?>/cancelar" onsubmit="return confirm('Cancelar comanda?')">
                <?= csrf_field() ?>
                <button type="submit" class="flex items-center gap-2 bg-red-900/30 hover:bg-red-900/50 text-red-400 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Cancelar
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        
        <!-- Items List -->
        <div class="lg:col-span-2 glass-card rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-white">Itens da Comanda</h3>
                <span class="text-xs text-gray-500"><?= count($itens) ?> itens</span>
            </div>
            
            <?php if (empty($itens)): ?>
            <div class="p-8 text-center text-gray-500">
                <i data-lucide="coffee" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
                <p>Nenhum item adicionado</p>
            </div>
            <?php else: ?>
            <div class="divide-y divide-white/5">
                <?php foreach ($itens as $item): ?>
                <div class="flex items-center gap-4 p-4 hover:bg-white/3 transition-colors">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white"><?= e($item['produto_nome']) ?></p>
                        <?php if ($item['observacao']): ?>
                        <p class="text-xs text-amber-400 mt-0.5">Obs: <?= e($item['observacao']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <?= number_format($item['quantidade'], 0) ?> <?= e($item['unidade']) ?> 
                            × <?= formatMoney($item['preco_unitario']) ?>
                        </p>
                    </div>
                    <span class="text-sm font-semibold text-white"><?= formatMoney($item['total_item']) ?></span>
                    <?php if ($comanda['status'] === 'aberta'): ?>
                    <form method="POST" action="/comandas/<?= $comanda['id'] ?>/item/<?= $item['id'] ?>/remover">
                        <?= csrf_field() ?>
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-900/30 text-gray-500 hover:text-red-400 transition-colors">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar: Totals + Add Item -->
        <div class="space-y-4">
            
            <!-- Totals Card -->
            <div class="glass-card rounded-2xl p-5 space-y-3">
                <h3 class="font-semibold text-white border-b border-white/10 pb-3">Totais</h3>
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Subtotal</span>
                    <span><?= formatMoney($comanda['subtotal']) ?></span>
                </div>
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Desconto</span>
                    <span class="text-red-400">- <?= formatMoney($comanda['desconto']) ?></span>
                </div>
                <div class="flex justify-between text-xl font-bold text-white border-t border-white/10 pt-2">
                    <span>Total</span>
                    <span class="text-primary-400"><?= formatMoney($comanda['total'] ?: $comanda['subtotal']) ?></span>
                </div>
            </div>

            <!-- Add Item -->
            <?php if ($comanda['status'] === 'aberta'): ?>
            <div class="glass-card rounded-2xl p-5 space-y-4">
                <h3 class="font-semibold text-white">Adicionar Item</h3>
                <form method="POST" action="/comandas/<?= $comanda['id'] ?>/item" class="space-y-3">
                    <?= csrf_field() ?>
                    <select name="produto_id" required class="w-full px-3 py-2.5 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                        <option value="">Selecione produto</option>
                        <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['nome']) ?> - <?= formatMoney($p['preco_venda']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Quantidade</label>
                            <input type="number" name="quantidade" value="1" min="0.1" step="0.1"
                                   class="w-full px-3 py-2.5 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Observação</label>
                            <input type="text" name="observacao" placeholder="Ex: sem gelo"
                                   class="w-full px-3 py-2.5 bg-dark-900 border border-white/10 rounded-xl text-sm text-white placeholder-gray-600 focus:outline-none focus:border-primary-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold py-3 rounded-xl transition-all">
                        + Adicionar Item
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
