<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'rsa_db';  // Make sure this database exists

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>