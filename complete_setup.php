<?php
require_once 'config/database.php';

echo "Setting up complete database structure...\n";

try {
    // First connect without database name to create it
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS video_game_storeg");
    echo "✓ Database 'video_game_storeg' created/verified\n";
    
    // Now connect to the specific database
    $database = new Database();
    $db = $database->getConnection();
    
    // Create all tables from setup.sql
    $sql_content = file_get_contents('database/setup.sql');
    
    // Execute the setup SQL
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    foreach($statements as $stmt) {
        if(!empty($stmt) && !preg_match('/^\s*--/', $stmt)) {
            try {
                $db->exec($stmt);
                // Extract table name for display
                if(preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $stmt, $matches)) {
                    echo "✓ Created table: " . $matches[1] . "\n";
                }
            } catch(Exception $e) {
                if(strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠ Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    // Add our custom columns to games table
    $columns_to_add = [
        'system_requirements' => "VARCHAR(100) DEFAULT 'medium'",
        'game_setup_file' => "VARCHAR(500) NULL",
        'discount_percentage' => "DECIMAL(5,2) DEFAULT 0"
    ];
    
    echo "\nAdding custom columns to games table:\n";
    foreach($columns_to_add as $column => $definition) {
        try {
            $db->exec("ALTER TABLE games ADD COLUMN $column $definition");
            echo "✓ Added column: $column\n";
        } catch(Exception $e) {
            if(strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "ℹ Column $column already exists\n";
            } else {
                echo "⚠ Error adding $column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Create game_files table
    echo "\nCreating game_files table:\n";
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
    
    try {
        $db->exec($game_files_sql);
        echo "✓ Created game_files table\n";
    } catch(Exception $e) {
        echo "⚠ Error creating game_files: " . $e->getMessage() . "\n";
    }
    
    // Create admin_users table
    echo "\nCreating admin_users table:\n";
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
    
    try {
        $db->exec($admin_users_sql);
        echo "✓ Created admin_users table\n";
        
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
            echo "✓ Created default admin user (username: admin, password: admin123)\n";
        } else {
            echo "ℹ Default admin user already exists\n";
        }
        
    } catch(Exception $e) {
        echo "⚠ Error with admin_users: " . $e->getMessage() . "\n";
    }
    
    // Create uploads directory
    $upload_dir = 'uploads/games/';
    if (!is_dir($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            echo "✓ Created uploads directory\n";
        } else {
            echo "⚠ Warning: Could not create uploads directory\n";
        }
    } else {
        echo "ℹ Uploads directory already exists\n";
    }
    
    echo "\n🎉 Complete database setup finished successfully!\n";
    echo "\nYou can now:\n";
    echo "- Use the admin dashboard to add games\n";
    echo "- Login with: username=admin, password=admin123\n";
    echo "- Upload game setup files up to 500MB\n";
    
} catch(Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
}
?>
