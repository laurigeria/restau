<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation</title>

    <style>
       *{
    box-sizing: border-box;
}

       body{
    margin: 0;
    min-height: 100vh;
    background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),url("foodmood.jpg");
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    font-family: Arial, Helvetica, sans-serif;
    display: flex;
    flex-direction: column;
}

        h1{
            text-align: center;
            color: white;
            text-decoration: underline;
            margin-bottom: 5px;
            font-size: clamp(20px, 5vw, 28px);
            padding: 0 10px;
        }

        .topbar{
            display: flex;
            justify-content: space-between;
            padding: 15px 20px 0 20px;
        }

        .topbar a{
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .topbar a:hover{
            text-decoration: underline;
        }

        .bienvenue{
            text-align: center;
            color: white;
            margin: 0 0 8px 0;
            padding: 0;
            font-size: 13px;
            line-height: 1;
            opacity: 0.85;
        }

        .reserve{
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 20px auto;
            width: 100%;
            max-width: 360px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            color: white;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }

        .champ{
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .champ label{
            width: 130px;
            text-align: right;
            flex-shrink: 0;
        }

        input, select{
            flex: 1;
            min-width: 0;
            height: 35px;
            border-radius: 5px;
            border: none;
            outline: none;
            padding: 5px;
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
        }

        input::placeholder{
            color: rgba(255, 255, 255, 0.6);
        }

        select option{
            background-color: rgb(52, 37, 37);
            color: white;
        }

        button{
            background-color: rgb(52, 37, 37);
            color: white;
            border-radius: 5px;
            width: 120px;
            height: 35px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            align-self: center;
        }

        button:hover{
            background-color: rgb(80, 60, 60);
        }

        @media (max-width: 480px){
            .champ{
                flex-direction: column;
                align-items: stretch;
                gap: 5px;
            }

            .champ label{
                width: auto;
                text-align: left;
            }
        }
    </style>
</head>

<body>

<div class="topbar">
    <a href="mesreservations.php" style="color:white;text-decoration:none;font-size:14px;">Mes réservations</a>
    <a href="deconnexion.php">Se déconnecter</a>
</div>

<h1>FORMULAIRE DE RÉSERVATION</h1>
<p class="bienvenue">Connecté en tant que <?= htmlspecialchars($_SESSION['user_nom']) ?></p>

<form action="traitement.php" method="POST">

    <div class="reserve">

        <div class="champ">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required>
        </div>

        <div class="champ">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" required>
        </div>

        <div class="champ">
            <label for="heure">Heure</label>
            <input type="time" id="heure" name="heure" required>
        </div>

        <div class="champ">
            <label for="nombre">Nombre de personnes</label>
            <input type="number" id="nombre" name="nombre" min="1" max="20" required>
        </div>

        <div class="champ">
            <label for="table">Table</label>
            <select id="table" name="table" required>
                <option value="" disabled selected>-- Choisissez --</option>
                <option value="15000">15 000 FCFA</option>
                <option value="25000">25 000 FCFA</option>
                <option value="50000">50 000 FCFA</option>
            </select>
        </div>

        <button type="submit">Réserver</button>

    </div>

</form>

</body>
</html>
