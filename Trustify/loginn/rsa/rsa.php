<?php
session_start();
require_once 'db_connection.php';

// Initialize variables
$error_message = '';
$public_key = '';
$private_key = '';
$encrypted_data = '';
$decrypted_data = '';

// Handle login
if (!isset($_SESSION['email'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['email'] = $email;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Invalid credentials";
        }
        $stmt->close();
    }
} else {
    // User is logged in, handle RSA operations
    $email = $_SESSION['email'];
    
    // Function to generate and save new keys
    function generateAndSaveKeys($conn, $email) {
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        if ($res === false) {
            throw new Exception('Failed to generate new OpenSSL key pair: ' . openssl_error_string());
        }

        openssl_pkey_export($res, $privateKey);
        $pubKey = openssl_pkey_get_details($res);
        $publicKey = $pubKey['key'];

        $stmt = $conn->prepare("UPDATE users SET rsaprvkey = ?, rsapubkey = ? WHERE email = ?");
        $stmt->bind_param("sss", $privateKey, $publicKey, $email);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save keys to database: " . $stmt->error);
        }
        $stmt->close();

        return ['public_key' => $publicKey, 'private_key' => $privateKey];
    }

    // Get or generate keys
    $stmt = $conn->prepare("SELECT rsaprvkey, rsapubkey FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || empty($user['rsapubkey']) || isset($_POST['generate_new_keys'])) {
        try {
            $keys = generateAndSaveKeys($conn, $email);
            $public_key = $keys['public_key'];
            $private_key = $keys['private_key'];
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    } else {
        $public_key = $user['rsapubkey'];
        $private_key = $user['rsaprvkey'];
    }

    // Handle encryption
    if (isset($_POST['encrypt'])) {
        $data = $_POST['data_to_encrypt'] ?? '';
        try {
            if (openssl_public_encrypt($data, $encrypted, $public_key)) {
                $encrypted_data = base64_encode($encrypted);
            } else {
                throw new Exception("Encryption failed");
            }
        } catch (Exception $e) {
            $error_message = "Encryption error: " . $e->getMessage();
        }
    }

    // Handle decryption
    if (isset($_POST['decrypt'])) {
        $data = base64_decode($_POST['encrypted_data'] ?? '');
        try {
            if (openssl_private_decrypt($data, $decrypted, $private_key)) {
                $decrypted_data = $decrypted;
            } else {
                throw new Exception("Decryption failed");
            }
        } catch (Exception $e) {
            $error_message = "Decryption error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSA Key Management</title>
    <style>
        :root {
            --primary-color: #4b0082;
            --secondary-color: #8a2be2;
            --text-color: #ffffff;
            --error-color: #ff3333;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        h1, h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-color);
            margin-bottom: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: var(--primary-color);
            border: none;
            border-radius: 5px;
            color: var(--text-color);
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 10px;
        }

        button:hover {
            background: var(--secondary-color);
        }

        .error {
            color: var(--error-color);
            text-align: center;
            margin-bottom: 20px;
        }

        .crypto-section {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .crypto-form {
            flex: 1;
            min-width: 300px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>RSA Key Management</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['email'])): ?>
            <!-- Login Form -->
            <form method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login">Login</button>
            </form>
        <?php else: ?>
            <!-- Key Management and Crypto Operations -->
            <div class="user-info">
                <p>Logged in as: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <form method="post" action="logout.php">
                    <button type="submit">Logout</button>
                </form>
            </div>

            <form method="post">
                <div class="form-group">
                    <button type="submit" name="generate_new_keys">Generate New Keys</button>
                </div>
            </form>

            <div class="form-group">
                <h3>Your Public Key</h3>
                <textarea readonly rows="5"><?php echo htmlspecialchars($public_key); ?></textarea>
            </div>

            <div class="crypto-section">
                <div class="crypto-form">
                    <h3>Encrypt Data</h3>
                    <form method="post">
                        <div class="form-group">
                            <textarea name="data_to_encrypt" placeholder="Enter text to encrypt..." required></textarea>
                        </div>
                        <button type="submit" name="encrypt">Encrypt</button>
                    </form>
                    <?php if (!empty($encrypted_data)): ?>
                        <textarea readonly><?php echo htmlspecialchars($encrypted_data); ?></textarea>
                    <?php endif; ?>
                </div>

                <div class="crypto-form">
                    <h3>Decrypt Data</h3>
                    <form method="post">
                        <div class="form-group">
                            <textarea name="encrypted_data" placeholder="Enter encrypted data..." required></textarea>
                        </div>
                        <button type="submit" name="decrypt">Decrypt</button>
                    </form>
                    <?php if (!empty($decrypted_data)): ?>
                        <textarea readonly><?php echo htmlspecialchars($decrypted_data); ?></textarea>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>