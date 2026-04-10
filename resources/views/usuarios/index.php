<?php $pageTitle = 'Usuários'; ?>
<div class="space-y-5 fade-in-up">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Usuários do Sistema</h2>
        <a href="/usuarios/criar" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Novo Usuário
        </a>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-white/10">
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Nome</th>
                <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">E-mail</th>
                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Perfil</th>
                <th class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($usuarios as $u): ?>
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-900/50 flex items-center justify-center text-xs font-bold text-primary-300">
                                <?= strtoupper(substr($u['nome'], 0, 1)) ?>
                            </div>
                            <span class="text-sm font-medium text-white"><?= e($u['nome']) ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400"><?= e($u['email']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            <?= match($u['perfil']) {
                                'admin'   => 'bg-purple-900/40 text-purple-400',
                                'gerente' => 'bg-blue-900/40 text-blue-400',
                                'caixa'   => 'bg-emerald-900/40 text-emerald-400',
                                default   => 'bg-amber-900/40 text-amber-400',
                            } ?>">
                            <?= ucfirst($u['perfil']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full <?= $u['ativo'] ? 'bg-emerald-900/40 text-emerald-400' : 'bg-gray-700 text-gray-400' ?>">
                            <?= $u['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="/usuarios/<?= $u['id'] ?>/editar" class="p-1.5 rounded-lg hover:bg-primary-900/30 text-gray-400 hover:text-primary-400 transition-colors">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </a>
                            <?php if ($u['id'] !== auth()['id']): ?>
                            <form method="POST" action="/usuarios/<?= $u['id'] ?>/deletar" onsubmit="return confirm('Remover usuário?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-1.5 rounded-lg hover:bg-red-900/30 text-gray-400 hover:text-red-400 transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
