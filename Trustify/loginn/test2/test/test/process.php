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
        :root {
            /* Color Palette */
            --primary-bg-color: linear-gradient(to right, #4b0082, #8a2be2); /* Deep purple gradient */
            --primary-text-color: #ecf0f1; /* Light gray */
            --accent-color: #800080; /* Purple */
            --accent-hover-color: #551a8b; /* Darker purple */
            --secondary-bg-color: rgba(255, 255, 255, 0.1); /* Semi-transparent white */

            /* Typography */
            --font-family: 'Roboto', 'Arial', sans-serif;
            --header-font-family: 'Montserrat', 'Arial', sans-serif;

            /* Layout */
            --header-height: 65px;
            --border-radius: 6px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background: var(--primary-bg-color);
            color: var(--primary-text-color);
            line-height: 1.6;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            color: var(--accent-color);
            margin-bottom: 30px;
            font-family: var(--header-font-family);
        }

        .content {
            padding: 20px;
            width: 100%;
            max-width: 800px;
            background-color: var(--secondary-bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        pre {
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--accent-color);
            border-radius: var(--border-radius);
            color: var(--primary-text-color);
            white-space: pre-wrap; /* Ensures text wraps within the pre element */
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: var(--accent-color);
            transition: color 0.3s ease;
        }

        a:hover {
            text-decoration: underline;
            color: var(--accent-hover-color);
        }

        /* Responsive Design */
        @media screen and (max-width: 600px) {
            .content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <h1>AES Result</h1>

    <div class="content">
        <!-- Display the result of encryption/decryption -->
        <pre><?php echo $result; ?></pre>

        <!-- Link to go back to the form -->
        <a href="aes.html">Back to Form</a>
    </div>
</body>
</html>
