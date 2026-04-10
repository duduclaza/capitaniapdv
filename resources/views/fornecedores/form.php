<?php $isEdit = $fornecedor !== null; $pageTitle = $isEdit ? 'Editar Fornecedor' : 'Novo Fornecedor'; ?>
<div class="max-w-2xl mx-auto fade-in-up">
    <div class="flex items-center gap-4 mb-6">
        <a href="/fornecedores" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-lg font-bold text-white"><?= $pageTitle ?></h2>
    </div>
    <form method="POST" action="<?= $isEdit ? '/fornecedores/'.$fornecedor['id'] : '/fornecedores' ?>" class="glass-card rounded-2xl p-6 space-y-5">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-400 mb-2">Razão Social *</label>
                <input type="text" name="razao_social" required value="<?= e($fornecedor['razao_social'] ?? '') ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">Nome Fantasia</label>
                <input type="text" name="nome_fantasia" value="<?= e($fornecedor['nome_fantasia'] ?? '') ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">CNPJ</label>
                <input type="text" name="cnpj" value="<?= e($fornecedor['cnpj'] ?? '') ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">Telefone</label>
                <input type="text" name="telefone" value="<?= e($fornecedor['telefone'] ?? '') ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">E-mail</label>
                <input type="email" name="email" value="<?= e($fornecedor['email'] ?? '') ?>"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-400 mb-2">Observações</label>
                <textarea name="observacoes" rows="3"
                          class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors"><?= e($fornecedor['observacoes'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <a href="/fornecedores" class="flex-1 text-center px-4 py-2.5 text-sm text-gray-400 border border-white/10 rounded-xl hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all">Salvar</button>
        </div>
    </form>
</div>
