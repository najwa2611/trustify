CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rsa_public_key TEXT NOT NULL,
    rsa_private_key TEXT NOT NULL
);
