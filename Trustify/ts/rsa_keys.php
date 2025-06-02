<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer la taille de la clé choisie par l'utilisateur
    $keySize = intval($_POST['keySize'] ?? 2048);

    // Configuration pour la génération des clés
    $config = [
        "private_key_bits" => $keySize,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    // Générer une nouvelle clé privée
    $resource = openssl_pkey_new($config);

    // Extraire la clé privée
    openssl_pkey_export($resource, $privateKey);

    // Extraire la clé publique
    $keyDetails = openssl_pkey_get_details($resource);
    $publicKey = $keyDetails['key'];

    // Afficher les clés générées
    echo "<h1>Clés générées</h1>";
    echo "<h2>Clé privée :</h2>";
    echo "<textarea rows='10' cols='80' readonly>" . htmlspecialchars($privateKey) . "</textarea>";
    echo "<h2>Clé publique :</h2>";
    echo "<textarea rows='10' cols='80' readonly>" . htmlspecialchars($publicKey) . "</textarea>";

    // Libérer les ressources
    openssl_free_key($resource);
} else {
    echo "Accès non autorisé.";
}
?>
