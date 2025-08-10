<?php
// Helper functions for authentication

function isLoggedIn() {
    session_start();
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    session_start();
    if(isset($_SESSION['user_id'])) {
        return array(
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'country' => $_SESSION['user_country']
        );
    }
    return null;
}

function requireLogin() {
    if(!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Authentication required."
        ));
        exit();
    }
}
?>
