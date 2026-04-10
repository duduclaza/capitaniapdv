<!-- PDV Header -->
<div class="flex items-center justify-between px-5 py-3 bg-dark-900 border-b border-purple-900/30 flex-shrink-0">
    <div class="flex items-center gap-4">
        <a href="/dashboard" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-800 flex items-center justify-center">
                <i data-lucide="anchor" class="w-4 h-4 text-white"></i>
            </div>
            <span class="text-base font-bold text-white">PDV Capitania</span>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <span class="text-xs text-gray-500" id="pdv-time"></span>
        <span class="text-xs bg-primary-900/40 text-primary-300 px-3 py-1 rounded-full">
            <?= e(auth()['nome']) ?>
        </span>
    </div>
</div>

<!-- PDV Body -->
<div class="flex flex-1 overflow-hidden">

    <!-- LEFT: Product Search + Results -->
    <div class="flex-1 flex flex-col bg-dark-900/50 overflow-hidden">
        
        <!-- Search Bar -->
        <div class="p-4 border-b border-white/5">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-5 h-5 text-gray-500"></i>
                </div>
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Buscar produto por nome, SKU ou código de barras..."
                    autofocus
                    autocomplete="off"
                    class="w-full pl-12 pr-4 py-4 bg-dark-800 border border-white/10 rounded-2xl text-white text-base placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all"
                >
            </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                <div class="col-span-full text-center py-12 text-gray-500">
                    <i data-lucide="search" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                    <p>Digite para buscar produtos</p>
                </div>
            </div>
        </div>

        <!-- Client selector -->
        <div class="p-3 border-t border-white/5 bg-dark-900/80">
            <div class="flex items-center gap-3">
                <label class="text-xs text-gray-500 flex-shrink-0">Cliente:</label>
                <select id="clienteSelect" class="flex-1 px-3 py-2 bg-dark-800 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500 transition-colors">
                    <option value="">-- Sem vínculo --</option>
                    <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- RIGHT: Cart -->
    <div class="w-80 xl:w-96 flex flex-col bg-dark-800 border-l border-white/5 flex-shrink-0">
        
        <!-- Cart Header -->
        <div class="p-4 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-primary-400"></i>
                <span class="font-semibold text-white">Carrinho</span>
                <span id="cartCount" class="text-xs bg-primary-600 text-white px-1.5 py-0.5 rounded-full font-bold">0</span>
            </div>
            <button onclick="clearCart()" class="text-xs text-gray-500 hover:text-red-400 transition-colors">
                Limpar
            </button>
        </div>

        <!-- Cart Items -->
        <div id="cartItems" class="flex-1 overflow-y-auto divide-y divide-white/5">
            <div id="cartEmpty" class="flex flex-col items-center justify-center h-full text-gray-600 py-8">
                <i data-lucide="shopping-cart" class="w-10 h-10 mb-3 opacity-30"></i>
                <p class="text-sm">Carrinho vazio</p>
            </div>
        </div>

        <!-- Discount -->
        <div class="p-3 border-t border-white/10">
            <div class="flex items-center gap-3">
                <label class="text-xs text-gray-500 flex-shrink-0">Desconto R$:</label>
                <input type="number" id="descontoInput" min="0" step="0.01" value="0"
                       class="flex-1 px-3 py-2 bg-dark-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-primary-500 transition-colors"
                       oninput="updateTotals()">
            </div>
        </div>

        <!-- Totals -->
        <div class="p-4 bg-dark-900/50 border-t border-white/10 space-y-2">
            <div class="flex justify-between text-sm text-gray-400">
                <span>Subtotal</span>
                <span id="subtotalDisplay">R$ 0,00</span>
            </div>
            <div class="flex justify-between text-sm text-gray-400">
                <span>Desconto</span>
                <span id="descontoDisplay" class="text-red-400">- R$ 0,00</span>
            </div>
            <div class="flex justify-between text-xl font-bold text-white border-t border-white/10 pt-2">
                <span>Total</span>
                <span id="totalDisplay" class="text-primary-400">R$ 0,00</span>
            </div>
        </div>

        <!-- Finish Button -->
        <div class="p-4">
            <button onclick="openPaymentModal()"
                    id="btnFinalizar"
                    disabled
                    class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-4 rounded-2xl transition-all text-base shadow-lg shadow-primary-900/30 flex items-center justify-center gap-2">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                Finalizar Venda
            </button>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-dark-800 border border-white/10 rounded-3xl w-full max-w-lg shadow-2xl">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-white/10">
            <h3 class="text-lg font-bold text-white">Forma de Pagamento</h3>
            <button onclick="closePaymentModal()" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Total -->
        <div class="p-6 text-center border-b border-white/10">
            <p class="text-sm text-gray-400 mb-1">Total a pagar</p>
            <p class="text-4xl font-bold text-primary-400" id="modalTotal">R$ 0,00</p>
        </div>

        <!-- Payment Options -->
        <div class="p-6 space-y-3">
            
            <!-- Cash -->
            <button onclick="selectPayment('dinheiro')" data-method="dinheiro"
                    class="payment-btn w-full flex items-center gap-4 p-4 rounded-2xl border border-white/10 hover:border-emerald-500/50 hover:bg-emerald-900/10 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">💵</span>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-white">Dinheiro</p>
                    <p class="text-xs text-gray-500">Pagamento em espécie + troco</p>
                </div>
            </button>

            <!-- Card Machine -->
            <button onclick="selectPayment('maquininha')" data-method="maquininha"
                    class="payment-btn w-full flex items-center gap-4 p-4 rounded-2xl border border-white/10 hover:border-blue-500/50 hover:bg-blue-900/10 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">💳</span>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-white">Maquininha</p>
                    <p class="text-xs text-gray-500">Débito, crédito ou Pix na maquininha</p>
                </div>
            </button>

            <!-- Stripe Pix QR -->
            <button onclick="selectPayment('stripe_qr')" data-method="stripe_qr"
                    class="payment-btn w-full flex items-center gap-4 p-4 rounded-2xl border border-white/10 hover:border-fuchsia-500/50 hover:bg-fuchsia-900/10 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-fuchsia-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">📲</span>
                </div>
                <div class="text-left">
                    <p class="font-semibold text-white">Pix QR Code</p>
                    <p class="text-xs text-gray-500">QR gerado via Stripe</p>
                </div>
            </button>
        </div>

        <!-- Sub-options area -->
        <div id="subOptions" class="px-6 pb-6 hidden"></div>

        <!-- Confirm button -->
        <div class="p-6 border-t border-white/10">
            <button onclick="confirmarVenda()" id="btnConfirmar"
                    class="w-full hidden bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-500 hover:to-primary-600 text-white font-bold py-4 rounded-2xl transition-all">
                Confirmar Pagamento
            </button>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center">
    <div class="text-center">
        <div class="w-16 h-16 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-white font-medium">Processando venda...</p>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
let cart = [];
let selectedPayment = null;
let selectedSubform = null;

// ------- Time display -------
function updateTime() {
    document.getElementById('pdv-time').textContent = new Date().toLocaleTimeString('pt-BR');
}
setInterval(updateTime, 1000);
updateTime();

// ------- Search -------
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('productGrid').innerHTML = `
            <div class="col-span-full text-center py-12 text-gray-500">
                <i data-lucide="search" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                <p>Digite para buscar produtos</p>
            </div>`;
        lucide.createIcons();
        return;
    }
    searchTimer = setTimeout(() => searchProducts(q), 300);
});

async function searchProducts(q) {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500"><div class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto"></div></div>';
    
    const res = await fetch('/pdv/buscar?q=' + encodeURIComponent(q));
    const products = await res.json();
    
    if (products.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center py-12 text-gray-500"><p>Nenhum produto encontrado</p></div>';
        return;
    }
    
    grid.innerHTML = products.map(p => `
        <button onclick='addToCart(${JSON.stringify(p)})'
                class="bg-dark-800 hover:bg-dark-700 border border-white/5 hover:border-primary-500/50 rounded-2xl p-4 text-left transition-all group">
            <div class="w-10 h-10 rounded-lg bg-primary-900/30 flex items-center justify-center mb-3 group-hover:bg-primary-900/50 transition-colors">
                <i data-lucide="package" class="w-5 h-5 text-primary-400"></i>
            </div>
            <p class="text-sm font-semibold text-white leading-tight">${escapeHtml(p.nome)}</p>
            <p class="text-xs text-gray-500 mt-0.5">${escapeHtml(p.unidade)}</p>
            <p class="text-sm font-bold text-primary-400 mt-2">R$ ${parseFloat(p.preco_venda).toFixed(2).replace('.', ',')}</p>
            ${p.controla_estoque ? `<p class="text-xs ${p.estoque_atual <= 5 ? 'text-red-400' : 'text-gray-600'} mt-0.5">Estq: ${p.estoque_atual}</p>` : ''}
        </button>
    `).join('');
    lucide.createIcons();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ------- Cart -------
function addToCart(product) {
    const existing = cart.find(i => i.produto_id === product.id);
    if (existing) {
        existing.quantidade++;
    } else {
        cart.push({
            produto_id: product.id,
            nome: product.nome,
            preco_unitario: parseFloat(product.preco_venda),
            quantidade: 1,
            unidade: product.unidade,
            observacao: '',
        });
    }
    renderCart();
    toast(product.nome + ' adicionado ao carrinho!');
}


function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function updateQty(idx, qty) {
    qty = parseInt(qty);
    if (qty <= 0) { removeFromCart(idx); return; }
    cart[idx].quantidade = qty;
    renderCart();
}

function clearCart() {
    if (cart.length === 0) return;
    if (!confirm('Limpar o carrinho?')) return;
    cart = [];
    renderCart();
    toast('Carrinho limpo!', 'info');
}


function renderCart() {
    const container = document.getElementById('cartItems');
    const empty     = document.getElementById('cartEmpty');
    
    if (cart.length === 0) {
        container.innerHTML = '';
        container.appendChild(empty);
        empty.style.display = 'flex';
        document.getElementById('btnFinalizar').disabled = true;
        document.getElementById('cartCount').textContent = '0';
        updateTotals();
        return;
    }
    
    empty.style.display = 'none';
    container.innerHTML = cart.map((item, idx) => `
        <div class="p-4 hover:bg-white/3 transition-colors">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white leading-tight">${escapeHtml(item.nome)}</p>
                    <p class="text-xs text-primary-400 mt-0.5">R$ ${item.preco_unitario.toFixed(2).replace('.', ',')}</p>
                </div>
                <button onclick="removeFromCart(${idx})" class="p-1 rounded hover:bg-red-900/30 text-gray-500 hover:text-red-400 transition-colors flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="flex items-center justify-between mt-2">
                <div class="flex items-center gap-2">
                    <button onclick="updateQty(${idx}, ${item.quantidade - 1})" 
                            class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-white flex items-center justify-center text-sm transition-colors">−</button>
                    <input type="number" value="${item.quantidade}" min="1"
                           onchange="updateQty(${idx}, this.value)"
                           class="w-12 text-center bg-white/5 border border-white/10 rounded-lg py-1 text-sm text-white focus:outline-none focus:border-primary-500">
                    <button onclick="updateQty(${idx}, ${item.quantidade + 1})"
                            class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-white flex items-center justify-center text-sm transition-colors">+</button>
                </div>
                <span class="text-sm font-bold text-white">R$ ${(item.quantidade * item.preco_unitario).toFixed(2).replace('.', ',')}</span>
            </div>
        </div>
    `).join('');
    
    document.getElementById('cartCount').textContent = cart.reduce((s, i) => s + i.quantidade, 0);
    document.getElementById('btnFinalizar').disabled = false;
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + i.quantidade * i.preco_unitario, 0);
    const desconto = parseFloat(document.getElementById('descontoInput').value) || 0;
    const total    = Math.max(0, subtotal - desconto);
    
    document.getElementById('subtotalDisplay').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
    document.getElementById('descontoDisplay').textContent = '- R$ ' + desconto.toFixed(2).replace('.', ',');
    document.getElementById('totalDisplay').textContent    = 'R$ ' + total.toFixed(2).replace('.', ',');
    document.getElementById('modalTotal').textContent      = 'R$ ' + total.toFixed(2).replace('.', ',');
}

// ------- Payment Modal -------
function openPaymentModal() {
    if (cart.length === 0) return;
    updateTotals();
    selectedPayment = null;
    selectedSubform = null;
    document.getElementById('subOptions').classList.add('hidden');
    document.getElementById('btnConfirmar').classList.add('hidden');
    document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('border-primary-500', 'bg-primary-900/20'));
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function selectPayment(method) {
    selectedPayment = method;
    selectedSubform = null;
    
    document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('border-primary-500', 'bg-primary-900/20'));
    document.querySelector(`[data-method="${method}"]`).classList.add('border-primary-500', 'bg-primary-900/20');
    
    const subOpts = document.getElementById('subOptions');
    const btnConf = document.getElementById('btnConfirmar');
    
    if (method === 'dinheiro') {
        const total = parseFloat(document.getElementById('totalDisplay').textContent.replace('R$ ', '').replace(',', '.'));
        subOpts.innerHTML = `
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-400 mb-2 block">Valor Recebido (R$)</label>
                    <input type="number" id="valorRecebido" min="${total}" step="0.01" value="${total}"
                           oninput="calcTroco()"
                           class="w-full px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-white text-lg font-semibold focus:outline-none focus:border-emerald-500">
                </div>
                <div class="flex justify-between items-center bg-emerald-900/20 rounded-xl p-3">
                    <span class="text-sm text-gray-400">Troco</span>
                    <span id="trocoDisplay" class="text-xl font-bold text-emerald-400">R$ 0,00</span>
                </div>
            </div>`;
        subOpts.classList.remove('hidden');
        btnConf.classList.remove('hidden');
        btnConf.textContent = 'Confirmar - Dinheiro';
    } else if (method === 'maquininha') {
        subOpts.innerHTML = `
            <div class="space-y-2">
                <p class="text-xs text-gray-400 mb-3">Selecione a modalidade:</p>
                ${[['debito','💳 Débito'],['credito','💳 Crédito'],['pix_maquininha','📱 Pix Maquininha']].map(([v,l]) => `
                    <button onclick="selectSubform('${v}')" data-sub="${v}"
                            class="sub-btn w-full text-left px-4 py-3 rounded-xl border border-white/10 hover:border-primary-500/50 text-sm text-gray-300 hover:text-white transition-all">
                        ${l}
                    </button>`).join('')}
            </div>`;
        subOpts.classList.remove('hidden');
        btnConf.classList.add('hidden');
    } else if (method === 'stripe_qr') {
        subOpts.innerHTML = `
            <div class="bg-fuchsia-900/20 border border-fuchsia-500/20 rounded-xl p-4 text-center">
                <p class="text-sm text-gray-300">Um QR Code Pix será gerado via Stripe para pagamento instantâneo.</p>
            </div>`;
        subOpts.classList.remove('hidden');
        btnConf.classList.remove('hidden');
        btnConf.textContent = '📲 Gerar QR Code Pix';
    }
    
    lucide.createIcons();
}

function selectSubform(val) {
    selectedSubform = val;
    document.querySelectorAll('.sub-btn').forEach(b => b.classList.remove('border-primary-500', 'bg-primary-900/20', 'text-white'));
    document.querySelector(`[data-sub="${val}"]`).classList.add('border-primary-500', 'bg-primary-900/20', 'text-white');
    const btn = document.getElementById('btnConfirmar');
    btn.classList.remove('hidden');
    btn.textContent = 'Confirmar - Maquininha';
}

function calcTroco() {
    const total    = parseFloat(document.getElementById('totalDisplay').textContent.replace('R$ ', '').replace(',', '.'));
    const recebido = parseFloat(document.getElementById('valorRecebido').value) || 0;
    const troco    = Math.max(0, recebido - total);
    document.getElementById('trocoDisplay').textContent = 'R$ ' + troco.toFixed(2).replace('.', ',');
    
    const btn = document.getElementById('btnConfirmar');
    btn.disabled = recebido < total;
}

async function confirmarVenda() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('loadingModal').classList.remove('hidden');
    
    const total    = parseFloat(document.getElementById('totalDisplay').textContent.replace('R$ ', '').replace(',', '.'));
    const desconto = parseFloat(document.getElementById('descontoInput').value) || 0;
    const cliente  = document.getElementById('clienteSelect').value;
    
    let valorRecebido = null;
    let troco = null;
    if (selectedPayment === 'dinheiro') {
        valorRecebido = parseFloat(document.getElementById('valorRecebido')?.value) || total;
        troco = Math.max(0, valorRecebido - total);
    }
    
    const payload = new FormData();
    payload.append('_csrf', CSRF_TOKEN);
    payload.append('itens', JSON.stringify(cart));
    payload.append('forma_pagamento', selectedPayment);
    payload.append('subforma_pagamento', selectedSubform || '');
    payload.append('desconto', desconto);
    payload.append('valor_recebido', valorRecebido || '');
    payload.append('troco', troco || '');
    payload.append('cliente_id', cliente || '');
    
    try {
        const res  = await fetch('/pdv/finalizar', { method: 'POST', body: payload });
        const data = await res.json();
        
        document.getElementById('loadingModal').classList.add('hidden');
        
        if (data.success) {
            if (data.awaiting_payment) {
                // Stripe QR - redirect to payment page
                window.location.href = '/vendas/' + data.venda_id + '/aguardando-pagamento';
                return;
            }
            
            // Success - show receipt prompt
            toast(`Venda #${data.venda_id} realizada com sucesso!`);
            
            if (confirm(`Venda #${data.venda_id} finalizada!\nTotal: R$ ${parseFloat(data.valor_final).toFixed(2).replace('.', ',')}\n\nDeseja imprimir o cupom?`)) {
                window.open('/vendas/' + data.venda_id + '/imprimir', '_blank');
            }
            
            cart = [];
            renderCart();
            document.getElementById('descontoInput').value = 0;
            document.getElementById('searchInput').value = '';
            document.getElementById('searchInput').focus();
            document.getElementById('productGrid').innerHTML = `
                <div class="col-span-full text-center py-12 text-gray-500">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 text-emerald-500 opacity-60"></i>
                    <p>Venda realizada! Busque o próximo produto.</p>
                </div>`;
            lucide.createIcons();
        } else {
            alert('Erro: ' + data.message);
        }
    } catch (e) {
        document.getElementById('loadingModal').classList.add('hidden');
        alert('Erro ao processar venda. Tente novamente.');
    }
}
</script>
