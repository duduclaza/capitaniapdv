<?php $pageTitle = 'Aguardando Pagamento'; ?>
<div class="max-w-lg mx-auto fade-in-up">
    <div class="glass-card rounded-2xl p-8 text-center mt-4">
        <div class="w-20 h-20 rounded-full bg-blue-900/30 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="qr-code" class="w-10 h-10 text-blue-400"></i>
        </div>
        <h2 class="text-xl font-bold text-white mb-2">Aguardando Pagamento Pix</h2>
        <p class="text-gray-400 text-sm mb-6">Venda #<?= $venda['id'] ?> — <?= formatMoney($venda['valor_final']) ?></p>

        <?php if (!empty($venda['qr_code_image'])): ?>
        <div class="bg-white p-4 rounded-2xl inline-block mb-4 overflow-hidden">
            <?php 
                $src = $venda['qr_code_image'];
                if (strpos($src, 'http') === false && strpos($src, 'data:') === false) {
                    $src = 'data:image/png;base64,' . $src;
                }
            ?>
            <img src="<?= $src ?>" alt="QR Code Pix" class="w-48 h-48 mx-auto">
        </div>
        <?php endif; ?>

        <?php if (!empty($venda['qr_code_text'])): ?>
        <div class="bg-dark-900 rounded-xl p-3 mb-6">
            <p class="text-xs text-gray-400 mb-1">Código Pix copia e cola:</p>
            <p class="text-xs text-white font-mono break-all select-all"><?= e($venda['qr_code_text']) ?></p>
        </div>
        <?php endif; ?>

        <div id="paymentStatus" class="flex items-center justify-center gap-2 text-amber-400 mb-6">
            <div class="w-4 h-4 border-2 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm">Aguardando confirmação...</span>
        </div>

        <a href="/dashboard" class="text-sm text-gray-500 hover:text-gray-300">← Voltar ao Dashboard</a>
    </div>
</div>

<script>
const vendaId = <?= $venda['id'] ?>;
let checkInterval;

async function checkPayment() {
    const res = await fetch('/pdv/verificar/' + vendaId);
    const data = await res.json();
    
    if (data.status === 'paga') {
        clearInterval(checkInterval);
        document.getElementById('paymentStatus').innerHTML = `
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm text-emerald-400 font-semibold">Pagamento confirmado!</span>
        `;
        setTimeout(() => window.location.href = '/vendas/' + vendaId, 2000);
    }
}

checkInterval = setInterval(checkPayment, 3000);
checkPayment(); // immediate check
</script>
