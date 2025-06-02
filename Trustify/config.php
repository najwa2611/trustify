<?php
// Database configuration
$servername = "localhost";  // Database host
$username = "root";         // Database username
$password = "";             // Database password
$dbname = "user_data";      // Database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?php
require 'config.php'; // Include the database configuration

function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted_data = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted_data . '::' . $iv);
}

function decryptData($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

function hashData($data) {
    return password_hash($data, PASSWORD_DEFAULT);
}

function verifyHash($data, $hashed_data) {
    return password_verify($data, $hashed_data);
}

function generateSignature($data, $private_key) {
    openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
    return base64_encode($signature);
}

function verifySignature($data, $signature, $public_key) {
    $signature = base64_decode($signature);
    return openssl_verify($data, $signature, $public_key, OPENSSL_ALGO_SHA256);
}

function generateCertificate() {
    $config = array(
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    $private_key = openssl_pkey_new($config);
    $csr = openssl_csr_new(array("commonName" => "example.com"), $private_key);
    $cert = openssl_csr_sign($csr, null, $private_key, 365);

    openssl_x509_export($cert, $certout);
    openssl_pkey_export($private_key, $pkeyout);

    file_put_contents("cert.pem", $certout);
    file_put_contents("private_key.pem", $pkeyout);

    return array("cert" => $certout, "private_key" => $pkeyout);
}

// Example usage
$data = "Sensitive data";
$encryption_key = "your-encryption-key";

$encrypted_data = encryptData($data, $encryption_key);
$decrypted_data = decryptData($encrypted_data, $encryption_key);

$hashed_data = hashData($data);
$is_valid_hash = verifyHash($data, $hashed_data);

$keys = generateCertificate();
$private_key = openssl_pkey_get_private($keys['private_key']);
$public_key = openssl_pkey_get_public($keys['cert']);

$signature = generateSignature($data, $private_key);
$is_valid_signature = verifySignature($data, $signature, $public_key);

echo "Original Data: $data\n";
echo "Encrypted Data: $encrypted_data\n";
echo "Decrypted Data: $decrypted_data\n";
echo "Hashed Data: $hashed_data\n";
echo "Is Valid Hash: " . ($is_valid_hash ? "Yes" : "No") . "\n";
echo "Signature: $signature\n";
echo "Is Valid Signature: " . ($is_valid_signature ? "Yes" : "No") . "\n";
?>
