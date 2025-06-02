<?php
// Check if the encrypted data was sent from the frontend
if (isset($_POST['encrypted'])) {
    // Get the encrypted data from the POST request
    $encrypted = $_POST['encrypted'];
    
    // Decode the encrypted data from JSON format
    $encryptedData = json_decode($encrypted, true);
    
    // Load the private key for decryption
    $privateKey = openssl_pkey_get_private("file:///path/to/private/key.pem");  // Replace with the actual path to your private key
    
    if ($privateKey === false) {
        die("Failed to load private key!");
    }
    
    // Decrypt the message using the private key
    $decryptedMessage = null;
    $success = openssl_private_decrypt(hex2bin($encryptedData['data']), $decryptedMessage, $privateKey);
    
    // Return the decrypted message or an error
    if ($success) {
        echo "Decrypted Message: " . $decryptedMessage;
    } else {
        echo "Decryption failed!";
    }
} else {
    echo "No encrypted data received!";
}
?>
