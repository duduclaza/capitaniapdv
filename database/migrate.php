<?php
/**
 * Database Migration - Runs the schema.sql
 * Run: php database/migrate.php
 */

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

echo "🚀 Running migrations...\n\n";

try {
    $db = Database::getInstance();
    $sql = file_get_contents(__DIR__ . '/schema.sql');

    // Split by semicolons and run each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s) && !str_starts_with($s, '--')
    );

    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $db->exec($statement);
        }
    }

    echo "✅ Migrations completed successfully!\n";
} catch (\Throwable $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
