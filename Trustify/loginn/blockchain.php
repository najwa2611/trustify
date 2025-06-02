<?php
session_start();
include('config1.php');

// Initialiser les variables
$public_key = '';
$private_key = '';
$wallet_address = '';
$wallet_private_key = '';
$signed_transaction = '';
$error_message = '';

// Fonction pour générer des clés RSA
function generateRSAKeys($pdo) {
    global $public_key, $private_key;

    $config = [
        "digest_alg" => "sha512",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA
    ];

    $res = openssl_pkey_new($config);
    if ($res === false) {
        return "Erreur lors de la génération de la clé RSA: " . openssl_error_string();
    }

    openssl_pkey_export($res, $private_key);
    $pubKey = openssl_pkey_get_details($res);
    $public_key = $pubKey['key'];

    $_SESSION['public_key'] = $public_key;
    $_SESSION['private_key'] = $private_key;

    $stmt = $pdo->prepare("INSERT INTO rsa_keys (public_key, private_key) VALUES (:public_key, :private_key)");
    $stmt->execute([ ':public_key' => $public_key, ':private_key' => $private_key ]);

    return true;
}

// Créer un portefeuille
function createWallet($pdo) {
    global $wallet_address, $wallet_private_key;

    $wallet_address = '0x' . bin2hex(random_bytes(20));
    $wallet_private_key = bin2hex(random_bytes(32));

    $wallet_name = $_POST['wallet_name'];
    $stmt = $pdo->prepare("INSERT INTO wallets (wallet_name, wallet_address, private_key) VALUES (:wallet_name, :wallet_address, :private_key)");
    $stmt->execute([ ':wallet_name' => $wallet_name, ':wallet_address' => $wallet_address, ':private_key' => $wallet_private_key ]);

    $_SESSION['wallet_private_key'] = $wallet_private_key;
}

// Signer une transaction
function signTransaction($pdo, $transaction_data) {
    global $signed_transaction;

    if (!empty($_SESSION['wallet_private_key'])) {
        $signed_transaction = hash('sha256', $transaction_data . $_SESSION['wallet_private_key']);
        $stmt = $pdo->prepare("INSERT INTO signed_transactions (transaction_data, signed_transaction) VALUES (:transaction_data, :signed_transaction)");
        $stmt->execute([ ':transaction_data' => $transaction_data, ':signed_transaction' => $signed_transaction ]);
        return true;
    } else {
        $signed_transaction = "Erreur: Clé privée manquante.";
        return false;
    }
}

// Récupérer l'historique des transactions
function getTransactionHistory($pdo, $wallet_address) {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE wallet_address = :wallet_address ORDER BY date DESC");
    $stmt->execute([ ':wallet_address' => $wallet_address ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Envoyer de la cryptomonnaie
function sendTransaction($pdo, $recipient_address, $amount) {
    global $wallet_address, $wallet_private_key;

    if (!empty($_SESSION['wallet_private_key'])) {
        if (is_numeric($amount) && $amount > 0) {
            // Vérification si l'adresse de l'expéditeur et le montant sont valides
            $transaction_data = [
                'from_address' => $wallet_address,
                'to_address' => $recipient_address,
                'amount' => $amount,
                'private_key' => $_SESSION['wallet_private_key']
            ];

            // Assurez-vous que la requête SQL inclut tous les paramètres nécessaires
            $stmt = $pdo->prepare("INSERT INTO transactions (from_address, to_address, amount, date) 
                                   VALUES (:from_address, :to_address, :amount, NOW())");
            // Assurez-vous que tous les paramètres sont correctement bindés
            $stmt->bindParam(':from_address', $transaction_data['from_address']);
            $stmt->bindParam(':to_address', $transaction_data['to_address']);
            $stmt->bindParam(':amount', $transaction_data['amount']);
            
            if ($stmt->execute()) {
                return "Transaction sent successfully!";
            } else {
                return "Transaction failed.";
            }
        } else {
            return "Invalid amount.";
        }
    } else {
        return "Private key missing.";
    }
}


// Récupérer le solde du portefeuille
function getWalletBalance($pdo, $wallet_address) {
    $stmt = $pdo->prepare("SELECT SUM(amount) as balance FROM transactions WHERE from_address = :wallet_address");
    $stmt->execute([ ':wallet_address' => $wallet_address ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['balance'] : 0;
}

// Traiter les soumissions des formulaires via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Générer des clés RSA
    if (isset($_POST['generate_keys'])) {
        $result = generateRSAKeys($pdo);
        echo $result === true ? 'Keys generated successfully!' : $result;
        exit;
    }

    // Créer un portefeuille
    if (isset($_POST['create_wallet'])) {
        createWallet($pdo);
        echo json_encode([ 'wallet_address' => $wallet_address, 'wallet_private_key' => $wallet_private_key ]);
        exit;
    }

    // Signer une transaction
    if (isset($_POST['sign_transaction'])) {
        $transaction_data = $_POST['transaction_data'] ?? '';
        signTransaction($pdo, $transaction_data);
        echo json_encode([ 'signed_transaction' => $signed_transaction ]);
        exit;
    }

    // Historique des transactions
    if (isset($_POST['action']) && $_POST['action'] === 'get_transaction_history') {
        $wallet_address = $_POST['wallet_address'];
        $transactions = getTransactionHistory($pdo, $wallet_address);
        echo json_encode($transactions);
        exit;
    }

    // Envoyer de la cryptomonnaie
    if (isset($_POST['send_transaction'])) {
        $recipient_address = $_POST['recipient_address'];
        $amount = $_POST['amount'];
        $response = sendTransaction($pdo, $recipient_address, $amount);
        echo $response;
        exit;
    }

    // Solde du portefeuille
    if (isset($_POST['action']) && $_POST['action'] === 'get_wallet_balance') {
        $wallet_address = $_POST['wallet_address'];
        $balance = getWalletBalance($pdo, $wallet_address);
        echo json_encode([ 'balance' => $balance ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Services</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-bg-color: #4b0082;
            --primary-text-color: #ecf0f1;
            --accent-color: #800080;
            --hover-color: #551a8b;
            --button-bg-color: #800080;
            --button-hover-color: #551a8b;
            --secondary-bg-color: rgba(255, 255, 255, 0.1);
            --font-family: 'Roboto', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: var(--font-family);
            background: var(--primary-bg-color);
            color: var(--primary-text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .container {
            background-color: var(--secondary-bg-color);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            width: 80%;
            max-width: 800px;
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid var(--accent-color);
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--primary-text-color);
        }

        button {
            background-color: var(--button-bg-color);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: var(--button-hover-color);
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .output {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 6px;
            word-wrap: break-word;
            margin-top: 15px;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        @media screen and (max-width: 600px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <h1>Blockchain Services</h1>
    
    <div class="container">
        <!-- Key Generation Section -->
        <div class="section">
            <h2>Generate RSA Keys</h2>
            <form id="generate_keys_form">
                <button type="submit" name="generate_keys">Generate Keys</button>
            </form>
            <div class="output">
                <h3>Your Public Key:</h3>
                <textarea readonly id="public_key"><?php echo isset($_SESSION['public_key']) ? $_SESSION['public_key'] : ''; ?></textarea>
                <h3>Your Private Key:</h3>
                <textarea readonly id="private_key"><?php echo isset($_SESSION['private_key']) ? $_SESSION['private_key'] : ''; ?></textarea>
            </div>
        </div>

        <!-- Cryptocurrency Wallet Section -->
        <div class="section">
            <h2>Create Cryptocurrency Wallet</h2>
            <form id="create_wallet_form">
                <input type="text" name="wallet_name" placeholder="Enter Wallet Name" required>
                <button type="submit" name="create_wallet">Create Wallet</button>
            </form>
            <div class="output">
                <h3>Wallet Details:</h3>
                <p>Wallet Address: <span id="wallet-address"><?php echo isset($wallet_address) ? $wallet_address : 'Not created yet'; ?></span></p>
                <p>Private Key: <span id="wallet-private-key"><?php echo isset($wallet_private_key) ? $wallet_private_key : 'Not created yet'; ?></span></p>
            </div>
        </div>

        <!-- Transaction Signing Section -->
        <div class="section">
            <h2>Sign Transaction</h2>
            <form id="sign_transaction_form">
                <textarea name="transaction_data" id="transaction_data" placeholder="Enter transaction data" required></textarea>
                <button type="submit" name="sign_transaction">Sign Transaction</button>
            </form>
            <div class="output">
                <h3>Transaction Signature:</h3>
                <textarea readonly id="signed_transaction"></textarea>
            </div>
        </div>

        <!-- Send Cryptocurrency Section -->
        <div class="section">
            <h2>Send Cryptocurrency</h2>
            <form id="send_transaction_form">
                <input type="text" name="recipient_address" placeholder="Recipient Wallet Address" required>
                <input type="number" name="amount" placeholder="Amount to Send" required>
                <button type="submit" name="send_transaction">Send</button>
            </form>
            <div class="output">
                <h3>Transaction Result:</h3>
                <p id="send-transaction-result"></p>
            </div>
        </div>

    
        <!-- Wallet Balance Section -->
        <div class="section">
            <h2>Wallet Balance</h2>
            <form id="wallet_balance_form">
                <input type="text" name="wallet_address" placeholder="Enter Wallet Address" required>
                <button type="submit" name="get_wallet_balance">Check Balance</button>
            </form>
            <div id="wallet-balance">
                <!-- Le solde sera affiché ici -->
            </div>
        </div>
    </div>

    <script>
        // AJAX pour générer les clés RSA
        $('#generate_keys_form').submit(function(e) {
            e.preventDefault();
            $.post('blockchain.php', { generate_keys: true }, function(response) {
                alert(response);
                location.reload();
            });
        });

        // AJAX pour créer un portefeuille
        $('#create_wallet_form').submit(function(e) {
            e.preventDefault();
            $.post('blockchain.php', $(this).serialize() + '&create_wallet=true', function(response) {
                var data = JSON.parse(response);
                $('#wallet-address').text(data.wallet_address);
                $('#wallet-private-key').text(data.wallet_private_key);
            });
        });

        // AJAX pour signer une transaction
        $('#sign_transaction_form').submit(function(e) {
            e.preventDefault();
            var transaction_data = $('#transaction_data').val();
            $.post('blockchain.php', { sign_transaction: true, transaction_data: transaction_data }, function(response) {
                var data = JSON.parse(response);
                $('#signed_transaction').val(data.signed_transaction);
            });
        });

        // AJAX pour envoyer de la cryptomonnaie
        $('#send_transaction_form').submit(function(e) {
            e.preventDefault();
            var recipient_address = $('input[name="recipient_address"]').val();
            var amount = $('input[name="amount"]').val();
            $.post('blockchain.php', { send_transaction: true, recipient_address: recipient_address, amount: amount }, function(response) {
                $('#send-transaction-result').text(response);
            });
        });

        // AJAX pour récupérer l'historique des transactions
        $('#transaction_history_form').submit(function(e) {
            e.preventDefault();
            var wallet_address = $('input[name="wallet_address"]').val();
            $.post('blockchain.php', { action: 'get_transaction_history', wallet_address: wallet_address }, function(response) {
                var transactions = JSON.parse(response);
                var html = '<ul>';
                transactions.forEach(function(tx) {
                    html += '<li>' + tx.date + ' - From: ' + tx.from_address + ' To: ' + tx.to_address + ' Amount: ' + tx.amount + '</li>';
                });
                html += '</ul>';
                $('#transaction-history').html(html);
            });
        });

        // AJAX pour vérifier le solde du portefeuille
        $('#wallet_balance_form').submit(function(e) {
            e.preventDefault();
            var wallet_address = $('input[name="wallet_address"]').val();
            $.post('blockchain.php', { action: 'get_wallet_balance', wallet_address: wallet_address }, function(response) {
                var data = JSON.parse(response);
                $('#wallet-balance').html('Balance: ' + data.balance);
            });
        });
    </script>

</body>
</html>
