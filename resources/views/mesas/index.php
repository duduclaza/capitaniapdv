<?php $pageTitle = 'Mesas'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Mesas</h2>
        <a href="/mesas/criar" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Nova Mesa
        </a>
    </div>
    
    <!-- Mesa Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        <?php foreach ($mesas as $m): 
            $statusColors = match($m['status']) {
                'ocupada' => ['bg' => 'bg-amber-900/30', 'border' => 'border-amber-500/50', 'text' => 'text-amber-300', 'dot' => 'bg-amber-500'],
                'fechada' => ['bg' => 'bg-gray-800/50', 'border' => 'border-gray-600/50', 'text' => 'text-gray-400', 'dot' => 'bg-gray-500'],
                default   => ['bg' => 'bg-emerald-900/20', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-400', 'dot' => 'bg-emerald-500'],
            };
        ?>
        <div class="<?= $statusColors['bg'] ?> border <?= $statusColors['border'] ?> rounded-2xl p-4 card-hover">
            <div class="flex items-start justify-between mb-3">
                <span class="text-2xl font-bold text-white">Nº <?= $m['numero'] ?></span>
                <span class="w-2.5 h-2.5 rounded-full <?= $statusColors['dot'] ?> mt-1.5 flex-shrink-0"></span>
            </div>
            <p class="text-xs <?= $statusColors['text'] ?> font-medium capitalize mb-3"><?= e($m['status']) ?></p>
            <?php if ($m['status'] === 'ocupada' && !empty($m['comanda_id'])): ?>
            <div class="mb-3">
                <p class="text-xs text-gray-400">Comanda aberta</p>
                <?php if (!empty($m['comanda_subtotal'])): ?>
                <p class="text-sm font-semibold text-amber-300"><?= formatMoney($m['comanda_subtotal']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="flex gap-2">
                <?php if ($m['status'] === 'livre'): ?>
                <a href="/comandas?mesa=<?= $m['id'] ?>" class="flex-1 text-center text-xs bg-emerald-600 hover:bg-emerald-500 text-white py-1.5 rounded-lg transition-colors font-medium">Abrir</a>
                <?php elseif ($m['status'] === 'ocupada' && !empty($m['comanda_id'])): ?>
                <a href="/comandas/<?= $m['comanda_id'] ?>" class="flex-1 text-center text-xs bg-amber-600 hover:bg-amber-500 text-white py-1.5 rounded-lg transition-colors font-medium">Ver</a>
                <?php endif; ?>
                <a href="/mesas/<?= $m['id'] ?>/editar" class="p-1.5 text-gray-500 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <i data-lucide="pencil" class="w-3 h-3"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- List View --> 
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10">
            <p class="text-sm font-semibold text-white">Gerenciar Mesas</p>
        </div>
        <table class="w-full">
            <thead><tr class="border-b border-white/5">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Mesa</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Descrição</th>
                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($mesas as $m): ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3 text-sm font-bold text-white">Mesa <?= $m['numero'] ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($m['descricao'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full
                            <?= match($m['status']) {
                                'livre' => 'bg-emerald-900/40 text-emerald-400',
                                'ocupada' => 'bg-amber-900/40 text-amber-400',
                                default  => 'bg-gray-700 text-gray-400'
                            } ?>">
                            <?= ucfirst($m['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="/mesas/<?= $m['id'] ?>/editar" class="p-1.5 rounded-lg hover:bg-primary-900/30 text-gray-400 hover:text-primary-400 transition-colors">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <form method="POST" action="/mesas/<?= $m['id'] ?>/deletar" onsubmit="return confirm('Remover mesa?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-900/30 text-gray-400 hover:text-red-400 transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
