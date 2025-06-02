CREATE DATABASE blockchain_service;

USE blockchain_service;

-- Table pour stocker les clés RSA
CREATE TABLE rsa_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    public_key TEXT NOT NULL,
    private_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour stocker les portefeuilles
CREATE TABLE wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_name VARCHAR(255) NOT NULL,
    wallet_address VARCHAR(255) NOT NULL,
    private_key VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table pour stocker les transactions signées
CREATE TABLE signed_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_data TEXT NOT NULL,
    signed_transaction TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_address VARCHAR(255) NOT NULL,
    to_address VARCHAR(255) NOT NULL,
    amount DECIMAL(18, 8) NOT NULL,  -- Le type DECIMAL est adapté pour stocker les montants
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
