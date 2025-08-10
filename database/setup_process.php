<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    $messages = [];
    $messages[] = "Connected to database successfully.";
    
    // First, let's check if the games table exists and add missing columns
    try {
        // Check if system_requirements column exists
        $result = $db->query("SHOW COLUMNS FROM games LIKE 'system_requirements'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE games ADD COLUMN system_requirements VARCHAR(100) DEFAULT 'medium'");
            $messages[] = "✓ Added system_requirements column to games table";
        }
        
        // Check if game_setup_file column exists
        $result = $db->query("SHOW COLUMNS FROM games LIKE 'game_setup_file'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE games ADD COLUMN game_setup_file VARCHAR(500) NULL");
            $messages[] = "✓ Added game_setup_file column to games table";
        }
        
        // Check if discount_percentage column exists
        $result = $db->query("SHOW COLUMNS FROM games LIKE 'discount_percentage'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE games ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0");
            $messages[] = "✓ Added discount_percentage column to games table";
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            $messages[] = "ℹ Games table doesn't exist yet, will be created by main setup.sql";
        } else {
            $messages[] = "⚠ Warning updating games table: " . $e->getMessage();
        }
    }
    
    // Create game_files table
    try {
        $game_files_sql = "
        CREATE TABLE IF NOT EXISTS game_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_primary BOOLEAN DEFAULT TRUE,
            INDEX idx_game_id (game_id)
        )";
        
        $db->exec($game_files_sql);
        $messages[] = "✓ Created/verified game_files table";
        
    } catch (PDOException $e) {
        $messages[] = "⚠ Warning creating game_files table: " . $e->getMessage();
    }
    
    // Create admin_users table
    try {
        $admin_users_sql = "
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('admin', 'super_admin') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        )";
        
        $db->exec($admin_users_sql);
        $messages[] = "✓ Created/verified admin_users table";
        
        // Insert default admin user
        $check_admin = $db->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
        $check_admin->execute();
        
        if ($check_admin->fetchColumn() == 0) {
            $default_password = password_hash('admin123', PASSWORD_DEFAULT);
            $insert_admin = $db->prepare("
                INSERT INTO admin_users (username, email, password, full_name, role) 
                VALUES ('admin', 'admin@gamehub.com', ?, 'System Administrator', 'super_admin')
            ");
            $insert_admin->execute([$default_password]);
            $messages[] = "✓ Created default admin user (username: admin, password: admin123)";
        } else {
            $messages[] = "ℹ Default admin user already exists";
        }
        
    } catch (PDOException $e) {
        $messages[] = "⚠ Warning creating admin_users table: " . $e->getMessage();
    }
    
    // Create uploads directory
    $upload_dir = '../uploads/games/';
    if (!is_dir($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            $messages[] = "✓ Created uploads directory";
        } else {
            $messages[] = "⚠ Warning: Could not create uploads directory";
        }
    } else {
        $messages[] = "ℹ Uploads directory already exists";
    }
    
    // Add .htaccess for uploads security
    $htaccess_content = "# Protect uploaded files\n";
    $htaccess_content .= "Options -Indexes\n";
    $htaccess_content .= "# Prevent execution of scripts\n";
    $htaccess_content .= "<Files *.php>\n";
    $htaccess_content .= "    Deny from all\n";
    $htaccess_content .= "</Files>\n";
    
    if (!file_exists($upload_dir . '.htaccess')) {
        if (file_put_contents($upload_dir . '.htaccess', $htaccess_content)) {
            $messages[] = "✓ Created security .htaccess for uploads";
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => implode("\n", $messages)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database setup failed: ' . $e->getMessage()
    ]);
}
?>
