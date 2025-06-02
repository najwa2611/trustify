<?php
// RSA Encryption and Decryption Functions

function encrypt_data($data, $public_key) {
    // Encrypt data using public key
    $encrypted = '';
    $result = openssl_public_encrypt($data, $encrypted, $public_key, OPENSSL_PKCS1_PADDING);
    
    if ($result === false) {
        throw new Exception("Encryption failed: " . openssl_error_string());
    }
    
    return $encrypted;
}

function decrypt_data($encrypted_data, $private_key) {
    // Ensure the private key has the correct format
    if (strpos($private_key, '-----BEGIN PRIVATE KEY-----') === false) {
        $private_key = "-----BEGIN PRIVATE KEY-----\n" . $private_key . "\n-----END PRIVATE KEY-----";
    }

    // Decrypt data using private key
    $decrypted = '';
    $result = openssl_private_decrypt($encrypted_data, $decrypted, $private_key, OPENSSL_PKCS1_PADDING);
    
    if ($result === false) {
        throw new Exception("Decryption failed: " . openssl_error_string());
    }
    
    return $decrypted;
}