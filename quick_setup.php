<?php
// Quick Database Setup Script
echo "<h2>GameHub Database Setup (Port 3307)</h2>";

try {
    // Connect to MySQL on port 3307
    $pdo = new PDO("mysql:host=localhost;port=3307", "root", "");
    echo "<p style='color: green;'>✓ Connected to MySQL on port 3307</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS video_game_storeg");
    echo "<p style='color: green;'>✓ Database 'video_game_storeg' created</p>";
    
    // Use the database
    $pdo->exec("USE video_game_storeg");
    echo "<p style='color: green;'>✓ Using database 'video_game_storeg'</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        country VARCHAR(50) NOT NULL,
        role VARCHAR(20) DEFAULT 'user'
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Users table created</p>";
    
    // Insert test user
    $sql = "INSERT IGNORE INTO users (first_name, last_name, email, password, country) 
            VALUES ('Test', 'User', 'test@gamehub.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'United States')";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Test user added (test@gamehub.com / test123)</p>";
    
    // Create games table
    $sql = "CREATE TABLE IF NOT EXISTS games (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        discount_price DECIMAL(10, 2) NULL,
        category VARCHAR(100),
        platform VARCHAR(100),
        release_date DATE,
        image_url VARCHAR(500),
        trailer_url VARCHAR(500),
        rating DECIMAL(2, 1) DEFAULT 0,
        is_featured BOOLEAN DEFAULT FALSE,
        is_new_release BOOLEAN DEFAULT FALSE,
        stock_quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Games table created</p>";
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Orders table created</p>";
    
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        game_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Order items table created</p>";
    
    // Create wishlist table
    $sql = "CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        game_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
        UNIQUE KEY unique_wishlist (user_id, game_id)
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Wishlist table created</p>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Database Setup Complete!</h3>";
    echo "<p><strong>You can now:</strong></p>";
    echo "<ul>";
    echo "<li>Login with: <strong>test@gamehub.com</strong> / <strong>test123</strong></li>";
    echo "<li>Visit the <a href='index.html'>homepage</a></li>";
    echo "<li>Try the <a href='pages/login.html'>login page</a></li>";
    echo "<li>Access <a href='admin/login.html'>admin panel</a></li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Setup failed: " . $e->getMessage() . "</p>";
}
?>
