<?php
// Quick setup script for GameHub
echo "<h1>GameHub Setup Checker</h1>";

// Check if we can connect to database
try {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Check if tables exist
        $tables = ['users', 'games', 'orders', 'order_items', 'wishlist'];
        foreach ($tables as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✓ Table '$table' exists</p>";
            } else {
                echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
            }
        }
        
        // Check if test user exists
        $query = "SELECT COUNT(*) as count FROM users WHERE email = 'test@gamehub.com'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo "<p style='color: green;'>✓ Test user account exists</p>";
            echo "<p><strong>Test Login:</strong><br>Email: test@gamehub.com<br>Password: test123</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Test user account not found</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Setup Instructions:</strong></p>";
    echo "<ol>";
    echo "<li>Make sure XAMPP Apache and MySQL services are running</li>";
    echo "<li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "<li>Click 'SQL' tab and paste the contents of database/setup.sql</li>";
    echo "<li>Click 'Go' to execute the SQL</li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<h3>Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='index.html'>Main Homepage</a></li>";
echo "<li><a href='pages/register.html'>Register New User</a></li>";
echo "<li><a href='pages/login.html'>User Login</a></li>";
echo "<li><a href='admin/login.html'>Admin Login</a></li>";
echo "<li><a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
echo "</ul>";

echo "<h3>System Information:</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
?>
