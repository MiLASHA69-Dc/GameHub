<?php
// Test Admin Login with New Password
include_once __DIR__ . '/../config/database.php';

$testEmail = 'admin@gamehub.com';
$testPassword = 'newAdminPassword123!';

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Query to check user credentials (same as login.php)
    $query = "SELECT user_id, first_name, last_name, email, password, country, role FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $testEmail);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "✅ User found in database<br>";
        echo "Testing password verification...<br>";
        
        if(password_verify($testPassword, $user['password'])) {
            echo "✅ Password verification successful!<br>";
            echo "Role: " . $user['role'] . "<br>";
            
            if($user['role'] === 'admin') {
                echo "✅ Admin access confirmed!<br><br>";
                echo "<strong>Login should work with:</strong><br>";
                echo "Email: " . $testEmail . "<br>";
                echo "Password: " . $testPassword . "<br>";
            } else {
                echo "❌ User is not an admin<br>";
            }
        } else {
            echo "❌ Password verification failed<br>";
            
            // Test with old password
            echo "Testing with old password (admin123)...<br>";
            if(password_verify('admin123', $user['password'])) {
                echo "⚠️ Old password is still active<br>";
            } else {
                echo "❌ Old password also doesn't work<br>";
            }
        }
    } else {
        echo "❌ User not found<br>";
    }

} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>
