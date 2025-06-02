<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Génération de signature
    if ($action === 'sign') {
        $message = $_POST['message'];

        // Générer une paire de clés RSA
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        $resource = openssl_pkey_new($config);

        // Extraire la clé privée
        openssl_pkey_export($resource, $privateKey);

        // Extraire la clé publique
        $keyDetails = openssl_pkey_get_details($resource);
        $publicKey = $keyDetails['key'];

        // Générer une signature numérique
        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Afficher les résultats
        echo "<h1>Résultats de la signature</h1>";
        echo "<h2>Message :</h2><textarea rows='5' cols='50' readonly>" . htmlspecialchars($message) . "</textarea>";
        echo "<h2>Signature (base64) :</h2><textarea rows='5' cols='50' readonly>" . base64_encode($signature) . "</textarea>";
        echo "<h2>Clé publique :</h2><textarea rows='5' cols='50' readonly>" . htmlspecialchars($publicKey) . "</textarea>";
        echo "<h2>Clé privée :</h2><textarea rows='5' cols='50' readonly>" . htmlspecialchars($privateKey) . "</textarea>";

        // Libérer les ressources
        openssl_free_key($resource);
    }

    // Vérification de signature
    elseif ($action === 'verify') {
        $message = $_POST['verify_message'];
        $signature = base64_decode($_POST['signature']);
        $publicKey = $_POST['publicKey'];

        // Vérifier la signature
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        $result = openssl_verify($message, $signature, $publicKeyResource, OPENSSL_ALGO_SHA256);

        // Afficher le résultat
        if ($result === 1) {
            echo "<h1>Résultat de la vérification</h1>";
            echo "<p style='color:green;'>La signature est valide.</p>";
        } elseif ($result === 0) {
            echo "<h1>Résultat de la vérification</h1>";
            echo "<p style='color:red;'>La signature est invalide.</p>";
        } else {
            echo "<h1>Erreur</h1>";
            echo "<p style='color:red;'>Une erreur est survenue lors de la vérification.</p>";
        }
    } else {
        echo "<p>Action non reconnue.</p>";
    }
} else {
    echo "<p>Accès non autorisé.</p>";
}
?>
