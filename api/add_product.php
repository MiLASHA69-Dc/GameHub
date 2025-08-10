<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get form data
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discount = floatval($_POST['discount'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $system_requirements = trim($_POST['system_requirements'] ?? '');
    $release_date = $_POST['release_date'] ?? null;
    
    // Calculate discount price if discount is provided
    $discount_price = null;
    if ($discount > 0 && $discount <= 100) {
        $discount_price = $price - ($price * ($discount / 100));
    }

    // Validate required fields
    if (empty($name)) {
        throw new Exception('Game name is required');
    }
    if (empty($category)) {
        throw new Exception('Category is required');
    }
    if ($price <= 0) {
        throw new Exception('Price must be greater than 0');
    }
    if (empty($description)) {
        throw new Exception('Description is required');
    }
    if (empty($platform)) {
        throw new Exception('Platform is required');
    }

    // Handle file upload
    $game_setup_path = null;
    if (isset($_FILES['game_setup']) && $_FILES['game_setup']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/games/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        $file = $_FILES['game_setup'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file
        $allowed_extensions = ['exe', 'msi', 'zip', 'rar', '7z', 'iso', 'bin'];
        if (!in_array($file_ext, $allowed_extensions)) {
            throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowed_extensions));
        }

        // Check file size (max 500MB)
        $max_size = 500 * 1024 * 1024; // 500MB in bytes
        if ($file_size > $max_size) {
            throw new Exception('File size too large. Maximum 500MB allowed');
        }

        // Generate unique filename
        $unique_name = uniqid() . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $unique_name;

        // Move uploaded file
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            throw new Exception('Failed to upload file');
        }

        $game_setup_path = 'uploads/games/' . $unique_name;
    } else {
        throw new Exception('Game setup file is required');
    }

    // Insert product into games table
    $stmt = $db->prepare("
        INSERT INTO games (
            title, 
            description, 
            price, 
            discount_price, 
            category, 
            platform, 
            release_date, 
            image_url, 
            is_featured, 
            stock_quantity, 
            created_at
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 999999, NOW())
    ");

    $stmt->bindParam(1, $name);
    $stmt->bindParam(2, $description);
    $stmt->bindParam(3, $price);
    $stmt->bindParam(4, $discount_price);
    $stmt->bindParam(5, $category);
    $stmt->bindParam(6, $platform);
    $stmt->bindParam(7, $release_date);
    $stmt->bindParam(8, $image_url);

    if ($stmt->execute()) {
        $product_id = $db->lastInsertId();
        
        // Insert game file information into game_files table
        if ($game_setup_path) {
            $file_stmt = $db->prepare("
                INSERT INTO game_files (game_id, file_name, file_path, file_size, file_type, is_primary)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            
            $original_filename = $_FILES['game_setup']['name'];
            $file_size = $_FILES['game_setup']['size'];
            $file_type = $_FILES['game_setup']['type'];
            
            $file_stmt->bindParam(1, $product_id);
            $file_stmt->bindParam(2, $original_filename);
            $file_stmt->bindParam(3, $game_setup_path);
            $file_stmt->bindParam(4, $file_size);
            $file_stmt->bindParam(5, $file_type);
            
            $file_stmt->execute();
        }
        
        // Update games table with additional fields
        $update_stmt = $db->prepare("
            UPDATE games 
            SET system_requirements = ?, 
                game_setup_file = ?, 
                discount_percentage = ? 
            WHERE id = ?
        ");
        
        $update_stmt->bindParam(1, $system_requirements);
        $update_stmt->bindParam(2, $game_setup_path);
        $update_stmt->bindParam(3, $discount);
        $update_stmt->bindParam(4, $product_id);
        
        $update_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Game added successfully',
            'product_id' => $product_id,
            'data' => [
                'id' => $product_id,
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'discount' => $discount,
                'discount_price' => $discount_price,
                'description' => $description,
                'image_url' => $image_url,
                'platform' => $platform,
                'system_requirements' => $system_requirements,
                'release_date' => $release_date,
                'game_setup_path' => $game_setup_path,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Failed to add game: ' . implode(' ', $stmt->errorInfo()));
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
