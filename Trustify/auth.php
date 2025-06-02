<?php
session_start();
require 'config.php'; // Database configuration

function login($email, $password) {
    global $conn;
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            header("Location: index11(1).html");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
    $stmt->close();
}
function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$email = validateInput($_POST['email']);
$password = validateInput($_POST['password']);
function logEvent($message) {
    $logfile = 'logs/security.log';
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

logEvent("User login attempt: " . $email);

function signup($email, $password) {
    global $conn;
    $check_email_sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Error: Email is already registered.";
        header("refresh:3; url=auth.html");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        $to = $email;
        $subject = "Thank You for Signing Up";
        $message = "Dear " . $email . ",\n\nThank you for signing up to Trustify. We appreciate your support.\n\nBest regards,\nTrustify Team";
        $headers = "From: no-reply@trustify.com\r\n";
        $headers .= "Reply-To: support@trustify.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo "Registration successful! A thank-you email has been sent.";
        } else {
            echo "Registration successful, but failed to send thank-you email.";
        }

        header("refresh:3; url=auth.html");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        login($_POST['email'], $_POST['password']);
    } elseif (isset($_POST['signup'])) {
        signup($_POST['email'], $_POST['password']);
    }
}
?>
<?php
session_start();
require 'config.php'; // Include the database configuration

// Your existing code here
?>
