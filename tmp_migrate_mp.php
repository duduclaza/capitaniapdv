<?php
require 'bootstrap/app.php';
$db = App\Core\Database::getInstance();
try {
    // Check if columns exist before adding
    $cols = $db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('mp_access_token', $cols)) {
        $db->exec('ALTER TABLE usuarios ADD COLUMN mp_access_token TEXT DEFAULT NULL AFTER ativo');
    }
    if (!in_array('mp_refresh_token', $cols)) {
        $db->exec('ALTER TABLE usuarios ADD COLUMN mp_refresh_token TEXT DEFAULT NULL AFTER mp_access_token');
    }
    if (!in_array('mp_user_id', $cols)) {
        $db->exec('ALTER TABLE usuarios ADD COLUMN mp_user_id VARCHAR(50) DEFAULT NULL AFTER mp_refresh_token');
    }
    if (!in_array('mp_expires_at', $cols)) {
        $db->exec('ALTER TABLE usuarios ADD COLUMN mp_expires_at DATETIME DEFAULT NULL AFTER mp_user_id');
    }

    $db->exec("ALTER TABLE vendas MODIFY COLUMN forma_pagamento ENUM('dinheiro', 'maquininha', 'stripe_qr', 'mercadopago_qr') NOT NULL");
    $db->exec("ALTER TABLE vendas MODIFY COLUMN subforma_pagamento ENUM('debito', 'credito', 'pix_maquininha', 'pix_stripe', 'pix_mercadopago') DEFAULT NULL");

    $colsVendas = $db->query('SHOW COLUMNS FROM vendas')->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('mp_payment_id', $colsVendas)) {
        $db->exec('ALTER TABLE vendas ADD COLUMN mp_payment_id VARCHAR(150) DEFAULT NULL AFTER stripe_payment_status');
    }
    if (!in_array('mp_payment_status', $colsVendas)) {
        $db->exec('ALTER TABLE vendas ADD COLUMN mp_payment_status VARCHAR(50) DEFAULT NULL AFTER mp_payment_id');
    }
    
    echo '✅ Database updated successfully!';
} catch (Exception $e) {
    echo '❌ Database update failed: ' . $e->getMessage();
}
