<?php
session_start();
require_once('db.php');

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    if ($nom === '') {
        $erreurs[] = "Le nom est obligatoire.";
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email est invalide.";
    }

    if ($telephone === '') {
        $erreurs[] = "Le numéro de téléphone est obligatoire.";
    }

    if (strlen($mot_de_passe) < 6) {
        $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if ($mot_de_passe !== $confirmation) {
        $erreurs[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifier si l'email existe déjà
    if (empty($erreurs)) {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreurs[] = "Un compte existe déjà avec cet email.";
        }
    }

    if (empty($erreurs)) {
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $telephone, $hash]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_nom'] = $nom;

        header('Location: reservation.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>

    <style>
        body{
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),url("food-mood.jpg");
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
            padding-bottom:5px;
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
            height: 25px;
            border-radius: 10px;
            outline: none;
            padding: 5px;
             background-color: rgba(255, 255, 255, 0.15);
             border: none;
        }

        button{
            background-color: rgb(52, 37, 37);
            color: white;
            border-radius: 10px;
            width: 120px;
            height: 35px;
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
            border-radius: 10px;
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
    <h1>CRÉER UN COMPTE</h1>

    <form action="inscription.php" method="POST">
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

            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" required>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <label for="confirmation">Confirmer le mot de passe</label>
            <input type="password" id="confirmation" name="confirmation" required>
            <button type="submit">S'inscrire</button>

            <p class="lien">Déjà un compte ? <a href="connexion.php">Se connecter</a></p>

        </div>
          
    </form>
</div>

</body>
</html>
