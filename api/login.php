<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->email) && !empty($data->password)) {
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Query to check user credentials
    $query = "SELECT user_id, first_name, last_name, email, password, country, role FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $data->email);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(password_verify($data->password, $user['password'])) {
            // Password is correct
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_country'] = $user['country'];
            $_SESSION['user_role'] = $user['role'] ?? 'user';

            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Login successful.",
                "user" => array(
                    "id" => $user['user_id'],
                    "name" => $user['first_name'] . ' ' . $user['last_name'],
                    "email" => $user['email'],
                    "country" => $user['country'],
                    "role" => $user['role'] ?? 'user'
                )
            ));
        } else {
            // Password is incorrect
            http_response_code(401);
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid email or password."
            ));
        }
    } else {
        // User not found
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Invalid email or password."
        ));
    }
} else {
    // Required data is missing
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Please provide both email and password."
    ));
}
?>
