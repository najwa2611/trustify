<?php
// Database configuration
$host = "localhost";     // Database host (usually localhost)
$username = "root";      // Database username (default for XAMPP is "root")
$password = "";          // Database password (default is an empty string for XAMPP)
$database = "test";      // Database name

try {
    // Create a new MySQL connection using PDO
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    
    // Set PDO error mode to exception for better error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to create the table
    $sql = "
        CREATE TABLE IF NOT EXISTS rsa_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            rsa_private_key TEXT NOT NULL,
            rsa_public_key TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";

    // Execute the query
    $conn->exec($sql);

    echo "Table 'rsa_users' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}

// Close the connection
$conn = null<?php
// Database configuration
$host = "localhost";     // Database host (usually localhost)
$username = "root";      // Database username (default for XAMPP is "root")
$password = "";          // Database password (default is an empty string for XAMPP)
$database = "test";      // Database name

try {
    // Create a new MySQL connection using PDO
    $conn = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    
    // Set PDO error mode to exception for better error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to create the table
    $sql = "
        CREATE TABLE IF NOT EXISTS rsa_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            rsa_private_key TEXT NOT NULL,
            rsa_public_key TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";

    // Execute the query
    $conn->exec($sql);

    echo "Table 'rsa_users' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}

// Close the connection
$conn = null;
?>

