<?php
/**
 * Database Seeder - Creates initial data
 * Run: php database/seed.php
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

$db = Database::getInstance();

echo "🌱 Seeding database...\n\n";

// --- Admin User ---
$senhaHash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute(['admin@capitania.pdv']);
if (!$stmt->fetch()) {
    $db->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) VALUES (?, ?, ?, 'admin', 1)")
       ->execute(['Administrador', 'admin@capitania.pdv', $senhaHash]);
    echo "✅ Admin user created: admin@capitania.pdv / admin123\n";
} else {
    echo "⏭  Admin user already exists\n";
}

// --- Categorias ---
$categorias = ['Cervejas', 'Drinks', 'Petiscos', 'Sem Álcool', 'Petisco Quente', 'Destilados'];
$catIds = [];
foreach ($categorias as $cat) {
    $stmt = $db->prepare("SELECT id FROM categorias WHERE nome = ?");
    $stmt->execute([$cat]);
    $row = $stmt->fetch();
    if (!$row) {
        $db->prepare("INSERT INTO categorias (nome, ativo) VALUES (?, 1)")->execute([$cat]);
        $catIds[$cat] = $db->lastInsertId();
        echo "✅ Categoria: {$cat}\n";
    } else {
        $catIds[$cat] = $row['id'];
        echo "⏭  Categoria já existe: {$cat}\n";
    }
}

// --- Produtos de exemplo ---
$produtos = [
    ['Cerveja Heineken 600ml',  $catIds['Cervejas'],     12.00, 22.00, 'un', 1],
    ['Cerveja Brahma Lata',     $catIds['Cervejas'],     4.00,  8.00,  'un', 1],
    ['Água Mineral 500ml',      $catIds['Sem Álcool'],   1.50,  4.00,  'un', 1],
    ['Refrigerante Lata',       $catIds['Sem Álcool'],   3.00,  6.00,  'un', 1],
    ['Caipirinha',              $catIds['Drinks'],       8.00,  18.00, 'un', 0],
    ['Whisky Jack Daniel\'s',   $catIds['Destilados'],   80.00, 150.00,'un', 1],
    ['Batata Frita',            $catIds['Petiscos'],     5.00,  22.00, 'porcao', 1],
    ['Porção de Calabresa',     $catIds['Petisco Quente'],7.00, 28.00, 'porcao', 1],
];

foreach ($produtos as [$nome, $catId, $custo, $venda, $unidade, $controla]) {
    $stmt = $db->prepare("SELECT id FROM produtos WHERE nome = ?");
    $stmt->execute([$nome]);
    if (!$stmt->fetch()) {
        $sku = strtoupper(substr(preg_replace('/[^a-z]/i', '', $nome), 0, 6)) . rand(100, 999);
        $lucro = round((($venda - $custo) / $venda) * 100, 2);
        $db->prepare("INSERT INTO produtos (categoria_id, nome, sku, unidade, preco_custo, preco_venda, percent_lucro, estoque_atual, estoque_minimo, controla_estoque, ativo) VALUES (?,?,?,?,?,?,?,?,?,?,1)")
           ->execute([$catId, $nome, $sku, $unidade, $custo, $venda, $lucro, 50, 10, $controla]);
        echo "✅ Produto: {$nome}\n";
    } else {
        echo "⏭  Produto já existe: {$nome}\n";
    }
}

// --- Mesas ---
for ($i = 1; $i <= 10; $i++) {
    $stmt = $db->prepare("SELECT id FROM mesas WHERE numero = ?");
    $stmt->execute([$i]);
    if (!$stmt->fetch()) {
        $db->prepare("INSERT INTO mesas (numero, descricao, status) VALUES (?, ?, 'livre')")
           ->execute([$i, "Mesa {$i}"]);
        echo "✅ Mesa: {$i}\n";
    }
}

echo "\n✅ Seed concluído!\n";
echo "🔑 Acesso admin: admin@capitania.pdv / admin123\n";
