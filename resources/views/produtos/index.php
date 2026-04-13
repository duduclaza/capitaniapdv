<?php $pageTitle = 'Produtos'; ?>

<div class="space-y-5 fade-in-up">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-white">Produtos</h2>
            <p class="text-sm text-gray-400"><?= count($produtos) ?> produtos cadastrados</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/produtos/criar" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> Novo Produto
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10">
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3 w-12">Img</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Produto</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Categoria</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">SKU</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Custo</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Venda</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">% Lucro</th>
                        <th class="text-right text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Estoque</th>
                        <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-12 text-gray-500">
                            <i data-lucide="package" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
                            <p>Nenhum produto cadastrado</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($produtos as $p): 
                        $estoqueOk = !$p['controla_estoque'] || $p['estoque_atual'] > $p['estoque_minimo'];
                    ?>
                    <tr class="hover:bg-white/3 transition-colors">
                        <td class="px-4 py-3">
                            <?php if (!empty($p['imagem_blob'])): ?>
                                <img src="/produtos/<?= $p['id'] ?>/imagem" alt="<?= e($p['nome']) ?>" 
                                     class="w-10 h-10 rounded-lg object-cover bg-white/5">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center">
                                    <i data-lucide="image" class="w-4 h-4 text-gray-600"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-white"><?= e($p['nome']) ?></p>
                            <p class="text-xs text-gray-500"><?= e($p['unidade']) ?></p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs bg-primary-900/40 text-primary-300 px-2 py-1 rounded-lg">
                                <?= e($p['categoria_nome'] ?? 'Sem categoria') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400"><?= e($p['sku'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300 text-right"><?= formatMoney($p['preco_custo']) ?></td>
                        <td class="px-4 py-3 text-sm font-semibold text-white text-right"><?= formatMoney($p['preco_venda']) ?></td>
                        <td class="px-4 py-3 text-right">
                            <?php
                                $lucro = $p['percent_lucro'] ?? 0;
                                if ($lucro == 0 && $p['preco_custo'] > 0 && $p['preco_venda'] > 0) {
                                    $lucro = round((($p['preco_venda'] - $p['preco_custo']) / $p['preco_venda']) * 100, 1);
                                }
                            ?>
                            <span class="text-xs font-semibold <?= $lucro >= 30 ? 'text-emerald-400' : ($lucro >= 15 ? 'text-amber-400' : 'text-red-400') ?>">
                                <?= number_format($lucro, 1) ?>%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php if ($p['controla_estoque']): ?>
                                <span class="text-sm font-medium <?= $estoqueOk ? 'text-white' : 'text-red-400' ?>">
                                    <?= number_format($p['estoque_atual'], 0) ?>
                                </span>
                                <?php if (!$estoqueOk): ?>
                                    <i data-lucide="alert-triangle" class="w-3 h-3 text-red-400 inline-block ml-1"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-xs text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full <?= $p['ativo'] ? 'bg-emerald-900/40 text-emerald-400' : 'bg-gray-700 text-gray-400' ?>">
                                <?= $p['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="/produtos/<?= $p['id'] ?>/editar" 
                                   class="p-1.5 rounded-lg hover:bg-primary-900/30 text-gray-400 hover:text-primary-400 transition-colors">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </a>
                                <form method="POST" action="/produtos/<?= $p['id'] ?>/deletar" 
                                      onsubmit="return confirm('Remover produto? Se houver historico, ele sera inativado.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-900/30 text-gray-400 hover:text-red-400 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
