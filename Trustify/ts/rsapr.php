<?php
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? '';

    try {
        if ($operation === 'encrypt') {
            // Encryption Process
            $data = $_POST['data'] ?? '';

            if (empty($data)) {
                throw new Exception("Please enter data to encrypt.");
            }

            // Generate RSA key pair
            $keyConfig = [
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
            $keyPair = openssl_pkey_new($keyConfig);

            // Extract the private and public keys
            openssl_pkey_export($keyPair, $privateKey);
            $publicKey = openssl_pkey_get_details($keyPair)['key'];

            // Encrypt the data
            openssl_public_encrypt($data, $encryptedData, $publicKey);

            if (!$encryptedData) {
                throw new Exception("Encryption failed.");
            }

            // Base64 encode the encrypted data for easy display
            $encryptedData = base64_encode($encryptedData);

            $result = "Encrypted Data: " . htmlspecialchars($encryptedData) . "\n";
            $result .= "Public Key: " . htmlspecialchars($publicKey) . "\n";
            $result .= "Private Key: " . htmlspecialchars($privateKey);
        } elseif ($operation === 'decrypt') {
            // Decryption Process
            $encryptedData = $_POST['encrypted_data'] ?? '';
            $privateKey = $_POST['private_key'] ?? '';

            if (empty($encryptedData) || empty($privateKey)) {
                throw new Exception("Please provide the encrypted data and private key for decryption.");
            }

            // Decrypt the data
            openssl_private_decrypt(base64_decode($encryptedData), $decryptedData, $privateKey);

            if (!$decryptedData) {
                throw new Exception("Decryption failed. Please check the private key.");
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
    <title>RSA Result</title>
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
    <a href="rsa.html">Back to Form</a>
</body>
</html>