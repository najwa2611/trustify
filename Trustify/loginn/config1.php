<?php
$host = 'localhost';  // Adresse de votre serveur MySQL
$dbname = 'blockchain_service';  // Nom de la base de données
$username = 'root';  // Votre nom d'utilisateur MySQL
$password = '';  // Votre mot de passe MySQL, souvent vide pour XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
