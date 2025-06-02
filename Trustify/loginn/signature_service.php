<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'generate_keys':
            generateKeys();
            break;
        case 'sign_message':
            signMessage();
            break;
        case 'sign_file':
            signFile();
            break;
        case 'verify_signature':
            verifySignature();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
}

function generateKeys() {
    $res = openssl_pkey_new([
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    openssl_pkey_export($res, $privateKey);
    $publicKey = openssl_pkey_get_details($res)['key'];

    echo "<pre>Private Key:\n$privateKey\n\nPublic Key:\n$publicKey</pre>";
}

function signMessage() {
    $message = $_POST['message'] ?? '';
    $privateKey = $_POST['private_key'] ?? '';

    if (empty($message) || empty($privateKey)) {
        echo 'Message or private key missing.';
        return;
    }

    openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    echo "<pre>Signature:\n" . base64_encode($signature) . "</pre>";
}

function signFile() {
    if (!isset($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
        echo 'File not uploaded.';
        return;
    }

    $privateKey = $_POST['private_key'] ?? '';
    if (empty($privateKey)) {
        echo 'Private key is missing.';
        return;
    }

    $fileContent = file_get_contents($_FILES['file']['tmp_name']);
    openssl_sign($fileContent, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    echo "<pre>Signature:\n" . base64_encode($signature) . "</pre>";
}

function verifySignature() {
    if (isset($_FILES['file']) && !empty($_FILES['file']['tmp_name'])) {
        // File verification
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
    } else {
        // Text message verification
        $fileContent = $_POST['message'] ?? '';
    }

    $signature = base64_decode($_POST['signature'] ?? '');
    $publicKey = $_POST['public_key'] ?? '';

    if (empty($fileContent) || empty($signature) || empty($publicKey)) {
        echo 'Message/File content, signature, or public key missing.';
        return;
    }

    $result = openssl_verify($fileContent, $signature, $publicKey, OPENSSL_ALGO_SHA256);

    if ($result === 1) {
        echo "Signature is valid.";
    } elseif ($result === 0) {
        echo "Signature is invalid.";
    } else {
        echo "Error verifying signature.";
    }
}
?>
