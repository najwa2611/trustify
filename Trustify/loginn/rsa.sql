-- 1. Créer la base de données
CREATE DATABASE rsa_encryption_service;

-- 2. Utiliser la base de données
USE rsa_encryption_service;

-- 3. Créer la table `users`
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rsa_public_key TEXT NOT NULL,
    rsa_private_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Ajouter un utilisateur d'exemple (remplacez les valeurs par de vraies données)
INSERT INTO users (email, password, rsa_public_key, rsa_private_key)
VALUES 
    ('user@example.com', 
    'hashed_password_example', 
    '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7HwiStOw79fJVR5q6hVZ\n...Q==\n-----END PUBLIC KEY-----', 
    '-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCAT4wggE0AgEAAkEAhkMjDFmGT9PqqpA\n...B4==\n-----END PRIVATE KEY-----');
