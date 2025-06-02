<?php
// Security and Configuration Settings

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'test');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security Settings
define('ENCRYPTION_KEY_BITS', 2048);
define('ENCRYPTION_DIGEST', 'sha512');

// Logging function
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'application_errors.log');
}

// Secure database connection
function getDatabaseConnection() {
    try {
        // Verify OpenSSL Extension
        if (!extension_loaded('openssl')) {
            throw new Exception('OpenSSL extension is not loaded.');
        }

        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        return $pdo;
    } catch (PDOException $e) {
        logError('Database Connection Failed: ' . $e->getMessage());
        die("A system error occurred. Please try again later.");
    } catch (Exception $e) {
        logError($e->getMessage());
        die("A configuration error occurred. Please contact support.");
    }
}

// Secure session initialization
function initializeSecureSession() {
    // Prevent session hijacking
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', 1); // Only works over HTTPS

    // Start session
    session_start();

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
}
?>