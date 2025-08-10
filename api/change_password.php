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
if(!empty($data->currentPassword) && !empty($data->newPassword) && !empty($data->userId)) {
    
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    try {
        // First, verify the current password
        $query = "SELECT password FROM users WHERE user_id = :userId LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':userId', $data->userId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if(password_verify($data->currentPassword, $user['password'])) {
                // Hash the new password
                $hashedNewPassword = password_hash($data->newPassword, PASSWORD_DEFAULT);
                
                // Update password
                $updateQuery = "UPDATE users SET password = :newPassword WHERE user_id = :userId";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':newPassword', $hashedNewPassword);
                $updateStmt->bindParam(':userId', $data->userId);
                
                if($updateStmt->execute()) {
                    http_response_code(200);
                    echo json_encode(array(
                        "success" => true,
                        "message" => "Password changed successfully."
                    ));
                } else {
                    http_response_code(503);
                    echo json_encode(array(
                        "success" => false,
                        "message" => "Unable to update password. Please try again."
                    ));
                }
            } else {
                http_response_code(401);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Current password is incorrect."
                ));
            }
        } else {
            http_response_code(404);
            echo json_encode(array(
                "success" => false,
                "message" => "User not found."
            ));
        }
    } catch(PDOException $e) {
        http_response_code(503);
        echo json_encode(array(
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Please provide current password, new password, and user ID."
    ));
}
?>
