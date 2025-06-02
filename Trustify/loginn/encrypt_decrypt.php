<?php
session_start();
require_once 'db_connection.php';
require_once 'rsa_functions.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$public_key = '';
$private_key = '';
$encrypted_data = '';
$decrypted_data = '';
$error_message = '';

// Function to generate and save new keys
function generateAndSaveKeys($conn, $email) {
    $config = [
        "digest_alg" => "sha512",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
        "config" => 'C:\xampp\apache\conf\openssl.cnf'
    ];

    // Generate the private key
    $res = @openssl_pkey_new($config);
    
    if ($res === false) {
        throw new Exception('Failed to generate new OpenSSL key pair: ' . openssl_error_string());
    }

    // Export private key
    openssl_pkey_export($res, $private_key, null, $config);

    // Extract the public key
    $pubKey = openssl_pkey_get_details($res);
    if (!$pubKey) {
        throw new Exception('Failed to retrieve public key details: ' . openssl_error_string());
    }

    $public_key = $pubKey['key'];

    // Delete existing keys for this user before inserting new ones
    $delete_stmt = $conn->prepare("DELETE FROM db WHERE email = ?");
    $delete_stmt->bind_param("s", $email);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Save new keys to database
    $stmt = $conn->prepare("INSERT INTO db (email, rsaprvkey, rsapubkey) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $private_key, $public_key);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to save keys to database: " . $stmt->error);
    }
    $stmt->close();

    return ['public_key' => $public_key, 'private_key' => $private_key];
}

// Check if user wants to generate new keys
$generate_new_keys = isset($_POST['generate_new_keys']);

// Retrieve or generate keys
try {
    // Check if keys exist
    $stmt = $conn->prepare("SELECT rsaprvkey, rsapubkey FROM db WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0 && !$generate_new_keys) {
        // Keys exist and no new key generation requested
        $keys = $result->fetch_assoc();
        $public_key = $keys['rsapubkey'];
        $private_key = $keys['rsaprvkey'];
    } else {
        // Generate new keys
        $new_keys = generateAndSaveKeys($conn, $email);
        $public_key = $new_keys['public_key'];
        $private_key = $new_keys['private_key'];
    }
    $stmt->close();

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// Rest of your existing encryption/decryption handling code
// Handle encryption
if (isset($_POST['encrypt'])) {
    $data_to_encrypt = $_POST['data_to_encrypt'] ?? '';

    try {
        if (strlen($data_to_encrypt) > 245) { // Check for data size limit
            throw new Exception('Data too large for RSA encryption. Please use a smaller message.');
        }

        $encrypted = '';
        $public_key_resource = openssl_pkey_get_public($public_key);

        if ($public_key_resource === false) {
            throw new Exception('Unable to load public key: ' . openssl_error_string());
        }

        $result = openssl_public_encrypt($data_to_encrypt, $encrypted, $public_key_resource, OPENSSL_PKCS1_PADDING);

        if ($result === false) {
            throw new Exception('Encryption failed: ' . openssl_error_string());
        }

        $encrypted_data = base64_encode($encrypted);
    } catch (Exception $e) {
        $error_message = "Encryption failed: " . $e->getMessage();
    }
}

// Handle decryption
if (isset($_POST['decrypt'])) {
    $encrypted_input = $_POST['encrypted_data'] ?? '';

    try {
        if (empty($encrypted_input) || empty($private_key)) {
            throw new Exception('Decryption data or key is empty');
        }

        if (strpos($private_key, '-----BEGIN PRIVATE KEY-----') === false) {
            $private_key = "-----BEGIN PRIVATE KEY-----\n" . $private_key . "\n-----END PRIVATE KEY-----";
        }

        $decoded_data = base64_decode($encrypted_input);
        if ($decoded_data === false) {
            throw new Exception('Base64 decoding failed.');
        }

        $private_key_resource = openssl_pkey_get_private($private_key);
        if ($private_key_resource === false) {
            throw new Exception('Unable to load private key: ' . openssl_error_string());
        }

        $decrypted_data = '';
        $result = openssl_private_decrypt($decoded_data, $decrypted_data, $private_key_resource, OPENSSL_PKCS1_PADDING);

        if ($result === false) {
            throw new Exception('Decryption failed: ' . openssl_error_string());
        }

    } catch (Exception $e) {
        $error_message = "Decryption failed: " . $e->getMessage();
    }
}
?>

<!-- In your HTML form, add a button to generate new keys -->
<form method="post">
    <input type="submit" name="generate_new_keys" value="Generate New Keys">
</form>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSA Encryption/Decryption</title>
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

        .key-section {
            background-color: var(--secondary-bg-color);
            padding: 10px;
            margin-bottom: 20px;
            word-wrap: break-word;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .form-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .form-section {
            width: 48%;
            background-color: var(--secondary-bg-color);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
        }

        textarea, select, input, button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid var(--accent-color);
            border-radius: var(--border-radius);
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--primary-text-color);
            transition: border-color 0.3s ease;
        }

        textarea:focus, select:focus, input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 5px rgba(128, 0, 128, 0.5);
        }

        button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: var(--accent-hover-color);
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media screen and (max-width: 600px) {
            .form-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>RSA Encryption and Decryption</h1>

    <div class="content">
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="key-section">
            <h2>Your Public Key</h2>
            <textarea rows="10" readonly><?php echo htmlspecialchars($public_key); ?></textarea>
        </div>

        <div class="form-container">
            <div class="form-section">
                <h2>Encrypt Data</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <textarea name="data_to_encrypt" rows="4" placeholder="Enter text to encrypt..." required><?php 
                        echo htmlspecialchars($data_to_encrypt ?? ''); 
                    ?></textarea>
                    <button type="submit" name="encrypt">Encrypt</button>
                </form>

                <?php if (!empty($encrypted_data)): ?>
                    <h3>Encrypted Data</h3>
                    <textarea rows="4" readonly><?php echo htmlspecialchars($encrypted_data); ?></textarea>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <h2>Decrypt Data</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <textarea name="encrypted_data" rows="4" placeholder="Enter encrypted data..." required><?php 
                        echo htmlspecialchars($encrypted_input ?? ''); 
                    ?></textarea>
                    <button type="submit" name="decrypt">Decrypt</button>
                </form>

                <?php if (!empty($decrypted_data)): ?>
                    <h3>Decrypted Data</h3>
                    <textarea rows="4" readonly><?php echo htmlspecialchars($decrypted_data); ?></textarea>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
