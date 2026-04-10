<?php $pageTitle = 'Fechar Comanda #' . $comanda['id']; ?>
<div class="max-w-2xl mx-auto fade-in-up">
    <div class="flex items-center gap-4 mb-6">
        <a href="/comandas/<?= $comanda['id'] ?>" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-lg font-bold text-white">Fechar & Pagar — Mesa <?= $comanda['mesa_numero'] ?></h2>
    </div>
    
    <!-- Items summary -->
    <div class="glass-card rounded-2xl p-5 mb-5">
        <h3 class="font-semibold text-white mb-3 border-b border-white/10 pb-3">Resumo da Comanda</h3>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            <?php foreach ($itens as $item): ?>
            <div class="flex justify-between text-sm py-1">
                <span class="text-gray-400"><?= e($item['produto_nome']) ?> × <?= number_format($item['quantidade'], 0) ?></span>
                <span class="text-white font-medium"><?= formatMoney($item['total_item']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="border-t border-white/10 mt-3 pt-3 flex justify-between text-base font-bold text-primary-400">
            <span>Total</span>
            <span id="totalFechamento"><?= formatMoney($comanda['subtotal']) ?></span>
        </div>
    </div>

    <form method="POST" action="/comandas/<?= $comanda['id'] ?>/fechar" class="space-y-5">
        <?= csrf_field() ?>
        
        <div class="glass-card rounded-2xl p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Desconto (R$)</label>
                    <input type="number" name="desconto" id="desconto" min="0" step="0.01" value="0"
                           oninput="recalcTotal()"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Total Final</label>
                    <div class="px-4 py-3 bg-primary-900/20 border border-primary-500/30 rounded-xl text-primary-400 text-lg font-bold" id="totalFinalDisplay">
                        <?= formatMoney($comanda['subtotal']) ?>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-3">Forma de Pagamento</label>
                <div class="space-y-2">
                    <?php foreach ([
                        ['dinheiro', '💵 Dinheiro'],
                        ['maquininha', '💳 Maquininha'],
                        ['stripe_qr', '📲 Pix QR Code (Stripe)'],
                    ] as [$val, $label]): ?>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-white/10 hover:border-primary-500/50 cursor-pointer transition-colors">
                        <input type="radio" name="forma_pagamento" value="<?= $val ?>" 
                               onclick="showSubForm('<?= $val ?>')"
                               class="accent-primary-500 w-4 h-4">
                        <span class="text-sm text-white"><?= $label ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="subFormDinheiro" class="hidden space-y-3">
                <label class="block text-xs font-medium text-gray-400 mb-2">Valor Recebido (R$)</label>
                <input type="number" name="valor_recebido" id="valorRecebido" min="0" step="0.01"
                       oninput="calcTroco()"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-emerald-500 text-sm">
                <div class="flex justify-between items-center bg-emerald-900/20 rounded-xl p-3">
                    <span class="text-sm text-gray-400">Troco</span>
                    <span id="trocoDisplay" class="text-xl font-bold text-emerald-400">R$ 0,00</span>
                </div>
                <input type="hidden" name="troco" id="trocoHidden" value="0">
            </div>

            <div id="subFormMaquininha" class="hidden space-y-2">
                <label class="block text-xs font-medium text-gray-400 mb-2">Modalidade</label>
                <?php foreach ([['debito','Débito'],['credito','Crédito'],['pix_maquininha','Pix Maquininha']] as [$v,$l]): ?>
                <label class="flex items-center gap-3 p-3 rounded-xl border border-white/10 hover:border-blue-500/50 cursor-pointer transition-colors">
                    <input type="radio" name="subforma_pagamento" value="<?= $v ?>" class="accent-blue-500 w-4 h-4">
                    <span class="text-sm text-white">💳 <?= $l ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button type="submit" class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-primary-900/30 flex items-center justify-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            Confirmar Pagamento
        </button>
    </form>
</div>

<script>
const subtotal = <?= $comanda['subtotal'] ?>;

function recalcTotal() {
    const desc = parseFloat(document.getElementById('desconto').value) || 0;
    const total = Math.max(0, subtotal - desc);
    document.getElementById('totalFinalDisplay').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('totalFechamento').textContent   = 'R$ ' + total.toFixed(2).replace('.', ',');
    calcTroco();
}

function showSubForm(method) {
    document.getElementById('subFormDinheiro').classList.add('hidden');
    document.getElementById('subFormMaquininha').classList.add('hidden');
    if (method === 'dinheiro') document.getElementById('subFormDinheiro').classList.remove('hidden');
    if (method === 'maquininha') document.getElementById('subFormMaquininha').classList.remove('hidden');
}

function calcTroco() {
    const desc     = parseFloat(document.getElementById('desconto').value) || 0;
    const total    = Math.max(0, subtotal - desc);
    const recebido = parseFloat(document.getElementById('valorRecebido').value) || 0;
    const troco    = Math.max(0, recebido - total);
    document.getElementById('trocoDisplay').textContent  = 'R$ ' + troco.toFixed(2).replace('.', ',');
    document.getElementById('trocoHidden').value         = troco.toFixed(2);
}
</script>
