<?php
require_once 'config/database.php';

echo "Running main database setup...\n";

try {
    $db = (new Database())->getConnection();
    $sql = file_get_contents('database/setup.sql');
    
    // Split into statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach($statements as $stmt) {
        if(!empty($stmt) && !preg_match('/^\s*--/', $stmt)) {
            try {
                $db->exec($stmt);
                echo "✓ Executed: " . substr(str_replace(["\n", "\r"], ' ', $stmt), 0, 60) . "...\n";
            } catch(Exception $e) {
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ Main database setup completed!\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
