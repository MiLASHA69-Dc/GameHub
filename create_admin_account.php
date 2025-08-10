<?php
// Create Admin Account for GameHub
include_once 'config/database.php';

echo "<h2>GameHub Admin Account Creation</h2>";

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<p style='color: green;'>✓ Connected to database</p>";

    // Add role column to users table if it doesn't exist
    try {
        $db->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
        echo "<p style='color: green;'>✓ Added role column to users table</p>";
    } catch (Exception $e) {
        // Column probably already exists
        echo "<p style='color: orange;'>⚠ Role column already exists or couldn't be added</p>";
    }

    // Admin account details
    $admin_email = 'admin@gamehub.com';
    $admin_password = 'admin123';
    $admin_first_name = 'Admin';
    $admin_last_name = 'User';
    $admin_country = 'United States';
    $admin_role = 'admin';

    // Check if admin already exists
    $check_query = "SELECT user_id, role FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $admin_email);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update existing user to admin
        $update_query = "UPDATE users SET role = 'admin' WHERE email = :email";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':email', $admin_email);
        $update_stmt->execute();
        
        echo "<p style='color: green;'>✓ Updated existing user to admin role</p>";
        echo "<p><strong>Existing Admin Account:</strong></p>";
        echo "<p>Email: <strong>$admin_email</strong></p>";
        echo "<p>Password: <strong>$admin_password</strong></p>";
        
    } else {
        // Create new admin account
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (first_name, last_name, email, password, country, role) 
                        VALUES (:first_name, :last_name, :email, :password, :country, :role)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':first_name', $admin_first_name);
        $insert_stmt->bindParam(':last_name', $admin_last_name);
        $insert_stmt->bindParam(':email', $admin_email);
        $insert_stmt->bindParam(':password', $hashed_password);
        $insert_stmt->bindParam(':country', $admin_country);
        $insert_stmt->bindParam(':role', $admin_role);
        
        if ($insert_stmt->execute()) {
            echo "<p style='color: green;'>✓ Admin account created successfully!</p>";
            
            echo "<h3 style='color: green;'>Admin Login Credentials:</h3>";
            echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
            echo "<p><strong>Email:</strong> $admin_email</p>";
            echo "<p><strong>Password:</strong> $admin_password</p>";
            echo "<p><strong>Role:</strong> $admin_role</p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create admin account</p>";
        }
    }

    // Verify admin account
    $verify_query = "SELECT user_id, first_name, last_name, email, role FROM users WHERE email = :email AND role = 'admin'";
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->bindParam(':email', $admin_email);
    $verify_stmt->execute();

    if ($verify_stmt->rowCount() > 0) {
        $admin = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        echo "<br><h3>Admin Account Verification:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>User ID</td><td>" . $admin['user_id'] . "</td></tr>";
        echo "<tr><td>Name</td><td>" . $admin['first_name'] . " " . $admin['last_name'] . "</td></tr>";
        echo "<tr><td>Email</td><td>" . $admin['email'] . "</td></tr>";
        echo "<tr><td>Role</td><td><strong>" . $admin['role'] . "</strong></td></tr>";
        echo "</table>";
        echo "<p style='color: green;'>✓ Admin account verified successfully!</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='admin/login.html' target='_blank'>Login to Admin Panel</a></li>";
echo "<li><a href='pages/login.html' target='_blank'>Login as Regular User</a></li>";
echo "<li><a href='index.html' target='_blank'>Visit Homepage</a></li>";
echo "</ol>";

echo "<h3>All Login Credentials:</h3>";
echo "<div style='background: #f9f9f9; padding: 15px; border: 1px solid #ddd;'>";
echo "<p><strong>Admin Account:</strong></p>";
echo "<p>Email: admin@gamehub.com | Password: admin123</p>";
echo "<p><strong>Test User Account:</strong></p>";
echo "<p>Email: test@gamehub.com | Password: test123</p>";
echo "</div>";
?>
