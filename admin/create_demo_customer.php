<?php
// Create Demo Customer Account
include_once __DIR__ . '/../config/database.php';

// Demo customer data
$customer_data = [
    'firstName' => 'Demo',
    'lastName' => 'Customer',
    'email' => 'customer@test.com',
    'password' => 'customer123',
    'country' => 'United States',
    'role' => 'user'
];

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check if customer email already exists
    $check_query = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $customer_data['email']);
    $check_stmt->execute();

    if($check_stmt->rowCount() > 0) {
        echo "⚠️ Demo customer email already exists in the database.<br>";
    } else {
        // Insert demo customer
        $insert_query = "INSERT INTO users (first_name, last_name, email, password, country, role) 
                        VALUES (:firstName, :lastName, :email, :password, :country, :role)";
        
        $stmt = $db->prepare($insert_query);
        
        // Hash the password
        $hashed_password = password_hash($customer_data['password'], PASSWORD_DEFAULT);
        
        // Bind data
        $stmt->bindParam(':firstName', $customer_data['firstName']);
        $stmt->bindParam(':lastName', $customer_data['lastName']);
        $stmt->bindParam(':email', $customer_data['email']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':country', $customer_data['country']);
        $stmt->bindParam(':role', $customer_data['role']);

        // Execute query
        if($stmt->execute()) {
            echo "✅ Demo customer account created successfully!<br><br>";
            echo "<strong>Demo Customer Login Credentials:</strong><br>";
            echo "Email: " . $customer_data['email'] . "<br>";
            echo "Password: " . $customer_data['password'] . "<br>";
            echo "Role: " . $customer_data['role'] . "<br>";
        } else {
            echo "❌ Failed to create demo customer account.<br>";
        }
    }

} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>
