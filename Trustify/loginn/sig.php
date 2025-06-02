<?php
// Configuration de la base de données
$host = 'localhost';
$dbname = 'rsa_signature_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Fonction pour générer des paires de clés RSA
function generateRSAKeys($email, $password) {
    global $pdo;

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Si l'utilisateur n'existe pas, générer les clés RSA
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);
        openssl_pkey_export($privateKey, $privateKeyOutput);
        $publicKeyDetails = openssl_pkey_get_details($privateKey);
        $publicKey = $publicKeyDetails['key'];

        // Hasher le mot de passe avant de le stocker
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insérer l'utilisateur et les clés dans la base de données
        $stmt = $pdo->prepare("INSERT INTO users (email, password, rsa_public_key, rsa_private_key) VALUES (:email, :password, :publicKey, :privateKey)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $hashedPassword,
            ':publicKey' => $publicKey,
            ':privateKey' => $privateKeyOutput,
        ]);

        return ['publicKey' => $publicKey, 'privateKey' => $privateKeyOutput];
    } else {
        return "L'email est déjà enregistré. Veuillez utiliser un autre email.";
    }
}

// Fonction pour récupérer la clé privée à partir de la base de données
function getPrivateKeyByEmail($email) {
    global $pdo;

    // Récupérer la clé privée de l'utilisateur par son email
    $stmt = $pdo->prepare("SELECT rsa_private_key FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch();
    if (!$user) {
        echo "<div class='error'>Utilisateur non trouvé.</div>";
        return null;
    }

    return $user['rsa_private_key'];
}

// Fonction pour signer un message
function signMessage($message, $privateKey) {
    $privateKeyResource = openssl_pkey_get_private($privateKey);
    if (!$privateKeyResource) {
        echo "<div class='error'>Clé privée invalide.</div>";
        return;
    }
    openssl_sign($message, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
    return base64_encode($signature);
}

// Fonction pour signer un fichier
function signFile($file, $privateKey) {
    // Vérifier que le fichier existe et peut être lu
    if (!file_exists($file['tmp_name'])) {
        echo "<div class='error'>Le fichier est introuvable ou corrompu.</div>";
        return;
    }
    $fileContent = file_get_contents($file['tmp_name']);

    $privateKeyResource = openssl_pkey_get_private($privateKey);
    if (!$privateKeyResource) {
        echo "<div class='error'>Clé privée invalide.</div>";
        return;
    }

    // Signer le contenu du fichier
    openssl_sign($fileContent, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
    return base64_encode($signature);
}

// Fonction pour vérifier une signature
function verifySignature($message, $signature, $publicKey) {
    $publicKeyResource = openssl_pkey_get_public($publicKey);
    $decodedSignature = base64_decode($signature);
    return openssl_verify($message, $decodedSignature, $publicKeyResource, OPENSSL_ALGO_SHA256) === 1;
}

// Traitement des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'generate_keys') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $result = generateRSAKeys($email, $password);

        if (is_array($result)) {
            echo "Clé publique :<pre>{$result['publicKey']}</pre>";
            echo "Clé privée :<pre>{$result['privateKey']}</pre>";
        } else {
            echo $result; // Affiche un message d'erreur si applicable
        }
    } elseif ($action === 'sign_message') {
        $email = trim($_POST['email']); // L'utilisateur entre seulement son email
        $message = $_POST['message'];

        // Récupérer la clé privée depuis la base de données
        $privateKey = getPrivateKeyByEmail($email);
        if ($privateKey) {
            $signature = signMessage($message, $privateKey);
            echo "Signature du message :<pre>$signature</pre>";
        }
    } elseif ($action === 'sign_file') {
        // Signer un fichier
        if (isset($_FILES['file'])) {
            $email = trim($_POST['email']); // L'utilisateur entre seulement son email
            $file = $_FILES['file'];

            // Récupérer la clé privée depuis la base de données
            $privateKey = getPrivateKeyByEmail($email);
            if ($privateKey) {
                $signature = signFile($file, $privateKey);
                echo "Signature du fichier :<pre>$signature</pre>";
            }
        } else {
            echo "<div class='error'>Erreur : Aucun fichier téléchargé.</div>";
        }
    } elseif ($action === 'verify_signature') {
        $message = $_POST['message'];
        $signature = $_POST['signature'];
        $publicKey = $_POST['public_key'];
        $isValid = verifySignature($message, $signature, $publicKey);
        echo $isValid ? "La signature est valide." : "La signature est invalide.";
    }
}
