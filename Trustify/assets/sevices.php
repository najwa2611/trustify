<?php
header('Content-Type: application/json');

// Decode the incoming JSON request
$data = json_decode(file_get_contents('php://input'), true);
$service = $data['service'] ?? null;

// Handle services
switch ($service) {
    case 'encrypt':
        handleEncryption($data);
        break;
    case 'hash':
        handleHashing($data);
        break;
    case 'sign':
        handleSigning($data);
        break;
    case 'decrypt':
        handleDecryption($data);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid service requested.']);
        break;
}

// Encryption function
function handleEncryption($data) {
    $text = $data['text'] ?? '';
    if (empty($text)) {
        echo json_encode(['error' => 'Text for encryption is required.']);
        return;
    }

    $key = "secret_key";
    $cipher = "AES-128-CTR";
    $iv = random_bytes(openssl_cipher_iv_length($cipher));
    $options = 0;

    $encryptedText = openssl_encrypt($text, $cipher, $key, $options, $iv);

    echo json_encode([
        'encryptedText' => $encryptedText,
        'iv' => base64_encode($iv) // Send IV if decryption will be needed
    ]);
}

// Hashing function
function handleHashing($data) {
    $text = $data['text'] ?? '';
    if (empty($text)) {
        echo json_encode(['error' => 'Text for hashing is required.']);
        return;
    }

    $hash = hash('sha256', $text);

    echo json_encode(['hash' => $hash]);
}

// Certificate signing function
function handleSigning($data) {
    $details = $data['details'] ?? '';
    if (empty($details)) {
        echo json_encode(['error' => 'Details for signing are required.']);
        return;
    }

    // Placeholder signing logic
    $signedCert = "Signed certificate for: " . $details;

    echo json_encode(['signedCert' => $signedCert]);
}

// Decryption function
function handleDecryption($data) {
    $encryptedText = $data['encryptedText'] ?? '';
    $iv = base64_decode($data['iv'] ?? '');

    if (empty($encryptedText) || empty($iv)) {
        echo json_encode(['error' => 'Encrypted text and IV are required.']);
        return;
    }

    $key = "secret_key";  // This should be the same key used for encryption
    $cipher = "AES-128-CTR";
    $options = 0;

    $decryptedText = openssl_decrypt($encryptedText, $cipher, $key, $options, $iv);

    if ($decryptedText === false) {
        echo json_encode(['error' => 'Decryption failed.']);
    } else {
        echo json_encode(['decryptedText' => $decryptedText]);
    }
}
?>