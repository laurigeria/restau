<?php
session_start();

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'restau2024');

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: admindashboard.php');
        exit;
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Connexion</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)), url("foodmood.jpg");
            background-size: cover;
            background-position: center;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
        }
        .box {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            padding: 30px 25px;
            width: 100%;
            max-width: 320px;
            color: white;
            text-align: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        h1 { margin: 0 0 20px 0; font-size: 22px; text-decoration: underline; }
        .champ { display: flex; flex-direction: column; gap: 5px; margin-bottom: 15px; text-align: left; }
        input {
            width: 100%;
            height: 38px;
            border-radius: 5px;
            border: none;
            outline: none;
            padding: 5px 10px;
            background: rgba(255,255,255,0.15);
            color: white;
        }
        input::placeholder { color: rgba(255,255,255,0.5); }
        button {
            background-color: rgb(52, 37, 37);
            color: white;
            border: none;
            border-radius: 5px;
            width: 130px;
            height: 38px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
        }
        button:hover { background-color: rgb(80, 60, 60); }
        .erreur {
            background: rgba(255,80,80,0.2);
            border: 1px solid rgba(255,80,80,0.5);
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .badge { font-size: 11px; opacity: 0.6; margin-top: 20px; }
    </style>
</head>
<body>
<div class="box">
    <h1>Espace Admin</h1>

    <?php if ($erreur): ?>
        <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form action="adminconnexion.php" method="POST">
        <div class="champ">
            <label for="username">Identifiant</label>
            <input type="text" id="username" name="username" placeholder="admin" required>
        </div>
        <div class="champ">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit">Se connecter</button>
    </form>
    <p class="badge">Réservé au personnel du restaurant</p>
</div>
</body>
</html>
