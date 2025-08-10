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
if (!isset($data->order_id) || !isset($data->action)) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Order ID and action are required"));
    exit();
}

$orderId = intval($data->order_id);
$action = trim($data->action);

// Define valid actions and their corresponding status changes
$validActions = array(
    'process' => 'processing',
    'complete' => 'completed',
    'cancel' => 'cancelled',
    'refund' => 'refunded'
);

if (!array_key_exists($action, $validActions)) {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Invalid action"));
    exit();
}

$newStatus = $validActions[$action];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Start transaction
    $db->beginTransaction();

    // Check if order exists
    $checkQuery = "SELECT order_id, status, customer_id, total_amount FROM orders WHERE order_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $orderId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        $db->rollback();
        http_response_code(404);
        echo json_encode(array("success" => false, "message" => "Order not found"));
        exit();
    }

    $order = $checkStmt->fetch(PDO::FETCH_ASSOC);

    // Update order status
    $updateQuery = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(1, $newStatus);
    $updateStmt->bindParam(2, $orderId);
    
    if ($updateStmt->execute()) {
        // If refund action, also update payment status
        if ($action === 'refund') {
            $paymentQuery = "UPDATE orders SET payment_status = 'refunded' WHERE order_id = ?";
            $paymentStmt = $db->prepare($paymentQuery);
            $paymentStmt->bindParam(1, $orderId);
            $paymentStmt->execute();
        }
        
        $db->commit();
        
        $actionMessages = array(
            'process' => 'Order marked as processing',
            'complete' => 'Order completed successfully',
            'cancel' => 'Order cancelled',
            'refund' => 'Order refunded successfully'
        );
        
        http_response_code(200);
        echo json_encode(array(
            "success" => true, 
            "message" => $actionMessages[$action],
            "new_status" => $newStatus
        ));
    } else {
        $db->rollback();
        http_response_code(500);
        echo json_encode(array("success" => false, "message" => "Failed to update order status"));
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
