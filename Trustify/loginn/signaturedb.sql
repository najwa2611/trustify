-- Créer la base de données si elle n'existe pas déjà
CREATE DATABASE IF NOT EXISTS rsa_signature_service;

-- Utiliser la base de données
USE rsa_signature_service;

-- Table pour stocker les informations des utilisateurs et leurs clés
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rsa_public_key TEXT NOT NULL,
    rsa_private_key TEXT NOT NULL
);

-- Ajouter un utilisateur avec un mot de passe haché
-- Exemple d'utilisateur
SET @password = 'votre_mot_de_passe';  -- Changez cela par un mot de passe sécurisé
SET @hashed_password = PASSWORD(@password);

INSERT INTO users (email, password) 
VALUES ('test@example.com', @hashed_password);
