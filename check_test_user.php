<?php
// Check Test User
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>Test User Check</h2>";

try {
    $query = "SELECT user_id, first_name, last_name, email, password, country FROM users WHERE email = 'test@gamehub.com'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Test user found!</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>User ID</td><td>" . $user['user_id'] . "</td></tr>";
        echo "<tr><td>Name</td><td>" . $user['first_name'] . " " . $user['last_name'] . "</td></tr>";
        echo "<tr><td>Email</td><td>" . $user['email'] . "</td></tr>";
        echo "<tr><td>Country</td><td>" . $user['country'] . "</td></tr>";
        echo "<tr><td>Password Hash</td><td>" . substr($user['password'], 0, 20) . "...</td></tr>";
        echo "</table>";
        
        // Test password verification
        echo "<br><h3>Password Verification Test:</h3>";
        if (password_verify('test123', $user['password'])) {
            echo "<p style='color: green;'>✓ Password 'test123' matches the hash</p>";
        } else {
            echo "<p style='color: red;'>✗ Password 'test123' does NOT match the hash</p>";
            echo "<p>Creating new user with correct password hash...</p>";
            
            // Delete old user and create new one with correct password
            $delete_query = "DELETE FROM users WHERE email = 'test@gamehub.com'";
            $db->exec($delete_query);
            
            $correct_hash = password_hash('test123', PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (first_name, last_name, email, password, country) 
                            VALUES ('Test', 'User', 'test@gamehub.com', :password, 'United States')";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':password', $correct_hash);
            $insert_stmt->execute();
            
            echo "<p style='color: green;'>✓ Test user recreated with correct password hash</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Test user not found</p>";
        
        // Create the test user
        echo "<p>Creating test user...</p>";
        $password_hash = password_hash('test123', PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (first_name, last_name, email, password, country) 
                        VALUES ('Test', 'User', 'test@gamehub.com', :password, 'United States')";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':password', $password_hash);
        $insert_stmt->execute();
        
        echo "<p style='color: green;'>✓ Test user created successfully</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Login Test:</h3>";
echo "<p>Email: <strong>test@gamehub.com</strong></p>";
echo "<p>Password: <strong>test123</strong></p>";
echo "<p><a href='pages/login.html'>Try login page now</a></p>";
?>
