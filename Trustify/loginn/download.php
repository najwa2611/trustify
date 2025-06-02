<?php
session_start();
// Database configuration
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "rsa_db";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to verify user credentials
function verifyUser($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    
    if ($stmt->fetch() && $password === $stored_password) {  // Direct password comparison
        $stmt->close();
        return true;
    }
    
    $stmt->close();
    return false;
}

if (isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $password = $_GET['password'];
    
    if (verifyUser($email, $password)) {
        // Retrieve file data including MIME type
        $stmt = $conn->prepare("SELECT filename, data, mime_type FROM users WHERE email = ? AND filename IS NOT NULL");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($filename, $data, $mime_type);
            $stmt->fetch();
            
            // Set headers for file download
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($data));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            echo $data;
        } else {
            echo "File not found!";
        }
        
        $stmt->close();
    } else {
        echo "Invalid email or password!";
    }
} else {
    echo "Email and password are required!";
}

$conn->close();
?>