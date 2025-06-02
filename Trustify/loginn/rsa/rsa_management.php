<?php
session_start();
require_once 'db_connection.php';
require_once 'db_operations.php';

// Initialize error message
$error_message = '';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    // Handle login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $db_ops = new DatabaseOperations($conn);
        if ($db_ops->verifyUser($email, $password)) {
            $_SESSION['email'] = $email;
        } else {
            $error_message = "Invalid credentials";
        }
    }
} else {
    // Handle key operations for logged-in user
    $db_ops = new DatabaseOperations($conn);
    $key_gen = new KeyGenerator();
    
    $keys = $db_ops->getUserKeys($_SESSION['email']);
    
    // Generate new keys if requested or if no keys exist
    if (!$keys || (isset($_POST['generate_new_keys']) && $_POST['generate_new_keys'] === '1')) {
        try {
            $new_keys = $key_gen->generateKeyPair();
            if ($db_ops->saveKeys($_SESSION['email'], $new_keys['private_key'], $new_keys['public_key'])) {
                $keys = $new_keys;
            } else {
                $error_message = "Failed to save new keys";
            }
        } catch (Exception $e) {
            $error_message = "Key generation failed: " . $e->getMessage();
        }
    }
    
    // Make keys available to the view
    $public_key = $keys['rsapubkey'] ?? '';
    $private_key = $keys['rsaprvkey'] ?? '';
}
?>