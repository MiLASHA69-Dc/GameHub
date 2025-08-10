-- Add additional columns to games table to handle all form fields
USE video_game_storeg;

-- Add new columns to games table if they don't exist
ALTER TABLE games 
ADD COLUMN IF NOT EXISTS system_requirements VARCHAR(100) DEFAULT 'medium',
ADD COLUMN IF NOT EXISTS game_setup_file VARCHAR(500) NULL,
ADD COLUMN IF NOT EXISTS discount_percentage DECIMAL(5,2) DEFAULT 0;

-- Create game_files table to store setup files
CREATE TABLE IF NOT EXISTS game_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_primary BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Create admin users table for admin authentication
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
);

-- Insert default admin user (username: admin, password: admin123)
INSERT IGNORE INTO admin_users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@gamehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');
