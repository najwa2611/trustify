-- SQLBook: Code
-- Create the database
CREATE DATABASE IF NOT EXISTS trustify;

-- Use the created database
USE trustify;

-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example Insert (this is just for testing, don't use real passwords in production)
-- Note: Make sure to use the hashed password (e.g., using PHP's password_hash function) for production
INSERT INTO users (email, password) VALUES 
('testuser@example.com', '$2y$10$X6q6tLnP59b3y0NjM7ZTze40eEzFF2JbNl77VmpgHLr6z.YjswMu6');  -- password: 'password123'
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
