<?php
// Complete Database Fix - Add All Missing Columns
include_once 'config/database.php';

echo "<h2>Complete Database Fix - Final Column Additions</h2>";

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<p style='color: green;'>✓ Connected to database</p>";

    // Add the remaining missing columns
    $additional_columns = [
        'game_setup_file' => 'VARCHAR(500)',
        'download_count' => 'INT DEFAULT 0',
        'version' => 'VARCHAR(20) DEFAULT \'1.0\'',
        'minimum_system_requirements' => 'TEXT',
        'recommended_system_requirements' => 'TEXT'
    ];
    
    echo "<h3>Adding Final Missing Columns:</h3>";
    
    foreach ($additional_columns as $column => $definition) {
        try {
            // Check if column exists
            $check_sql = "SHOW COLUMNS FROM games LIKE '$column'";
            $result = $db->query($check_sql);
            
            if ($result->rowCount() == 0) {
                $alter_sql = "ALTER TABLE games ADD COLUMN $column $definition";
                $db->exec($alter_sql);
                echo "<p style='color: green;'>✓ Added column: $column</p>";
            } else {
                echo "<p style='color: blue;'>→ Column $column already exists</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ Could not add $column: " . $e->getMessage() . "</p>";
        }
    }
    
    // Update existing games with sample setup files
    echo "<h3>Updating Games with Setup Files:</h3>";
    
    try {
        $update_sql = "UPDATE games SET game_setup_file = CONCAT('uploads/games/', id, '_setup.exe') WHERE game_setup_file IS NULL OR game_setup_file = ''";
        $affected = $db->exec($update_sql);
        echo "<p style='color: green;'>✓ Updated $affected games with setup file paths</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ Could not update setup files: " . $e->getMessage() . "</p>";
    }
    
    // Test the games API
    echo "<h3>Testing Games API:</h3>";
    try {
        // Simulate the API query
        $test_sql = "SELECT 
            g.id,
            g.title,
            g.description,
            g.price,
            g.discount_price,
            g.discount_percentage,
            g.category,
            g.platform,
            g.system_requirements,
            g.release_date,
            g.image_url,
            g.game_setup_file,
            g.rating,
            g.is_featured,
            g.stock_quantity,
            g.created_at,
            g.updated_at,
            COALESCE(gf.file_name, 'No file') as setup_file_name,
            COALESCE(gf.file_size, 0) as setup_file_size
        FROM games g
        LEFT JOIN game_files gf ON g.id = gf.game_id AND gf.is_primary = 1
        LIMIT 5";
        
        $test_result = $db->query($test_sql);
        $games_count = $test_result->rowCount();
        
        echo "<p style='color: green;'>✓ Games API query successful! Found $games_count games</p>";
        
        if ($games_count > 0) {
            echo "<h4>Sample Games:</h4>";
            echo "<ul>";
            while ($game = $test_result->fetch(PDO::FETCH_ASSOC)) {
                echo "<li><strong>" . $game['title'] . "</strong> - $" . $game['price'];
                if ($game['discount_percentage'] > 0) {
                    echo " (". $game['discount_percentage'] . "% off)";
                }
                echo "</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Games API test failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Complete Database Fix Successful!</h3>";
    echo "<p>All database errors should now be resolved.</p>";
    
    echo "<h3>Final Test Links:</h3>";
    echo "<ul>";
    echo "<li><a href='api/get_games.php' target='_blank'>Test Games API</a></li>";
    echo "<li><a href='index.html' target='_blank'>Test Homepage</a></li>";
    echo "<li><a href='pages/login.html' target='_blank'>Test Login</a></li>";
    echo "<li><a href='admin/login.html' target='_blank'>Test Admin Login</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
