<?php
class DatabaseOperations {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getUserKeys($email) {
        $stmt = $this->conn->prepare("SELECT rsaprvkey, rsapubkey FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
    
    public function saveKeys($email, $private_key, $public_key) {
        $stmt = $this->conn->prepare("UPDATE users SET rsaprvkey = ?, rsapubkey = ? WHERE email = ?");
        $stmt->bind_param("sss", $private_key, $public_key, $email);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    
    public function verifyUser($email, $password) {
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        return false;
    }
}

class KeyGenerator {
    private $config;
    
    public function __construct() {
        $this->config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
    }
    
    public function generateKeyPair() {
        $res = openssl_pkey_new($this->config);
        if ($res === false) {
            throw new Exception('Failed to generate new OpenSSL key pair: ' . openssl_error_string());
        }
        
        openssl_pkey_export($res, $privateKey);
        $pubKey = openssl_pkey_get_details($res);
        
        return [
            'private_key' => $privateKey,
            'public_key' => $pubKey['key']
        ];
    }
}