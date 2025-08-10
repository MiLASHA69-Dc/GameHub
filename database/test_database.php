<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();
    
    $tests = [];
    
    // Test 1: Check database connection
    $tests[] = [
        'name' => 'Database Connection',
        'status' => $db ? 'PASS' : 'FAIL',
        'message' => $db ? 'Connected successfully' : 'Connection failed'
    ];
    
    // Test 2: Check games table structure
    try {
        $result = $db->query("DESCRIBE games");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);
        $required_columns = ['id', 'title', 'description', 'price', 'category', 'platform', 'system_requirements', 'game_setup_file', 'discount_percentage'];
        
        $missing_columns = array_diff($required_columns, $columns);
        
        $tests[] = [
            'name' => 'Games Table Structure',
            'status' => empty($missing_columns) ? 'PASS' : 'FAIL',
            'message' => empty($missing_columns) ? 'All required columns present' : 'Missing columns: ' . implode(', ', $missing_columns)
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'Games Table Structure',
            'status' => 'FAIL',
            'message' => 'Table check failed: ' . $e->getMessage()
        ];
    }
    
    // Test 3: Check game_files table
    try {
        $result = $db->query("DESCRIBE game_files");
        $tests[] = [
            'name' => 'Game Files Table',
            'status' => 'PASS',
            'message' => 'Table exists and accessible'
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'Game Files Table',
            'status' => 'FAIL',
            'message' => 'Table not found: ' . $e->getMessage()
        ];
    }
    
    // Test 4: Check admin_users table
    try {
        $result = $db->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
        $admin_count = $result->fetchColumn();
        
        $tests[] = [
            'name' => 'Admin Users Table',
            'status' => $admin_count > 0 ? 'PASS' : 'FAIL',
            'message' => $admin_count > 0 ? 'Default admin user exists' : 'Default admin user not found'
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'Admin Users Table',
            'status' => 'FAIL',
            'message' => 'Table check failed: ' . $e->getMessage()
        ];
    }
    
    // Test 5: Check uploads directory
    $upload_dir = '../uploads/games/';
    $tests[] = [
        'name' => 'Uploads Directory',
        'status' => is_dir($upload_dir) && is_writable($upload_dir) ? 'PASS' : 'FAIL',
        'message' => is_dir($upload_dir) ? 
            (is_writable($upload_dir) ? 'Directory exists and writable' : 'Directory exists but not writable') : 
            'Directory does not exist'
    ];
    
    // Test 6: Test add_product.php endpoint
    $tests[] = [
        'name' => 'Add Product API',
        'status' => file_exists('../api/add_product.php') ? 'PASS' : 'FAIL',
        'message' => file_exists('../api/add_product.php') ? 'API file exists' : 'API file not found'
    ];
    
    $all_passed = array_reduce($tests, function($carry, $test) {
        return $carry && ($test['status'] === 'PASS');
    }, true);
    
    echo json_encode([
        'success' => $all_passed,
        'overall_status' => $all_passed ? 'ALL TESTS PASSED' : 'SOME TESTS FAILED',
        'tests' => $tests,
        'summary' => [
            'total' => count($tests),
            'passed' => count(array_filter($tests, function($t) { return $t['status'] === 'PASS'; })),
            'failed' => count(array_filter($tests, function($t) { return $t['status'] === 'FAIL'; }))
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Test suite failed: ' . $e->getMessage()
    ]);
}
?>
