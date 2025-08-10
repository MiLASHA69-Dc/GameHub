<?php
require_once 'config/database.php';

echo "Adding sample game to test the dashboard...\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Insert a sample game
    $sample_game = [
        'title' => 'Cyberpunk 2077',
        'description' => 'An open-world, action-adventure story set in Night City, a megalopolis obsessed with power, glamour and body modification.',
        'price' => 59.99,
        'discount_percentage' => 25,
        'category' => 'action',
        'platform' => 'windows',
        'system_requirements' => 'high',
        'release_date' => '2020-12-10',
        'image_url' => 'https://images.gog-statics.com/5643a7c831df452d29005caeca24c231d6f5b4bf22139bc9f7ec2cd94ad93888_product_card_v2_mobile_slider_639.jpg',
        'stock_quantity' => 999999,
        'is_featured' => 1
    ];
    
    // Calculate discount price
    $discount_price = $sample_game['price'] - ($sample_game['price'] * ($sample_game['discount_percentage'] / 100));
    
    $sql = "
        INSERT INTO games (
            title, description, price, discount_price, discount_percentage, 
            category, platform, system_requirements, release_date, image_url, 
            stock_quantity, is_featured, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $sample_game['title'],
        $sample_game['description'],
        $sample_game['price'],
        $discount_price,
        $sample_game['discount_percentage'],
        $sample_game['category'],
        $sample_game['platform'],
        $sample_game['system_requirements'],
        $sample_game['release_date'],
        $sample_game['image_url'],
        $sample_game['stock_quantity'],
        $sample_game['is_featured']
    ]);
    
    $game_id = $db->lastInsertId();
    
    // Add a mock game file entry
    $file_sql = "
        INSERT INTO game_files (game_id, file_name, file_path, file_size, file_type, is_primary)
        VALUES (?, ?, ?, ?, ?, 1)
    ";
    
    $file_stmt = $db->prepare($file_sql);
    $file_stmt->execute([
        $game_id,
        'cyberpunk_2077_setup.exe',
        'uploads/games/sample_cyberpunk.exe',
        2147483648, // 2GB
        'application/x-msdownload'
    ]);
    
    echo "✓ Sample game 'Cyberpunk 2077' added successfully!\n";
    echo "✓ Game ID: $game_id\n";
    echo "✓ Price: \$59.99 (25% off = \$" . number_format($discount_price, 2) . ")\n";
    echo "✓ Platform: Windows\n";
    echo "✓ Category: Action\n";
    echo "\nNow visit the admin dashboard to see the game!\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
