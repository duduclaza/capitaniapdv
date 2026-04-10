<?php $pageTitle = 'Clientes'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Clientes</h2>
        <a href="/clientes/criar" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Novo Cliente
        </a>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Nome</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Telefone</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Documento</th>
                <th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($clientes)): ?>
                <tr><td colspan="4" class="text-center py-8 text-gray-500">Nenhum cliente cadastrado</td></tr>
                <?php else: foreach ($clientes as $c): ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-white"><?= e($c['nome']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($c['telefone'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($c['documento'] ?? '-') ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="/clientes/<?= $c['id'] ?>/editar" class="p-1.5 rounded-lg hover:bg-primary-900/30 text-gray-400 hover:text-primary-400 transition-colors">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <form method="POST" action="/clientes/<?= $c['id'] ?>/deletar" onsubmit="return confirm('Remover cliente?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-900/30 text-gray-400 hover:text-red-400 transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
