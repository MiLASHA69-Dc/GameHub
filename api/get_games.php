<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Get filter parameters
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Build the base query
    $where_conditions = [];
    $params = [];
    
    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM games $where_clause";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_games = $count_stmt->fetch()['total'];
    
    // Get games data
    $sql = "
        SELECT 
            g.id,
            g.title,
            g.description,
            g.price,
            g.discount_price,
            g.discount_percentage,
            g.category,
            g.platform,
            g.system_requirements,
            g.release_date,
            g.image_url,
            g.game_setup_file,
            g.rating,
            g.is_featured,
            g.stock_quantity,
            g.created_at,
            g.updated_at,
            COALESCE(gf.file_name, 'No file') as setup_file_name,
            COALESCE(gf.file_size, 0) as setup_file_size
        FROM games g
        LEFT JOIN game_files gf ON g.id = gf.game_id AND gf.is_primary = 1
        $where_clause
        ORDER BY g.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the games data
    $formatted_games = array_map(function($game) {
        // Calculate final price
        $final_price = $game['discount_price'] ?? $game['price'];
        
        // Format file size
        $file_size_formatted = 'N/A';
        if ($game['setup_file_size'] > 0) {
            $file_size_formatted = formatFileSize($game['setup_file_size']);
        }
        
        // Calculate sales (placeholder - you might want to join with orders table)
        $sales = rand(50, 2000); // Replace with actual sales query
        
        return [
            'id' => $game['id'],
            'title' => $game['title'],
            'description' => substr($game['description'], 0, 100) . '...',
            'category' => ucfirst($game['category']),
            'platform' => ucfirst($game['platform']),
            'price' => number_format($game['price'], 2),
            'discount_price' => $game['discount_price'] ? number_format($game['discount_price'], 2) : null,
            'discount_percentage' => $game['discount_percentage'],
            'final_price' => number_format($final_price, 2),
            'image_url' => $game['image_url'] ?: 'https://via.placeholder.com/50x50/333/fff?text=' . substr($game['title'], 0, 2),
            'setup_file_name' => $game['setup_file_name'],
            'setup_file_size' => $file_size_formatted,
            'system_requirements' => ucfirst($game['system_requirements']),
            'stock_quantity' => $game['stock_quantity'],
            'sales' => $sales,
            'status' => 'active', // You can add a status column to games table if needed
            'is_featured' => $game['is_featured'],
            'release_date' => $game['release_date'],
            'created_at' => date('M j, Y', strtotime($game['created_at'])),
            'updated_at' => date('M j, Y g:i A', strtotime($game['updated_at']))
        ];
    }, $games);
    
    // Get categories for filter
    $categories_sql = "SELECT DISTINCT category FROM games WHERE category IS NOT NULL ORDER BY category";
    $categories_stmt = $db->query($categories_sql);
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'games' => $formatted_games,
        'total' => $total_games,
        'categories' => $categories,
        'pagination' => [
            'current_page' => floor($offset / $limit) + 1,
            'total_pages' => ceil($total_games / $limit),
            'limit' => $limit,
            'offset' => $offset
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch games: ' . $e->getMessage()
    ]);
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>
