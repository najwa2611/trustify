<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Électronique et Génération de Clés</title>
    <style>
        :root {
            --nav-bg-color: #4a245e; 
            --nav-hover-color: #b88dc7;
            --nav-text-color: #fff;
            --header-height: 70px;
            --font-family: 'Arial', sans-serif;
            --background-color: rgb(255, 255, 255);
            --button-color: #007BFF;
            --button-hover-color: #0056b3;
            --border-color: #ccc;
            --input-background-color: #f9f9f9;
            --text-color: #000;
            --title-color: #b6277f;
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            padding-top: var(--header-height);
        }

        h1 {
            color: var(--title-color);
            text-align: center;
            margin-top: 20px;
        }

        .content {
            padding: 20px;
        }

        .container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        form {
            flex: 1;
            margin: 10px;
            background-color: var(--background-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 45%;
        }

        textarea, input, button {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--input-background-color);
        }

        button {
            background-color: var(--button-color);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: var(--button-hover-color);
        }

        .nav {
            height: var(--header-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            background-color: var(--nav-bg-color);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .nav__logo {
            color: var(--nav-text-color);
            font-weight: bold;
            font-size: 1.5rem;
            text-transform: uppercase;
        }

        .nav__menu {
            display: flex;
            align-items: center;
        }

        .nav__list {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav__item {
            margin: 0 15px;
        }

        .nav__link {
            color: var(--nav-text-color);
            text-decoration: none;
            font-size: 1rem;
            font-weight: bold;
            text-transform: capitalize;
            transition: color 0.3s;
        }

        .nav__link:hover {
            color: var(--nav-hover-color);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav">
        <a href="#" class="nav__logo">Trustify</a>
        <div class="nav__menu">
            <ul class="nav__list">
                <li class="nav_item"><a href="#home" class="nav_link">Home</a></li>
                <li class="nav_item"><a href="#about" class="nav_link">About</a></li>
                <li class="nav_item"><a href="#services" class="nav_link">Why Us</a></li>
                <li class="nav_item"><a href="#contact" class="nav_link">Services</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <h1>Signature Électronique et Génération de Clés</h1>
        <div class="container">
            <!-- AES Encryption Form -->
            <form action="sig.php" method="POST">
                <h2>Générer des Clés</h2>
                <input type="hidden" name="operation" value="generate">
                <button type="submit">Générer des Clés</button>
            </form>

            <!-- AES Sign Form -->
            <form action="sig.php" method="POST">
                <h2>Signer un Message</h2>
                <textarea name="message" placeholder="Entrez le message à signer..." rows="5" required></textarea><br>
                <textarea name="private_key" placeholder="Entrez la clé privée..." rows="5" required></textarea><br>
                <button type="submit" name="operation" value="sign">Signer</button>
            </form>

            <!-- AES Verify Form -->
            <form action="sig.php" method="POST">
                <h2>Vérifier une Signature</h2>
                <textarea name="message" placeholder="Entrez le message..." rows="5" required></textarea><br>
                <textarea name="signature" placeholder="Entrez la signature (Base64)..." rows="5" required></textarea><br>
                <textarea name="public_key" placeholder="Entrez la clé publique..." rows="5" required></textarea><br>
                <button type="submit" name="operation" value="verify">Vérifier</button>
            </form>
        </div>
    </div>
</body>
</html>
