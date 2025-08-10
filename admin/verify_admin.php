<?php
// Verify Admin Account Script
include_once __DIR__ . '/../config/database.php';

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check admin user details
    $query = "SELECT user_id, first_name, last_name, email, role, password FROM users WHERE email = 'admin@gamehub.com'";
    $stmt = $db->prepare($query);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Admin user found in database:<br>";
        echo "ID: " . $admin['user_id'] . "<br>";
        echo "Name: " . $admin['first_name'] . " " . $admin['last_name'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Role: " . $admin['role'] . "<br>";
        echo "Password Hash (first 20 chars): " . substr($admin['password'], 0, 20) . "...<br><br>";
        
        // Test password verification
        $testPassword = 'newAdminPassword123!';
        if(password_verify($testPassword, $admin['password'])) {
            echo "✅ Password verification successful for: " . $testPassword . "<br>";
        } else {
            echo "❌ Password verification failed for: " . $testPassword . "<br>";
            
            // Test old password
            $oldPassword = 'admin123';
            if(password_verify($oldPassword, $admin['password'])) {
                echo "⚠️ Old password still active: " . $oldPassword . "<br>";
            }
        }
        
    } else {
        echo "❌ Admin user not found in database<br>";
    }

    // Check table structure
    echo "<br><strong>Users table structure:</strong><br>";
    $structure = $db->query("DESCRIBE users");
    while($column = $structure->fetch(PDO::FETCH_ASSOC)) {
        echo $column['Field'] . " (" . $column['Type'] . ")<br>";
    }

} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>
