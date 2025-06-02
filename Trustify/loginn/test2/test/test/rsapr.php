<?php
require_once 'config.php';

// Initialize secure session
initializeSecureSession();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

try {
    // Get database connection
    $pdo = getDatabaseConnection();

    // Retrieve user's keys from database
    $stmt = $pdo->prepare("SELECT rsapubkey, rsaprvkey FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $keys = $stmt->fetch();

    $result = '';
    $publicKey = '';
    $privateKey = '';

    // Check if using saved or newly generated keys
    if (isset($_SESSION['saved_public_key'])) {
        $publicKey = $_SESSION['saved_public_key'];
        $privateKey = $_SESSION['saved_private_key'];
    } elseif ($keys) {
        $publicKey = $keys['rsapubkey'];
        $privateKey = $keys['rsaprvkey'];
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Generate keys
        if (isset($_POST['generate_keys'])) {
            $generatedKeys = generateRSAKeys($pdo, $_SESSION['user_id']);
            if ($generatedKeys) {
                $publicKey = $generatedKeys['publicKey'];
                $privateKey = $generatedKeys['privateKey'];
                $result = "New Keys Generated Successfully!";
            }
        }

        // Encryption
        if (isset($_POST['encrypt']) && $publicKey) {
            $dataToEncrypt = $_POST['data_to_encrypt'] ?? '';
            
            if ($dataToEncrypt) {
                $encrypted = safeEncrypt($dataToEncrypt, $publicKey);
                if ($encrypted) {
                    $result = "Encrypted Data: " . htmlspecialchars($encrypted);
                }
            }
        }

        // Decryption
        if (isset($_POST['decrypt']) && $privateKey) {
            $encryptedData = $_POST['encrypted_data'] ?? '';
            
            if ($encryptedData) {
                $decrypted = safeDecrypt($encryptedData, $privateKey);
                if ($decrypted) {
                    $result = "Decrypted Data: " . htmlspecialchars($decrypted);
                }
            }
        }
    }
} catch (Exception $e) {
    logError($e->getMessage());
    $result = "An error occurred. Please try again.";
}

// Safe RSA key generation function
function generateRSAKeys($pdo, $userId) {
    try {
        $config = [
            "digest_alg" => ENCRYPTION_DIGEST,
            "private_key_bits" => ENCRYPTION_KEY_BITS,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        ];

        // Generate the private key
        $privateKey = openssl_pkey_new($config);
        
        if ($privateKey === false) {
            throw new Exception('Failed to generate private key: ' . openssl_error_string());
        }

        // Export private key
        openssl_pkey_export($privateKey, $privateKeyOutput);
        
        // Extract the public key
        $publicKeyDetails = openssl_pkey_get_details($privateKey);
        $publicKey = $publicKeyDetails['key'];

        // Update database with new keys
        $stmt = $pdo->prepare("UPDATE users SET rsapubkey = ?, rsaprvkey = ? WHERE id = ?");
        $stmt->execute([$publicKey, $privateKeyOutput, $userId]);

        return [
            'publicKey' => $publicKey,
            'privateKey' => $privateKeyOutput
        ];

    } catch (Exception $e) {
        logError($e->getMessage());
        return false;
    }
}

// Safe encryption function
function safeEncrypt($data, $publicKey) {
    try {
        if (!$publicKey) {
            throw new Exception("No public key available");
        }
        
        $encrypted = '';
        $result = openssl_public_encrypt($data, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        
        if ($result === false) {
            throw new Exception("Encryption failed: " . openssl_error_string());
        }
        
        return base64_encode($encrypted);
    } catch (Exception $e) {
        logError($e->getMessage());
        return false;
    }
}

// Safe decryption function
function safeDecrypt($encryptedData, $privateKey) {
    try {
        if (!$privateKey) {
            throw new Exception("No private key available");
        }
        
        $decrypted = '';
        $result = openssl_private_decrypt(base64_decode($encryptedData), $decrypted, $privateKey, OPENSSL_PKCS1_PADDING);
        
        if ($result === false) {
            throw new Exception("Decryption failed: " . openssl_error_string());
        }
        
        return $decrypted;
    } catch (Exception $e) {
        logError($e->getMessage());
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSA Encryption/Decryption</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .result {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .logout-link {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>RSA Key Management</h2>
        
        <!-- Public Key Display -->
        <div>
            <h3>Your Public Key</h3>
            <textarea readonly rows="10"><?php echo htmlspecialchars($publicKey); ?></textarea>
        </div>