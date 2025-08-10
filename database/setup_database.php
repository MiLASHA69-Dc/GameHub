#!/usr/bin/env php
<?php
/**
 * Database Setup Script
 * Run this script to set up the database with all required tables and columns
 */

require_once '../config/database.php';

echo "Starting database setup...\n";

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Connected to database successfully.\n";
    
    // Read and execute the extended games setup SQL
    $sql_file = '../sql/create_games_extended.sql';
    
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql_content)),
            function($statement) {
                return !empty($statement) && !preg_match('/^\s*--/', $statement);
            }
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $db->exec($statement);
                    echo "✓ Executed SQL statement successfully\n";
                } catch (PDOException $e) {
                    // Ignore errors for statements that already exist
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'Duplicate') === false) {
                        echo "⚠ Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "\n✓ Database setup completed successfully!\n";
        echo "\nYour database now includes:\n";
        echo "- Updated games table with all required fields\n";
        echo "- game_files table for storing setup files\n";
        echo "- admin_users table for admin authentication\n";
        echo "- Default admin user (username: admin, password: admin123)\n";
        
    } else {
        echo "❌ Error: SQL file not found at $sql_file\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
