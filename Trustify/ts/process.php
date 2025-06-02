<?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? '';

    // AES Cipher configuration
    $cipher = "aes-256-cbc"; // AES cipher
    $ivLength = openssl_cipher_iv_length($cipher); // IV length for the selected cipher

    try {
        if ($operation === 'encrypt') {
            // Encryption Process
            $data = $_POST['data'] ?? '';

            if (empty($data)) {
                throw new Exception("Please enter data to encrypt.");
            }

            // Generate a random key (32 characters for AES-256)
            $key = bin2hex(random_bytes(16)); // 32-character hex key (128-bit)
            $iv = bin2hex(random_bytes($ivLength)); // Generate a random IV

            // Encrypt the data
            $encryptedData = openssl_encrypt($data, $cipher, $key, 0, hex2bin($iv));

            if (!$encryptedData) {
                throw new Exception("Encryption failed.");
            }

            // Show the encrypted data and key used
            $result = "Encrypted Data: " . htmlspecialchars($encryptedData) . "\n";
            $result .= "Key Used: " . htmlspecialchars($key) . "\n";
            $result .= "IV Used: " . htmlspecialchars($iv);

        } elseif ($operation === 'decrypt') {
            // Decryption Process
            $encryptedData = $_POST['encrypted_data'] ?? '';
            $key = $_POST['key'] ?? '';
            $iv = $_POST['iv'] ?? '';

            if (empty($encryptedData) || empty($key) || empty($iv)) {
                throw new Exception("Please provide the encrypted data, key, and IV for decryption.");
            }

            // Decrypt the data
            $decryptedData = openssl_decrypt($encryptedData, $cipher, $key, 0, hex2bin($iv));

            if (!$decryptedData) {
                throw new Exception("Decryption failed. Please check the key or IV.");
            }

            $result = "Decrypted Data: " . htmlspecialchars($decryptedData);
        } else {
            throw new Exception("Invalid operation selected.");
        }
    } catch (Exception $e) {
        $result = "Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    $result = "Invalid request method.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AES Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        pre {
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Result</h1>

    <!-- Display the result of encryption/decryption -->
    <pre><?php echo $result; ?></pre>

    <!-- Link to go back to the form -->
    <a href="index.html">Back to Form</a>
</body>
</html>