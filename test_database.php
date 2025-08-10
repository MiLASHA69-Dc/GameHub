<?php
// Database Connection Test
echo "<h2>GameHub Database Connection Test</h2>";

try {
    // Test basic MySQL connection
    $pdo = new PDO("mysql:host=localhost;port=3307", "root", "");
    echo "<p style='color: green;'>✓ MySQL connection successful (port 3307)</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'video_game_storeg'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Database 'video_game_storeg' exists</p>";
        
        // Connect to the specific database
        $pdo = new PDO("mysql:host=localhost;port=3307;dbname=video_game_storeg", "root", "");
        echo "<p style='color: green;'>✓ Connected to video_game_storeg database</p>";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p style='color: green;'>✓ Found " . count($tables) . " tables:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
            
            // Check if test user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'test@gamehub.com'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Test user exists in database</p>";
            } else {
                echo "<p style='color: orange;'>⚠ Test user not found</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ No tables found in database</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database 'video_game_storeg' does not exist</p>";
        echo "<p><strong>Action needed:</strong> Create the database using setup.sql</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL service is running in XAMPP Control Panel</li>";
    echo "<li>Check if MySQL port (3306) is available</li>";
    echo "<li>Try restarting XAMPP services</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If database doesn't exist, go to <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
echo "<li>Create database and run the setup.sql file</li>";
echo "<li>Test login again</li>";
echo "</ol>";
?>
