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
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        textarea, input, button {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        h1 {
            color: #4a245e;
        }
    </style>
</head>
<body>

    <h1>Hash Result</h1>
    <p><strong>Original Data:</strong></p>
    <textarea rows="5" readonly><?php echo htmlspecialchars($data); ?></textarea><br>
    <p><strong>Hash Type: </strong><?php echo htmlspecialchars($hash_algorithm); ?></p>
    <p><strong>Hashed Data:</strong></p>
    <textarea rows="5" readonly><?php echo htmlspecialchars($hashedData); ?></textarea><br>
    <a href="hash_generator.html">Back to Form</a>

</body>
</html>
