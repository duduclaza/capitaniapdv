<?php $pageTitle = 'Caixa'; ?>
<div class="space-y-5 fade-in-up">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-white">Caixa — <?= formatDate($data, 'd/m/Y') ?></h2>
        <form method="GET" class="flex items-center gap-3">
            <input type="date" name="data" value="<?= e($data) ?>"
                   class="px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
            <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white text-sm px-4 py-2 rounded-xl transition-all">Filtrar</button>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $entradas = ($totais['abertura'] ?? 0) + ($totais['venda'] ?? 0) + ($totais['suprimento'] ?? 0);
        $saidas   = abs($totais['sangria'] ?? 0);
        $saldo    = $entradas - $saidas;
        ?>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Entrada Total</p>
            <p class="text-2xl font-bold text-emerald-400"><?= formatMoney($entradas) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Sangrias</p>
            <p class="text-2xl font-bold text-red-400"><?= formatMoney($saidas) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Vendas</p>
            <p class="text-2xl font-bold text-white"><?= formatMoney($totais['venda'] ?? 0) ?></p>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-primary-500/30">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Saldo Caixa</p>
            <p class="text-2xl font-bold text-primary-400"><?= formatMoney($saldo) ?></p>
        </div>
    </div>

    <!-- Actions -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Abertura -->
        <button onclick="openModal('modalAbertura')" class="glass-card rounded-xl p-4 flex items-center gap-3 hover:border-emerald-500/50 border border-white/5 transition-all">
            <div class="w-10 h-10 bg-emerald-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="unlock" class="w-5 h-5 text-emerald-400"></i>
            </div>
            <span class="text-sm font-medium text-white">Abertura</span>
        </button>
        <!-- Sangria -->
        <button onclick="openModal('modalSangria')" class="glass-card rounded-xl p-4 flex items-center gap-3 hover:border-red-500/50 border border-white/5 transition-all">
            <div class="w-10 h-10 bg-red-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="arrow-down-circle" class="w-5 h-5 text-red-400"></i>
            </div>
            <span class="text-sm font-medium text-white">Sangria</span>
        </button>
        <!-- Suprimento -->
        <button onclick="openModal('modalSuprimento')" class="glass-card rounded-xl p-4 flex items-center gap-3 hover:border-blue-500/50 border border-white/5 transition-all">
            <div class="w-10 h-10 bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="arrow-up-circle" class="w-5 h-5 text-blue-400"></i>
            </div>
            <span class="text-sm font-medium text-white">Suprimento</span>
        </button>
        <!-- Fechamento -->
        <button onclick="if(confirm('Fechar o caixa?')) document.getElementById('formFechamento').submit()"
                class="glass-card rounded-xl p-4 flex items-center gap-3 hover:border-amber-500/50 border border-white/5 transition-all">
            <div class="w-10 h-10 bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="lock" class="w-5 h-5 text-amber-400"></i>
            </div>
            <span class="text-sm font-medium text-white">Fechar Caixa</span>
        </button>
    </div>
    <form id="formFechamento" method="POST" action="/caixa/fechamento" class="hidden"><?= csrf_field() ?></form>

    <div class="grid lg:grid-cols-2 gap-5">
        <!-- Resumo por Forma Pagamento -->
        <div class="glass-card rounded-2xl p-5">
            <h3 class="font-semibold text-white mb-4 border-b border-white/10 pb-3">Resumo por Forma de Pagamento</h3>
            <div class="space-y-2">
                <?php if (empty($formas)): ?>
                <p class="text-sm text-gray-500 text-center py-4">Nenhuma venda no período</p>
                <?php else: foreach ($formas as $f): 
                    $label = match($f['forma_pagamento']) {
                        'dinheiro'   => '💵 Dinheiro',
                        'maquininha' => match($f['subforma_pagamento'] ?? '') {
                            'debito'  => '💳 Débito',
                            'credito' => '💳 Crédito',
                            'pix_maquininha' => '📱 Pix Maquininha',
                            default   => '💳 Maquininha',
                        },
                        'stripe_qr' => '📲 Pix QR (Stripe)',
                        default     => $f['forma_pagamento'],
                    };
                ?>
                <div class="flex justify-between items-center py-2 border-b border-white/5">
                    <span class="text-sm text-gray-400"><?= $label ?></span>
                    <span class="text-sm font-semibold text-white"><?= formatMoney($f['total']) ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Movimentos Recentes -->
        <div class="glass-card rounded-2xl p-5">
            <h3 class="font-semibold text-white mb-4 border-b border-white/10 pb-3">Movimentos do Dia</h3>
            <div class="space-y-2 max-h-72 overflow-y-auto">
                <?php foreach (array_slice($movimentos, 0, 15) as $m): 
                    $isPositive = in_array($m['tipo'], ['abertura', 'venda', 'suprimento']);
                ?>
                <div class="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
                    <div>
                        <p class="text-xs font-medium text-white capitalize"><?= $m['tipo'] ?></p>
                        <p class="text-xs text-gray-500"><?= formatDate($m['created_at'], 'H:i') ?> · <?= e($m['usuario_nome'] ?? '-') ?></p>
                    </div>
                    <span class="text-sm font-semibold <?= $isPositive ? 'text-emerald-400' : 'text-red-400' ?>">
                        <?= $isPositive ? '+' : '' ?><?= formatMoney(abs($m['valor'])) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php foreach ([
    ['modalAbertura', 'Abertura de Caixa', 'abertura', 'Valor em caixa no início', 'Registrar Abertura', 'bg-emerald-600 hover:bg-emerald-500'],
    ['modalSangria', 'Sangria de Caixa', 'sangria', 'Valor retirado', 'Registrar Sangria', 'bg-red-600 hover:bg-red-500'],
    ['modalSuprimento', 'Suprimento de Caixa', 'suprimento', 'Valor adicionado', 'Registrar Suprimento', 'bg-blue-600 hover:bg-blue-500'],
] as [$id, $title, $action, $label, $btn, $btnClass]): ?>
<div id="<?= $id ?>" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-dark-800 border border-white/10 rounded-3xl w-full max-w-md shadow-2xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-white"><?= $title ?></h3>
            <button onclick="closeModal('<?= $id ?>')" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="/caixa/<?= $action ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs text-gray-400 mb-2"><?= $label ?> (R$)</label>
                <input type="number" name="valor" required min="0" step="0.01"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-xl font-bold focus:outline-none focus:border-primary-500 text-center">
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-2">Observação</label>
                <input type="text" name="observacao" value="<?= $title ?>"
                       class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('<?= $id ?>')" class="flex-1 py-3 text-sm text-gray-400 border border-white/10 rounded-xl hover:text-white transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 <?= $btnClass ?> text-white text-sm font-semibold py-3 rounded-xl transition-all"><?= $btn ?></button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
