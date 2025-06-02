<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'] ?? '';
    $hash_algorithm = $_POST['hash_algorithm'] ?? 'sha256';  // Default to SHA-256

    // Ensure the selected hash algorithm is available
    if (!in_array($hash_algorithm, ['sha256', 'sha512', 'sha3-256', 'sha3-512'])) {
        die("Unsupported hash algorithm.");
    }

    // Generate the hash using the selected algorithm
    $hashedData = hash($hash_algorithm, $data);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash Result</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(to right, #fdeff9, #ec38bc, #7303c0, #03001e); /* Gradient de fond */
            --text-color: #ecf0f1; /* Texte clair */
            --border-radius: 8px; /* Bords arrondis */
            --container-bg: rgba(0, 0, 0, 0.7); /* Fond des formulaires */
            --link-color: #ec38bc; /* Couleur des liens */
            --button-gradient: linear-gradient(to right, #AA076B 0%, #61045F 51%, #AA076B 100%);
            --button-hover: #7303c0;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--bg-gradient);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h1 {
            color: var(--text-color);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        textarea {
            width: 100%;
            max-width: 600px;
            padding: 12px;
            margin-bottom: 20px;
            border: none;
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.2);
            color: var(--text-color);
            font-size: 1rem;
            resize: none;
        }

        textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.4);
        }

        a {
            display: inline-block;
            color: var(--link-color);
            text-decoration: none;
            font-size: 1.2rem;
            margin-top: 20px;
            padding: 10px 20px;
            border: 2px solid var(--link-color);
            border-radius: var(--border-radius);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        a:hover {
            background-color: var(--link-color);
            color: #fff;
        }

        div.container {
            background: var(--container-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 100%;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(-20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hash Result</h1>
        <p><strong>Original Data:</strong></p>
        <textarea rows="5" readonly><?php echo htmlspecialchars($data); ?></textarea>
        <p><strong>Hash Type:</strong> <?php echo htmlspecialchars($hash_algorithm); ?></p>
        <p><strong>Hashed Data:</strong></p>
        <textarea rows="5" readonly><?php echo htmlspecialchars($hashedData); ?></textarea>
        <a href="Hashing.html">Back to Form</a>
    </div>
</body>
</html>
