<?php
// Database Connection Configuration for XAMPP
$host = 'localhost';     // Database host
$username = 'root';      // Default XAMPP MySQL username
$password = '';          // Default XAMPP MySQL password (empty string)
$database = 'test';      // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_errno) {
    // Detailed error logging
    error_log("MySQL Connection Failed: (" . $conn->connect_errno . ") " . $conn->connect_error);
    die("Database connection failed. Please check the logs.");
}

// Optional: Set character set to UTF-8
$conn->set_charset("utf8mb4");