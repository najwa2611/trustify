<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: loginn/index.php');
    exit();
}

$service = isset($_GET['service']) ? $_GET['service'] : 'encryption';

function handleOperation($service, $data) {
    switch ($service) {
        case 'encryption':
            return base64_encode($data);
        case 'decryption':
            return base64_decode($data);
        case 'hashing':
            return hash('sha256', $data);
        case 'signing':
            return hash_hmac('sha256', $data, 'secret_key');
        case 'certificate':
            return "Simulated Certificate for: $data";
        default:
            return "Unknown service.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($service); ?> Service</title>
</head>
<body>
    <h1><?php echo ucfirst($service); ?> Service</h1>
    <form method="POST" action="">
        <label for="data">Enter Data for <?php echo ucfirst($service); ?>:</label>
        <input type="text" name="data" required><br><br>
        <button type="submit"><?php echo ucfirst($service); ?></button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['data'])) {
        $data = $_POST['data'];
        $result = handleOperation($service, $data);
        echo "<h3>Result:</h3><p>$result</p>";
    }
    ?>

    <a href="index11(1).html">Back to Services</a>
</body>
</html>
