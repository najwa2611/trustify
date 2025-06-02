CREATE DATABASE IF NOT EXISTS rsa_db;
USE rsa_db;

CREATE TABLE users  (
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rsapubkey` text NOT NULL,
  `rsaprvkey` text NOT NULL,
  `data` mediumblob NOT NULL,
  `filename` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Create a test user (password is 'test123')
INSERT INTO users (email, password) VALUES 
('bachirsoukaina03@gmail.com', 'soukaina');