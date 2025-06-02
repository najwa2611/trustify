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
            header("Location: $service.html");
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
</head>
<body>
    <div class="login">
        <img src="assets/img/img0.jpg" alt="login image" class="login__img">
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
                Don't have an account? <a href="registration.php">Register</a>
            </p>
        </form>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
