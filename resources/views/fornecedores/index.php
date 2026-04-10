<?php $pageTitle = 'Fornecedores'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Fornecedores</h2>
        <a href="/fornecedores/criar" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i> Novo Fornecedor
        </a>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Razão Social</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Nome Fantasia</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">CNPJ</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Telefone</th>
                <th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($fornecedores)): ?>
                <tr><td colspan="5" class="text-center py-8 text-gray-500">Nenhum fornecedor cadastrado</td></tr>
                <?php else: foreach ($fornecedores as $f): ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3 text-sm font-medium text-white"><?= e($f['razao_social']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($f['nome_fantasia'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($f['cnpj'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($f['telefone'] ?? '-') ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="/fornecedores/<?= $f['id'] ?>/editar" class="p-1.5 rounded-lg hover:bg-primary-900/30 text-gray-400 hover:text-primary-400 transition-colors">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <form method="POST" action="/fornecedores/<?= $f['id'] ?>/deletar" onsubmit="return confirm('Remover fornecedor?')">
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
