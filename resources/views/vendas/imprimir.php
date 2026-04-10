<?php
/**
 * Cupom Não Fiscal - Capitania PDV
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom Venda #<?= $venda['id'] ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { 
            width: 80mm; 
            margin: 0; 
            padding: 5mm; 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 12px; 
            line-height: 1.2;
            color: #000;
        }
        .text-center { text-center: center !important; text-align: center; }
        .text-right { text-align: right; }
        .dashed-line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 10px; }
        .footer { margin-top: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; border-bottom: 1px solid #000; padding-bottom: 2px; }
        td { padding: 2px 0; vertical-align: top; }
        .total-row { font-size: 14px; margin-top: 5px; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center header">
        <div class="bold" style="font-size: 16px;">CAPITANIA PDV</div>
        <div>Bar & Restaurante</div>
        <div>Rua do Porto, 123 - Centro</div>
        <div>(11) 99999-9999</div>
    </div>

    <div class="dashed-line"></div>

    <div>
        <strong>DATA:</strong> <?= date('d/m/Y H:i', strtotime($venda['created_at'])) ?><br>
        <strong>VENDA:</strong> #<?= str_pad($venda['id'], 6, '0', STR_PAD_LEFT) ?><br>
        <strong>COMPROVANTE NÃO FISCAL</strong>
    </div>

    <div class="dashed-line"></div>

    <table>
        <thead>
            <tr>
                <th>DESC</th>
                <th class="text-right">QTD</th>
                <th class="text-right">VL.UN</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
            <tr>
                <td><?= substr(e($item['nome']), 0, 15) ?></td>
                <td class="text-right"><?= number_format($item['quantidade'], 0) ?></td>
                <td class="text-right"><?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                <td class="text-right"><?= number_format($item['total_item'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <div class="text-right">
        <div>Subtotal: R$ <?= number_format($venda['valor_bruto'], 2, ',', '.') ?></div>
        <?php if ($venda['desconto'] > 0): ?>
            <div>Desconto: R$ <?= number_format($venda['desconto'], 2, ',', '.') ?></div>
        <?php endif; ?>
        <div class="bold total-row">TOTAL: R$ <?= number_format($venda['valor_final'], 2, ',', '.') ?></div>
    </div>

    <div class="dashed-line"></div>

    <div>
        <strong>Pagamento:</strong> <?= ucfirst($venda['forma_pagamento']) ?><br>
        <?php if ($venda['subforma_pagamento']): ?>
            <strong>Tipo:</strong> <?= ucfirst($venda['subforma_pagamento']) ?><br>
        <?php endif; ?>
        <strong>Status:</strong> <?= strtoupper($venda['status']) ?>
    </div>

    <div class="dashed-line"></div>

    <div class="text-center footer">
        Obrigado pela preferência!<br>
        Volte sempre ao Capitania.<br>
        <br>
        www.capitaniapdv.com.br
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Fechar Janela</button>
    </div>

</body>
</html>
