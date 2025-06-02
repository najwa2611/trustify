<?php
// Vérifier l'extension OpenSSL
if (!extension_loaded('openssl')) {
    die(json_encode(['status' => 'error', 'message' => 'OpenSSL extension is not loaded.']));
}

$action = $_POST['action'] ?? null;

switch ($action) {
    case 'generate_keys':
        generateKeys();
        break;
    case 'sign_message':
        signMessage();
        break;
    case 'verify_signature':
        verifySignature();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}

// Générer des clés RSA
function generateKeys() {
    $config = [
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    $privateKeyResource = openssl_pkey_new($config);
    openssl_pkey_export($privateKeyResource, $privateKey);
    $publicKey = openssl_pkey_get_details($privateKeyResource)['key'];

    echo json_encode([
        'status' => 'success',
        'private_key' => $privateKey,
        'public_key' => $publicKey,
    ]);
}

// Signer un message
function signMessage() {
    $message = $_POST['message'] ?? '';
    $privateKey = $_POST['private_key'] ?? '';

    $privateKeyResource = openssl_pkey_get_private($privateKey);
    if (!$privateKeyResource) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid private key.']);
        return;
    }

    openssl_sign($message, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
    echo json_encode(['status' => 'success', 'signature' => base64_encode($signature)]);
}

// Vérifier une signature
function verifySignature() {
    $message = $_POST['message'] ?? '';
    $signature = base64_decode($_POST['signature'] ?? '');
    $publicKey = $_POST['public_key'] ?? '';

    $publicKeyResource = openssl_pkey_get_public($publicKey);
    if (!$publicKeyResource) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid public key.']);
        return;
    }

    $result = openssl_verify($message, $signature, $publicKeyResource, OPENSSL_ALGO_SHA256);
    echo json_encode(['status' => $result === 1 ? 'success' : 'error', 'message' => $result === 1 ? 'Signature is valid.' : 'Signature is invalid.']);
}
?>
