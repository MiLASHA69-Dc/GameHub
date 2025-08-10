<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Method not allowed"));
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if (!isset($data->userId) || !isset($data->first_name) || !isset($data->email)) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Required fields missing"));
    exit();
}

$userId = intval($data->userId);
$firstName = trim($data->first_name);
$lastName = trim($data->last_name ?? '');
$email = trim($data->email);
$contactNumber = trim($data->contact_number ?? '');
$address = trim($data->address ?? '');
$age = isset($data->age) && $data->age !== '' ? intval($data->age) : null;
$country = trim($data->country ?? 'United States');
$preferredCurrency = trim($data->preferred_currency ?? 'USD ($)');

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Invalid email format"));
    exit();
}

// Validate age if provided
if ($age !== null && ($age < 13 || $age > 120)) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Age must be between 13 and 120"));
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Start transaction
    $db->beginTransaction();

    // Check if user exists and is a customer
    $checkQuery = "SELECT user_id, email FROM users WHERE user_id = ? AND role = 'customer'";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $userId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        $db->rollback();
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "User not found"));
        exit();
    }

    // Update user email in users table if changed
    $updateUserQuery = "UPDATE users SET email = ? WHERE user_id = ?";
    $updateUserStmt = $db->prepare($updateUserQuery);
    $updateUserStmt->bindParam(1, $email);
    $updateUserStmt->bindParam(2, $userId);
    $updateUserStmt->execute();

    // Check if profile exists
    $profileCheckQuery = "SELECT id FROM customer_profiles WHERE user_id = ?";
    $profileCheckStmt = $db->prepare($profileCheckQuery);
    $profileCheckStmt->bindParam(1, $userId);
    $profileCheckStmt->execute();

    if ($profileCheckStmt->rowCount() > 0) {
        // Update existing profile
        $updateQuery = "UPDATE customer_profiles SET 
                        first_name = ?, 
                        last_name = ?, 
                        email = ?, 
                        contact_number = ?, 
                        address = ?, 
                        age = ?, 
                        country = ?, 
                        preferred_currency = ?,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE user_id = ?";
        
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(1, $firstName);
        $updateStmt->bindParam(2, $lastName);
        $updateStmt->bindParam(3, $email);
        $updateStmt->bindParam(4, $contactNumber);
        $updateStmt->bindParam(5, $address);
        $updateStmt->bindParam(6, $age);
        $updateStmt->bindParam(7, $country);
        $updateStmt->bindParam(8, $preferredCurrency);
        $updateStmt->bindParam(9, $userId);
        
        if ($updateStmt->execute()) {
            $db->commit();
            http_response_code(200);
            echo json_encode(array("success" => true, "message" => "Profile updated successfully"));
        } else {
            $db->rollback();
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Failed to update profile"));
        }
    } else {
        // Insert new profile
        $insertQuery = "INSERT INTO customer_profiles 
                        (user_id, first_name, last_name, email, contact_number, address, age, country, preferred_currency) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(1, $userId);
        $insertStmt->bindParam(2, $firstName);
        $insertStmt->bindParam(3, $lastName);
        $insertStmt->bindParam(4, $email);
        $insertStmt->bindParam(5, $contactNumber);
        $insertStmt->bindParam(6, $address);
        $insertStmt->bindParam(7, $age);
        $insertStmt->bindParam(8, $country);
        $insertStmt->bindParam(9, $preferredCurrency);
        
        if ($insertStmt->execute()) {
            $db->commit();
            http_response_code(201);
            echo json_encode(array("success" => true, "message" => "Profile created successfully"));
        } else {
            $db->rollback();
            http_response_code(500);
            echo json_encode(array("success" => false, "message" => "Failed to create profile"));
        }
    }

} catch(PDOException $e) {
    $db->rollback();
    http_response_code(503);
    echo json_encode(array(
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ));
}
?>
