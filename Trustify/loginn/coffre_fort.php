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

// Function to verify email and password
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload']) && isset($_FILES['file'])) {
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];  // Plain password
        
        if (verifyUser($email, $password)) {
            // Get file information
            $file_data = file_get_contents($_FILES['file']['tmp_name']);
            $filename = $_FILES['file']['name'];
            $mime_type = $_FILES['file']['type'];
            
            // If MIME type is empty, try to detect it
            if (empty($mime_type)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $_FILES['file']['tmp_name']);
                finfo_close($finfo);
            }
            
            // Insert file data into the database
            $stmt = $conn->prepare("UPDATE users SET data = ?, filename = ?, mime_type = ? WHERE email = ?");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            
            $null = NULL;
            $stmt->bind_param("bsss", $null, $filename, $mime_type, $email);
            $stmt->send_long_data(0, $file_data);
            
            if ($stmt->execute()) {
                echo "File successfully uploaded.";
            } else {
                echo "File upload failed: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            echo "Invalid email or password!";
        }
    } elseif (isset($_POST['list_files'])) {
        $email = htmlspecialchars($_POST['email']);
        $password = $_POST['password'];  // Plain password
        
        if (verifyUser($email, $password)) {
            // List files for the user
            $stmt = $conn->prepare("SELECT filename, mime_type FROM users WHERE email = ? AND filename IS NOT NULL");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($filename, $mime_type);
            
            echo "<h2>Your Files:</h2><ul>";
            while ($stmt->fetch()) {
                // Pass the email as a parameter for download identification
                echo "<li><a href='download.php?email=" . urlencode($email) . "'>" . 
                     htmlspecialchars($filename) . "</a> (" . htmlspecialchars($mime_type) . ")</li>";
            }
            echo "</ul>";
            $stmt->close();
        } else {
            echo "Invalid email or password!";
        }
    }
} else {
    header('Location: coffre_fort.html');
    exit;
}

$conn->close();
?>
