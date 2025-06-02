CREATE DATABASE IF NOT EXISTS rsa_db;
USE rsa_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rsapubkey TEXT,
    rsaprvkey TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create a test user (password is 'test123')
INSERT INTO users (email, password) VALUES 
('bachirsoukaina03@gmail.com', 'soukaina');