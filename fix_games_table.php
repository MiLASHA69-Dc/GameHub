<?php
// Fix Games Table - Add Missing Columns
include_once 'config/database.php';

echo "<h2>Fix Games Table - Add Missing Columns</h2>";

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<p style='color: green;'>✓ Connected to database</p>";

    // Check current games table structure
    echo "<h3>Current Games Table Structure:</h3>";
    $describe_result = $db->query("DESCRIBE games");
    $existing_columns = [];
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = $describe_result->fetch(PDO::FETCH_ASSOC)) {
        $existing_columns[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check and add missing columns
    $columns_to_add = [
        'discount_percentage' => 'INT DEFAULT 0',
        'status' => 'VARCHAR(20) DEFAULT \'active\'',
        'featured' => 'BOOLEAN DEFAULT FALSE',
        'genre' => 'VARCHAR(100)',
        'developer' => 'VARCHAR(100)',
        'publisher' => 'VARCHAR(100)',
        'system_requirements' => 'TEXT',
        'tags' => 'VARCHAR(500)',
        'metacritic_score' => 'INT',
        'age_rating' => 'VARCHAR(10)'
    ];
    
    echo "<h3>Adding Missing Columns:</h3>";
    
    foreach ($columns_to_add as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            try {
                $alter_sql = "ALTER TABLE games ADD COLUMN $column $definition";
                $db->exec($alter_sql);
                echo "<p style='color: green;'>✓ Added column: $column</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠ Could not add $column: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>→ Column $column already exists</p>";
        }
    }
    
    // Add some sample data if games table is empty
    $count_result = $db->query("SELECT COUNT(*) as count FROM games");
    $count = $count_result->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        echo "<h3>Adding Sample Games:</h3>";
        
        $sample_games = [
            [
                'title' => 'Lords of the Fallen',
                'description' => 'Action RPG with challenging combat and dark fantasy setting.',
                'price' => 29.99,
                'discount_price' => 7.79,
                'discount_percentage' => 74,
                'category' => 'Action RPG',
                'platform' => 'Windows',
                'genre' => 'Action, RPG',
                'developer' => 'Hexworks',
                'publisher' => 'CI Games',
                'status' => 'active',
                'featured' => true
            ],
            [
                'title' => 'The Elder Scrolls IV: Oblivion',
                'description' => 'Classic open-world RPG adventure.',
                'price' => 19.99,
                'discount_price' => 4.99,
                'discount_percentage' => 75,
                'category' => 'RPG',
                'platform' => 'Windows',
                'genre' => 'RPG, Open World',
                'developer' => 'Bethesda',
                'publisher' => 'Bethesda',
                'status' => 'active',
                'featured' => true
            ],
            [
                'title' => 'Mystery Bundle',
                'description' => 'Surprise collection of indie games.',
                'price' => 9.99,
                'discount_price' => null,
                'discount_percentage' => 0,
                'category' => 'Bundle',
                'platform' => 'Windows',
                'genre' => 'Various',
                'developer' => 'Various',
                'publisher' => 'GameHub',
                'status' => 'active',
                'featured' => false
            ]
        ];
        
        foreach ($sample_games as $game) {
            try {
                $insert_sql = "INSERT INTO games (title, description, price, discount_price, discount_percentage, category, platform, genre, developer, publisher, status, is_featured) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($insert_sql);
                $stmt->execute([
                    $game['title'],
                    $game['description'],
                    $game['price'],
                    $game['discount_price'],
                    $game['discount_percentage'],
                    $game['category'],
                    $game['platform'],
                    $game['genre'],
                    $game['developer'],
                    $game['publisher'],
                    $game['status'],
                    $game['featured']
                ]);
                echo "<p style='color: green;'>✓ Added game: " . $game['title'] . "</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠ Could not add game: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>✅ Games Table Fix Complete!</h3>";
    echo "<p>All missing columns have been added. The games API should work now.</p>";
    
    echo "<h3>Test Links:</h3>";
    echo "<ul>";
    echo "<li><a href='api/get_games.php'>Test Games API</a></li>";
    echo "<li><a href='index.html'>Test Homepage</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
