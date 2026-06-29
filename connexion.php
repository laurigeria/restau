<?php
session_start();
require_once('db.php');

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if ($email === '' || $mot_de_passe === '') {
        $erreurs[] = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT id, nom, mot_de_passe FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch();

        if (!$utilisateur || !password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            $erreurs[] = "Email ou mot de passe incorrect.";
        } else {
            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_nom'] = $utilisateur['nom'];

            header('Location: reservation.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>

    <style>
        body{
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),url("foodmood.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            font-family: Arial, Helvetica, sans-serif;
            padding: 30px;
            box-sizing: border-box;
        }

        h1{
            text-align: center;
            color: white;
            text-decoration: underline;
        }

        .reserve{
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: auto;
            width: 320px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            text-align: center;
            align-items: center;
            color: white;
            border-radius: 10px;
            backdrop-filter: blur(5px);
            background: rgba(255,255,255,0.08);
        }

        label{
            align-self: flex-start;
            margin-left: 50px;
        }

        input{
            width: 220px;
            height: 35px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.15);
            outline: none;
            padding: 5px;
             border: none;
             
        }


        button{
            background-color: rgb(52, 37, 37);
            color: white;
            border-radius: 10px;
            width: 120px;
            height: 25px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover{
            background-color: rgb(80, 60, 60);
        }

        .erreurs{
            background: rgba(255, 80, 80, 0.2);
            border: 1px solid rgba(255,80,80,0.6);
            border-radius: 8px;
            padding: 10px 15px;
            width: 280px;
            text-align: left;
        }

        .erreurs ul{
            margin: 0;
            padding-left: 20px;
        }

        .lien{
            color: #ffd9b3;
        }

        .lien a{
            color: #ffd9b3;
        }
    </style>
</head>

<body>

<div>
    <h1>CONNEXION</h1>

    <form action="connexion.php" method="POST">
        <div class="reserve">

            <?php if (!empty($erreurs)): ?>
                <div class="erreurs">
                    <ul>
                        <?php foreach ($erreurs as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <button type="submit">Se connecter</button>

            <p class="lien">Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>

        </div>
    </form>
</div>

</body>
</html>
