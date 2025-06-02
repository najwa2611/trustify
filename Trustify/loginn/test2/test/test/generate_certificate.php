<?php
// File to track form submission
$configFile = 'C:/xampp/htdocs/test/certificates/config_data.json';

// Handle file download
if (isset($_GET['download'])) {
    $type = $_GET['type'];
    $configData = json_decode(file_get_contents($configFile), true);

    if ($type === 'key') {
        $filepath = $configData['private_key_path'];
        $filename = 'private_key.pem';
    } else {
        $filepath = $configData['certificate_path'];
        $filename = 'certificate.crt';
    }

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $common_name = trim($_POST['common_name']);
    $organization = trim($_POST['organization']);
    $locality = trim($_POST['locality']);
    $country = strtoupper(trim($_POST['country']));

    // Validate country code (must be 2-letter ISO code)
    if (strlen($country) !== 2) {
        die('Country must be a 2-letter ISO country code (e.g., MA for Morocco)');
    }

    // Set up the configuration for RSA private key
    $config = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
        "config" => 'C:\xampp\apache\conf\openssl.cnf',
        "string_mask" => "default"
    ];

    // Prepare Distinguished Name (DN)
    $dn = [
        "countryName" => $country,
        "stateOrProvinceName" => $locality,
        "organizationName" => $organization,
        "commonName" => $common_name,
    ];

    // Generate the private key
    $privateKey = openssl_pkey_new($config);
    if (!$privateKey) {
        die('Unable to generate private key. ' . openssl_error_string());
    }

    // Generate the CSR with additional error handling
    $csr = @openssl_csr_new($dn, $privateKey, $config);
    if (!$csr) {
        $errors = [];
        while ($error = openssl_error_string()) {
            $errors[] = $error;
        }
        die('Unable to generate CSR. Errors: ' . implode('; ', $errors));
    }

    // Self-sign the certificate
    $certificate = openssl_csr_sign($csr, null, $privateKey, 365, $config);
    if (!$certificate) {
        die('Unable to sign the certificate. ' . openssl_error_string());
    }

    // Ensure the directory exists
    $saveDir = 'C:/xampp/htdocs/test/certificates/';
    if (!file_exists($saveDir)) {
        mkdir($saveDir, 0777, true);
    }

    // Define paths to save the key and certificate
    $privateKeyFile = $saveDir . 'private_key.pem';
    $certFile = $saveDir . 'certificate.crt';

    // Export private key to a file
    if (!openssl_pkey_export($privateKey, $privateKeyOutput, null, $config)) {
        die('Unable to export private key. ' . openssl_error_string());
    }

    // Attempt to write private key file with error handling
    if (file_put_contents($privateKeyFile, $privateKeyOutput) === false) {
        die("Unable to write private key file: $privateKeyFile");
    }

    // Export certificate to a file and to a string for display
    if (!openssl_x509_export($certificate, $certificateOutput)) {
        die('Unable to export certificate. ' . openssl_error_string());
    }

    // Attempt to write certificate file with error handling
    if (file_put_contents($certFile, $certificateOutput) === false) {
        die("Unable to write certificate file: $certFile");
    }

    // Prepare configuration data to save
    $configData = [
        'common_name' => $common_name,
        'organization' => $organization,
        'locality' => $locality,
        'country' => $country,
        'timestamp' => date('Y-m-d H:i:s'),
        'private_key_path' => $privateKeyFile,
        'certificate_path' => $certFile
    ];

    // Save configuration data
    if (file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT)) === false) {
        die("Unable to save configuration file.");
    }

    // Get certificate details
    $certDetails = openssl_x509_parse($certificate);

    // Free the resources
    openssl_pkey_free($privateKey);
    openssl_x509_free($certificate);
}

// Check if configuration exists
$configData = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : null;

// If configuration exists, always show certificate details
if ($configData): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
            background: linear-gradient(to right, #6a0dad, #9b30b0); /* Deep purple gradient */
            color: #fff;
        }
        h1, h2 {
            text-align: center;
        }
        .certificate-details {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .certificate-info {
            margin-bottom: 10px;
        }
        .certificate-text {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 15px;
            font-family: monospace;
            font-size: 14px;
            border-radius: 5px;
            color: #fff;
        }
        .download-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .download-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #6a0dad; /* Deep purple button */
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .download-btn:hover {
            background: #9b30b0; /* Lighter deep purple on hover */
        }
        .copy-btn {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .copy-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Generated Certificate Details</h1>
    
    <div class="certificate-details">
        <h2>Certificate Information</h2>
        <div class="certificate-info">
            <strong>Common Name:</strong> <?php echo htmlspecialchars($configData['common_name']); ?>
        </div>
        <div class="certificate-info">
            <strong>Organization:</strong> <?php echo htmlspecialchars($configData['organization']); ?>
        </div>
        <div class="certificate-info">
            <strong>Locality:</strong> <?php echo htmlspecialchars($configData['locality']); ?>
        </div>
        <div class="certificate-info">
            <strong>Country:</strong> <?php echo htmlspecialchars($configData['country']); ?>
        </div>
        <div class="certificate-info">
            <strong>Generated on:</strong> <?php echo htmlspecialchars($configData['timestamp']); ?>
        </div>
    </div>

    <div class="certificate-details">
        <h2>Certificate Parsed Details</h2>
        <?php foreach ($certDetails as $key => $value): ?>
            <div class="certificate-info">
                <strong><?php echo htmlspecialchars($key); ?>:</strong>
                <?php echo htmlspecialchars(is_array($value) ? print_r($value, true) : $value); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="certificate-details">
        <h2>Certificate Text</h2>
        <div class="certificate-text" id="certificate-text"><?php echo htmlspecialchars($certificateOutput); ?></div>
    </div>

    <div class="download-buttons">
        <a href="?download=1&type=key" class="download-btn">Download Private Key</a>
        <a href="?download=1&type=cert" class="download-btn">Download Certificate</a>
    </div>
</body>
</html>
<?php endif; ?>
