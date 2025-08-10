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
if(
    !empty($data->firstName) &&
    !empty($data->lastName) &&
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->country)
) {
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check if email already exists
    $check_query = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $data->email);
    $check_stmt->execute();

    if($check_stmt->rowCount() > 0) {
        // Email already exists
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Email already exists. Please use a different email address."
        ));
    } else {
        // Insert user data
        $query = "INSERT INTO users (first_name, last_name, email, password, country) 
                  VALUES (:firstName, :lastName, :email, :password, :country)";
        
        $stmt = $db->prepare($query);
        
        // Hash the password
        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
        
        // Bind data
        $stmt->bindParam(':firstName', $data->firstName);
        $stmt->bindParam(':lastName', $data->lastName);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':country', $data->country);

        // Execute query
        if($stmt->execute()) {
            // Registration successful
            http_response_code(201);
            echo json_encode(array(
                "success" => true,
                "message" => "User registration successful."
            ));
        } else {
            // Registration failed
            http_response_code(503);
            echo json_encode(array(
                "success" => false,
                "message" => "Unable to register user. Please try again."
            ));
        }
    }
} else {
    // Required data is missing
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to register user. Data is incomplete."
    ));
}
?>
