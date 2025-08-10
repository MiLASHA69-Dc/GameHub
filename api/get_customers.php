<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Query to get all customers (users with role 'user' or NULL)
    $query = "SELECT 
                user_id,
                CONCAT(first_name, ' ', last_name) as name,
                email,
                country,
                role,
                DATE_FORMAT(created_at, '%b %d, %Y') as registration_date,
                IFNULL(created_at, NOW()) as created_at
              FROM users 
              WHERE role = 'user' OR role IS NULL OR role = ''
              ORDER BY created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $customers = array();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $customer = array(
            "id" => $row['user_id'],
            "name" => $row['name'],
            "email" => $row['email'],
            "country" => $row['country'],
            "registration_date" => $row['registration_date'] ?: 'N/A',
            "orders" => rand(0, 25), // Mock data for now
            "total_spent" => '$' . number_format(rand(0, 500) + (rand(0, 99) / 100), 2),
            "status" => "Active"
        );
        
        array_push($customers, $customer);
    }

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "customers" => $customers,
        "total" => count($customers)
    ));

} catch(PDOException $e) {
    http_response_code(503);
    echo json_encode(array(
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ));
}
?>
