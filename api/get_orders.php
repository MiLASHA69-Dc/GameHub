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

// Get optional filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

try {
    $database = new Database();
    $db = $database->getConnection();

    // Build the query with optional status filter
    $whereClause = "";
    $params = array();
    
    if (!empty($status) && $status !== 'all') {
        $whereClause = "WHERE o.status = ?";
        $params[] = $status;
    }

    // Get orders with customer information and item count
    $query = "SELECT 
                o.order_id,
                o.order_number,
                o.customer_id,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                u.email as customer_email,
                o.order_date,
                o.total_amount,
                o.status,
                o.payment_method,
                o.payment_status,
                COUNT(oi.item_id) as item_count,
                GROUP_CONCAT(oi.product_name SEPARATOR ', ') as products
              FROM orders o
              LEFT JOIN users u ON o.customer_id = u.user_id
              LEFT JOIN order_items oi ON o.order_id = oi.order_id
              $whereClause
              GROUP BY o.order_id
              ORDER BY o.order_date DESC
              LIMIT $limit OFFSET $offset";
    
    $stmt = $db->prepare($query);
    
    // Bind status parameter if exists
    if (!empty($status) && $status !== 'all') {
        $stmt->bindParam(1, $status);
    }
    
    $stmt->execute();
    $orders = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $order = array(
            "order_id" => $row['order_id'],
            "order_number" => $row['order_number'],
            "customer_id" => $row['customer_id'],
            "customer_name" => $row['customer_name'] ?? 'Unknown Customer',
            "customer_email" => $row['customer_email'] ?? '',
            "order_date" => $row['order_date'],
            "formatted_date" => date('M d, Y', strtotime($row['order_date'])),
            "total_amount" => floatval($row['total_amount']),
            "formatted_total" => '$' . number_format($row['total_amount'], 2),
            "status" => $row['status'],
            "payment_method" => $row['payment_method'] ?? '',
            "payment_status" => $row['payment_status'] ?? '',
            "item_count" => intval($row['item_count']),
            "items_text" => $row['item_count'] . ' item' . ($row['item_count'] != 1 ? 's' : ''),
            "products" => $row['products'] ?? ''
        );
        
        array_push($orders, $order);
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM orders o $whereClause";
    $countStmt = $db->prepare($countQuery);
    
    // Bind status parameter if exists
    if (!empty($status) && $status !== 'all') {
        $countStmt->bindParam(1, $status);
    }
    
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "orders" => $orders,
        "total" => intval($totalCount),
        "count" => count($orders)
    ));

} catch(PDOException $e) {
    http_response_code(503);
    echo json_encode(array(
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ));
}
?>
