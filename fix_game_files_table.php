<?php
// Fix Missing game_files Table
include_once 'config/database.php';

echo "<h2>Fix Missing game_files Table</h2>";

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<p style='color: green;'>✓ Connected to database</p>";

    // Create game_files table
    $game_files_sql = "
    CREATE TABLE IF NOT EXISTS game_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        game_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size BIGINT NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_primary BOOLEAN DEFAULT TRUE,
        INDEX idx_game_id (game_id),
        FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
    )";
    
    $db->exec($game_files_sql);
    echo "<p style='color: green;'>✓ Created game_files table successfully!</p>";
    
    // Verify table creation
    $verify_query = "DESCRIBE game_files";
    $result = $db->query($verify_query);
    
    if ($result) {
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: green;'>✓ Table structure verified!</p>";
    }
    
    // Check if there are any games to add sample file entries for
    $games_check = $db->query("SELECT COUNT(*) as count FROM games");
    $games_count = $games_check->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($games_count > 0) {
        echo "<p>Found $games_count games in database. You may want to add game files for them.</p>";
        
        // Add a sample game file entry for existing games
        $sample_file_sql = "
        INSERT IGNORE INTO game_files (game_id, file_name, file_path, file_size, file_type, is_primary)
        SELECT id, CONCAT(title, '_setup.exe'), CONCAT('uploads/games/', id, '_setup.exe'), 1048576, 'executable', 1
        FROM games 
        LIMIT 5
        ";
        
        try {
            $db->exec($sample_file_sql);
            echo "<p style='color: green;'>✓ Added sample game file entries</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ Could not add sample files: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>No games found in database yet.</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ game_files Table Fix Complete!</h3>";
    echo "<p>The missing table has been created successfully. The error should be resolved now.</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='index.html'>Test Homepage</a></li>";
    echo "<li><a href='api/get_games.php'>Test Games API</a></li>";
    echo "<li><a href='pages/login.html'>Test Login</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'game_files') !== false) {
        echo "<h3>Manual Fix:</h3>";
        echo "<p>Run this SQL in phpMyAdmin:</p>";
        echo "<textarea style='width:100%;height:200px;'>";
        echo "CREATE TABLE IF NOT EXISTS game_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_primary BOOLEAN DEFAULT TRUE,
    INDEX idx_game_id (game_id)
);";
        echo "</textarea>";
    }
}
?>
