<?php
require_once 'config/database.php';

echo "Checking and updating games table structure...\n";

try {
    $db = (new Database())->getConnection();
    
    // Check current structure
    echo "\nCurrent games table structure:\n";
    $result = $db->query('DESCRIBE games');
    $existing_columns = [];
    while($row = $result->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        $existing_columns[] = $row['Field'];
    }
    
    // Add missing columns
    $columns_to_add = [
        'system_requirements' => "VARCHAR(100) DEFAULT 'medium'",
        'game_setup_file' => "VARCHAR(500) NULL",
        'discount_percentage' => "DECIMAL(5,2) DEFAULT 0"
    ];
    
    echo "\nAdding missing columns:\n";
    
    foreach($columns_to_add as $column => $definition) {
        if(!in_array($column, $existing_columns)) {
            try {
                $db->exec("ALTER TABLE games ADD COLUMN $column $definition");
                echo "✓ Added column: $column\n";
            } catch(Exception $e) {
                echo "⚠ Error adding $column: " . $e->getMessage() . "\n";
            }
        } else {
            echo "ℹ Column $column already exists\n";
        }
    }
    
    echo "\n✓ Games table update completed!\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
