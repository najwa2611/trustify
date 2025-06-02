<?php
$host = 'localhost'; // Database host
$dbname = 'trustify'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password (default for MySQL root is an empty string)

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
