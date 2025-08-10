<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Method not allowed"));
    exit();
}

// Get user ID from query parameter
$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

if (!$userId) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "User ID is required"));
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get user profile data
    $query = "SELECT cp.*, u.first_name as user_first_name, u.last_name as user_last_name, u.email as user_email 
              FROM customer_profiles cp 
              RIGHT JOIN users u ON cp.user_id = u.user_id 
              WHERE u.user_id = ? AND u.role = 'customer'";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $userId);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // If no profile exists, create default data from user table
        if (!$result['user_id']) {
            $profile = array(
                "first_name" => $result['user_first_name'] ?? '',
                "last_name" => $result['user_last_name'] ?? '',
                "email" => $result['user_email'],
                "contact_number" => '',
                "address" => '',
                "age" => '',
                "country" => 'United States',
                "preferred_currency" => 'USD ($)'
            );
        } else {
            $profile = array(
                "first_name" => $result['first_name'] ?? $result['user_first_name'],
                "last_name" => $result['last_name'] ?? $result['user_last_name'],
                "email" => $result['email'] ?? $result['user_email'],
                "contact_number" => $result['contact_number'] ?? '',
                "address" => $result['address'] ?? '',
                "age" => $result['age'] ?? '',
                "country" => $result['country'] ?? 'United States',
                "preferred_currency" => $result['preferred_currency'] ?? 'USD ($)'
            );
        }
        
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "profile" => $profile
        ));
    } else {
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "User not found"));
    }

} catch(PDOException $e) {
    http_response_code(503);
    echo json_encode(array(
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ));
}
?>
