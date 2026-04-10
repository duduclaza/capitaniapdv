<?php 
$isEdit = $produto !== null;
$pageTitle = $isEdit ? 'Editar Produto' : 'Novo Produto';
?>

<div class="max-w-3xl mx-auto fade-in-up">
    
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="/produtos" class="p-2 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-lg font-bold text-white"><?= $pageTitle ?></h2>
            <p class="text-sm text-gray-400">Preencha os dados do produto</p>
        </div>
    </div>

    <form method="POST" action="<?= $isEdit ? '/produtos/' . $produto['id'] : '/produtos' ?>" 
          enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Info Básica -->
        <div class="glass-card rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider border-b border-white/10 pb-3">
                Informações Básicas
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-2">Nome do Produto *</label>
                    <input type="text" name="nome" required
                           value="<?= e($produto['nome'] ?? '') ?>"
                           placeholder="Ex: Cerveja Heineken 600ml"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-sm transition-colors">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Categoria</label>
                    <select name="categoria_id" class="w-full px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-white focus:outline-none focus:border-primary-500 text-sm transition-colors">
                        <option value="">-- Sem categoria --</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($produto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Unidade</label>
                    <select name="unidade" class="w-full px-4 py-3 bg-dark-900 border border-white/10 rounded-xl text-white focus:outline-none focus:border-primary-500 text-sm transition-colors">
                        <?php foreach (['un' => 'Unidade', 'kg' => 'Kg', 'g' => 'Gramas', 'l' => 'Litro', 'ml' => 'mL', 'porcao' => 'Porção', 'cx' => 'Caixa'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($produto['unidade'] ?? 'un') == $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">SKU</label>
                    <input type="text" name="sku"
                           value="<?= e($produto['sku'] ?? '') ?>"
                           placeholder="Código interno"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Código de Barras</label>
                    <input type="text" name="codigo_barras"
                           value="<?= e($produto['codigo_barras'] ?? '') ?>"
                           placeholder="EAN-13"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
                </div>
            </div>
        </div>

        <!-- Preços e Lucro -->
        <div class="glass-card rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider border-b border-white/10 pb-3">
                Precificação
            </h3>
            
            <div class="bg-primary-900/20 border border-primary-500/20 rounded-xl p-4 text-sm text-gray-300">
                <p class="flex items-center gap-2 mb-1">
                    <i data-lucide="info" class="w-4 h-4 text-primary-400"></i>
                    <strong class="text-white">Cálculo automático de preço</strong>
                </p>
                <p class="text-gray-400 text-xs">Informe o custo e a margem de lucro desejada. O preço de venda será calculado automaticamente. Ou informe o preço de venda diretamente.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Preço de Custo (R$)</label>
                    <input type="text" id="preco_custo" name="preco_custo"
                           value="<?= number_format($produto['preco_custo'] ?? 0, 2, '.', '') ?>"
                           placeholder="0.00"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors"
                           oninput="calcularVenda()">
                </div>
                
                <div class="space-y-3">
                    <label class="block text-xs font-medium text-gray-400">Margem de Lucro (%)</label>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="adjustMargem(-0.1)" 
                                class="p-3 bg-white/5 border border-white/10 rounded-xl text-gray-400 hover:text-white hover:bg-white/10 active:scale-95 transition-all">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        
                        <div class="relative flex-1">
                            <input type="number" id="percent_lucro" name="percent_lucro" step="0.1"
                                   value="<?= number_format($produto['percent_lucro'] ?? 0, 1, '.', '') ?>"
                                   placeholder="0.0"
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                   oninput="syncToRange(this.value); calcularVenda()"
                                   onwheel="handleMouseWheel(event)">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">%</span>
                        </div>

                        <button type="button" onclick="adjustMargem(0.1)" 
                                class="p-3 bg-white/5 border border-white/10 rounded-xl text-gray-400 hover:text-white hover:bg-white/10 active:scale-95 transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Mini Scroll / Slider -->
                    <div class="px-1">
                        <input type="range" id="percent_lucro_range" min="0" max="100" step="0.1" 
                               value="<?= number_format($produto['percent_lucro'] ?? 0, 1, '.', '') ?>"
                               class="w-full h-1.5 bg-white/10 rounded-lg appearance-none cursor-pointer accent-primary-500 hover:accent-primary-400 transition-all"
                               oninput="syncFromRange(this.value)">
                        <div class="flex justify-between mt-1 text-[10px] text-gray-600 uppercase tracking-tighter">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-500">Lucro ajustável sobre o preço de venda</p>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Preço de Venda (R$) *</label>
                    <input type="text" id="preco_venda" name="preco_venda"
                           value="<?= number_format($produto['preco_venda'] ?? 0, 2, '.', '') ?>"
                           placeholder="0.00" required
                           class="w-full px-4 py-3 bg-emerald-900/20 border border-emerald-500/30 rounded-xl text-emerald-300 placeholder-gray-500 focus:outline-none focus:border-emerald-500 text-sm font-semibold transition-colors">
                </div>
            </div>

            <!-- Lucro preview -->
            <div id="lucro-preview" class="hidden bg-white/3 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-400">Lucro estimado por unidade: <span id="lucro-valor" class="font-semibold text-emerald-400"></span></p>
            </div>
        </div>

        <!-- Estoque -->
        <div class="glass-card rounded-2xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">Estoque</h3>
                <label class="flex items-center gap-2 cursor-pointer">
                    <span class="text-xs text-gray-400">Controlar estoque</span>
                    <input type="checkbox" name="controla_estoque" value="1"
                           <?= ($produto['controla_estoque'] ?? 1) ? 'checked' : '' ?>
                           class="w-4 h-4 accent-primary-500">
                </label>
            </div>
            
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Estoque Atual</label>
                    <input type="text" name="estoque_atual"
                           value="<?= number_format($produto['estoque_atual'] ?? 0, 0, '.', '') ?>"
                           placeholder="0"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">Estoque Mínimo</label>
                    <input type="text" name="estoque_minimo"
                           value="<?= number_format($produto['estoque_minimo'] ?? 0, 0, '.', '') ?>"
                           placeholder="0"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-primary-500 text-sm transition-colors">
                </div>
            </div>
        </div>

        <!-- Imagem -->
        <div class="glass-card rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider border-b border-white/10 pb-3">
                Imagem do Produto <span class="text-gray-500 text-xs normal-case font-normal">(armazenada no banco)</span>
            </h3>
            
            <div class="flex items-start gap-6">
                <!-- Preview -->
                <div class="flex-shrink-0">
                    <?php if ($isEdit && !empty($produto['imagem_blob'])): ?>
                        <img id="imgPreview" src="/produtos/<?= $produto['id'] ?>/imagem" 
                             class="w-24 h-24 rounded-xl object-cover border border-white/10">
                    <?php else: ?>
                        <div id="imgPreview" class="w-24 h-24 rounded-xl bg-white/5 border border-white/10 flex flex-col items-center justify-center">
                            <i data-lucide="image" class="w-8 h-8 text-gray-600"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1">
                    <label class="block">
                        <span class="sr-only">Escolher imagem</span>
                        <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp"
                               id="imgInput"
                               class="block w-full text-sm text-gray-400
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-medium
                                      file:bg-primary-600 file:text-white
                                      hover:file:bg-primary-500 file:cursor-pointer
                                      cursor-pointer transition-colors">
                    </label>
                    <p class="text-xs text-gray-500 mt-2">JPG, PNG ou WebP. Máximo 5MB.</p>
                </div>
            </div>
        </div>

        <!-- Status e Ações -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="ativo" value="1"
                       <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>
                       class="w-4 h-4 accent-primary-500">
                <div>
                    <p class="text-sm font-medium text-white">Produto ativo</p>
                    <p class="text-xs text-gray-500">Disponível para venda no PDV</p>
                </div>
            </label>
            
            <div class="flex items-center gap-3">
                <a href="/produtos" class="px-4 py-2.5 text-sm text-gray-400 hover:text-white border border-white/10 rounded-xl transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="flex items-center gap-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-primary-900/30">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <?= $isEdit ? 'Salvar Alterações' : 'Cadastrar Produto' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Image preview
document.getElementById('imgInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const preview = document.getElementById('imgPreview');
        if (preview.tagName === 'IMG') {
            preview.src = ev.target.result;
        } else {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.id = 'imgPreview';
            img.className = 'w-24 h-24 rounded-xl object-cover border border-white/10';
            preview.replaceWith(img);
        }
    };
    reader.readAsDataURL(file);
});

// Margem adjustment logic
function adjustMargem(delta) {
    const input = document.getElementById('percent_lucro');
    let val = parseFloat(input.value) || 0;
    val = Math.max(0, Math.min(100, (val + delta)));
    input.value = val.toFixed(1);
    syncToRange(input.value);
    calcularVenda();
}

function syncToRange(val) {
    const range = document.getElementById('percent_lucro_range');
    if (range) range.value = val;
}

function syncFromRange(val) {
    document.getElementById('percent_lucro').value = val;
    calcularVenda();
}

function handleMouseWheel(e) {
    if (document.activeElement === e.target) {
        e.preventDefault();
        const delta = e.deltaY < 0 ? 0.1 : -0.1;
        adjustMargem(delta);
    }
}

// Price calculator
function calcularVenda() {
    const custo = parseFloat(document.getElementById('preco_custo').value) || 0;
    const pct   = parseFloat(document.getElementById('percent_lucro').value) || 0;
    
    if (custo > 0 && pct > 0 && pct < 100) {
        const venda = custo / (1 - pct / 100);
        document.getElementById('preco_venda').value = venda.toFixed(2);
        
        const lucro = venda - custo;
        document.getElementById('lucro-preview').classList.remove('hidden');
        document.getElementById('lucro-valor').textContent = 
            'R$ ' + lucro.toFixed(2).replace('.', ',') + ' (' + pct.toFixed(1) + '% sobre venda)';
    } else if (pct >= 100) {
        document.getElementById('lucro-preview').classList.remove('hidden');
        document.getElementById('lucro-valor').textContent = 'Margem inválida (>= 100%)';
    }
}

// Also update % when venda is changed manually
document.getElementById('preco_venda').addEventListener('input', function() {
    const custo = parseFloat(document.getElementById('preco_custo').value) || 0;
    const venda = parseFloat(this.value) || 0;
    if (custo > 0 && venda > 0) {
        const pct = ((venda - custo) / venda) * 100;
        const pctFixed = pct.toFixed(1);
        document.getElementById('percent_lucro').value = pctFixed;
        syncToRange(pctFixed);
        
        const lucro = venda - custo;
        document.getElementById('lucro-preview').classList.remove('hidden');
        document.getElementById('lucro-valor').textContent = 
            'R$ ' + lucro.toFixed(2).replace('.', ',') + ' (' + pctFixed + '% sobre venda)';
    }
});
</script>
