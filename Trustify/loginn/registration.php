<?php
require('db.php');
session_start();

// Récupération du paramètre `service` depuis l'URL
$service = isset($_GET['service']) ? $_GET['service'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assainir et récupérer les données du formulaire
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Vérification des mots de passe
    if ($password === $confirm_password) {
        // Vérifier si l'email existe déjà
        $check_email_query = "SELECT * FROM `users` WHERE `email`='$email'";
        $result = mysqli_query($conn, $check_email_query);

        if (mysqli_num_rows($result) == 0) {
            // Ajouter l'utilisateur dans la base de données
            $insert_query = "INSERT INTO `users` (email, password) VALUES ('$email', '$password')";
            if (mysqli_query($conn, $insert_query)) {
                echo "<script>alert('Registration successful! Redirecting to login page.'); window.location.href='index.php?service=" . urlencode($service) . "';</script>";
                exit;
            } else {
                echo "<div class='form'><h3>Something went wrong. Please try again.</h3></div>";
            }
        } else {
            echo "<div class='form'><h3>Email already exists. Please choose a different one.</h3></div>";
        }
    } else {
        echo "<div class='form'><h3>Passwords do not match.</h3></div>";
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
    <title>Registration Form</title>
</head>
<body>
    <div class="login">
        <img src="assets/img/img0.jpg" alt="registration image" class="login__img">

        <form action="" method="POST" class="login__form">
            <h1 class="login__title">Register</h1>

            <div class="login__content">
                <div class="login__box">
                    <i class="ri-mail-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="email" name="email" required class="login__input" placeholder=" ">
                        <label class="login__label">Email</label>
                    </div>
                </div>
                <div class="login__box">
                    <i class="ri-lock-2-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" name="password" required class="login__input" placeholder=" ">
                        <label class="login__label">Password</label>
                    </div>
                </div>
                <div class="login__box">
                    <i class="ri-lock-password-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" name="confirm_password" required class="login__input" placeholder=" ">
                        <label class="login__label">Confirm Password</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="login__button">Register</button>

            <p class="login__register">
                Already have an account? <a href="index.php?service=<?php echo urlencode($service); ?>">Login</a>
            </p>
        </form>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
