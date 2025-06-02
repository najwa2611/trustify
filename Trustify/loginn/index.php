<?php
require('db.php');
session_start();

// Récupérer le service choisi avant la connexion (depuis l'URL)
$service = isset($_GET['service']) ? $_GET['service'] : null;

if (isset($_POST['email']) && isset($_POST['password'])) {
    // Assainir les entrées utilisateur
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Requête pour vérifier les identifiants
    $query = "SELECT * FROM `users` WHERE `email`='$email' AND `password`='$password'";
    $result = mysqli_query($conn, $query);
    $rows = mysqli_num_rows($result);

    if ($rows == 1) {
        // L'utilisateur est authentifié, on démarre la session
        $_SESSION['email'] = $email;

        // Si un service est choisi, rediriger vers la page correspondante
        if ($service) {
            // Vérifier si le service est RSA, Blockchain ou autre et rediriger
            if ($service == 'rsa') {
                header("Location: rsa.php");
            } elseif ($service == 'blockchain') {
                header("Location: blockchain.php");
            } else {
                // Sinon, rediriger vers la page HTML correspondante
                header("Location: $service.html");
            }
            exit;
        } else {
            // Si aucun service n'est choisi (ce qui ne devrait pas arriver normalement)
            header("Location: start.php");
            exit;
        }
    } else {
        echo "<div class='form'>
        <h3>Email ou mot de passe incorrect.</h3>
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>Login Form</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        /* Vidéo en arrière-plan */
        video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .login {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .login__form {
            background: rgba(0, 0, 0, 0.7); /* Fond semi-transparent */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
            color: #fff;
        }

        .login__title {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .login__input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
        }

        .login__button {
            width: 100%;
            padding: 10px;
            background: #ec38bc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login__button:hover {
            background: #7303c0;
        }

        .login__register a {
            color: #ec38bc;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Vidéo en arrière-plan -->
    <video autoplay muted loop>
        <source src="assets/img/vidlog1.mp4" type="video/mp4">
        Votre navigateur ne supporte pas les vidéos HTML5.
    </video>

    <div class="login">
        <!-- Login Form -->
        <form id="login-form" action="" method="POST" class="login__form">
            <h1 class="login__title">Login</h1>
            <div class="login__content">
                <div class="login__box">
                    <i class="ri-user-3-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="email" name="email" required class="login__input" id="login-email" placeholder=" ">
                        <label for="login-email" class="login__label">Email</label>
                    </div>
                </div>
                <div class="login__box">
                    <i class="ri-lock-2-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" name="password" required class="login__input" id="login-pass" placeholder=" ">
                        <label for="login-pass" class="login__label">Password</label>
                        <i class="ri-eye-off-line login__eye" id="login-eye"></i>
                    </div>
                </div>
            </div>
            <button type="submit" class="login__button">Login</button>
            <p class="login__register">
                Don't have an account? <a href="registration.php?service=<?php echo urlencode($service); ?>">Register</a>
            </p>
        </form>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
