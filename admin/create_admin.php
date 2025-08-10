<?php
// Create Admin Account Script
// Run this script once to create an admin account

include_once __DIR__ . '/../config/database.php';

// Admin account details
$admin_data = [
    'firstName' => 'Admin',
    'lastName' => 'User',
    'email' => 'admin@gamehub.com',
    'password' => 'admin123',
    'country' => 'United States',
    'role' => 'admin'
];

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // First, let's add a role column to the users table if it doesn't exist
    $add_role_query = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) DEFAULT 'user'";
    $db->exec($add_role_query);
    echo "✓ Role column added/verified in users table<br>";

    // Check if admin email already exists
    $check_query = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $admin_data['email']);
    $check_stmt->execute();

    if($check_stmt->rowCount() > 0) {
        echo "⚠️ Admin email already exists in the database.<br>";
        
        // Update existing user to admin role
        $update_query = "UPDATE users SET role = 'admin' WHERE email = :email";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':email', $admin_data['email']);
        
        if($update_stmt->execute()) {
            echo "✓ Existing user updated to admin role successfully!<br>";
        }
    } else {
        // Insert admin user
        $insert_query = "INSERT INTO users (first_name, last_name, email, password, country, role) 
                        VALUES (:firstName, :lastName, :email, :password, :country, :role)";
        
        $stmt = $db->prepare($insert_query);
        
        // Hash the password
        $hashed_password = password_hash($admin_data['password'], PASSWORD_DEFAULT);
        
        // Bind data
        $stmt->bindParam(':firstName', $admin_data['firstName']);
        $stmt->bindParam(':lastName', $admin_data['lastName']);
        $stmt->bindParam(':email', $admin_data['email']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':country', $admin_data['country']);
        $stmt->bindParam(':role', $admin_data['role']);

        // Execute query
        if($stmt->execute()) {
            echo "✅ Admin account created successfully!<br><br>";
            echo "<strong>Admin Login Credentials:</strong><br>";
            echo "Email: " . $admin_data['email'] . "<br>";
            echo "Password: " . $admin_data['password'] . "<br><br>";
            echo "⚠️ <strong>Important:</strong> Please change the admin password after first login for security.<br>";
        } else {
            echo "❌ Failed to create admin account.<br>";
        }
    }

    echo "<br><hr><br>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Delete this file (create_admin.php) for security<br>";
    echo "2. Go to <a href='login.html'>admin login</a> and use the credentials above<br>";
    echo "3. Change the admin password from the settings section<br>";

} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Creation - GameHub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        hr { margin: 20px 0; }
        a {
            color: #ff8c00;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 GameHub Admin Account Setup</h1>
    </div>
</body>
</html>
