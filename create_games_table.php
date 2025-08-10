<?php
require_once 'config/database.php';

echo "Creating games table with all required fields...\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Create the complete games table
    $games_table_sql = "
    CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        discount_price DECIMAL(10, 2) NULL,
        discount_percentage DECIMAL(5,2) DEFAULT 0,
        category VARCHAR(100),
        platform VARCHAR(100),
        system_requirements VARCHAR(100) DEFAULT 'medium',
        release_date DATE,
        image_url VARCHAR(500),
        trailer_url VARCHAR(500),
        game_setup_file VARCHAR(500) NULL,
        rating DECIMAL(2, 1) DEFAULT 0,
        is_featured BOOLEAN DEFAULT FALSE,
        is_new_release BOOLEAN DEFAULT FALSE,
        stock_quantity INT DEFAULT 999999,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $db->exec($games_table_sql);
    echo "✓ Created games table with all required fields\n";
    
    // Verify the table structure
    echo "\nGames table structure:\n";
    $result = $db->query('DESCRIBE games');
    while($row = $result->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\n✓ Database is now ready for storing game data!\n";
    
    // Test the add_product.php API
    echo "\nTesting add_product.php API availability...\n";
    if(file_exists('api/add_product.php')) {
        echo "✓ add_product.php API is available\n";
    } else {
        echo "❌ add_product.php API not found\n";
    }
    
    echo "\n🎮 Your GameHub admin panel is ready!\n";
    echo "📋 What you can do now:\n";
    echo "1. Go to: http://localhost/video_game_storeG/admin/dashboard.html\n";
    echo "2. Click 'Add New Game' button\n";
    echo "3. Fill in all the game details\n";
    echo "4. Upload a game setup file\n";
    echo "5. Submit to store in database\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
