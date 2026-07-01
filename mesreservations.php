<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$reservations = $pdo->prepare(
    "SELECT * FROM reservations WHERE utilisateur_id = ? ORDER BY created_at DESC"
);
$reservations->execute([$_SESSION['user_id']]);
$reservations = $reservations->fetchAll();

$tables = ['15000' => '15 000 FCFA', '25000' => '25 000 FCFA', '50000' => '50 000 FCFA'];

$statut_labels = [
    'en_attente' => 'En attente',
    'validee'    => 'Validée',
    'annulee'    => 'Annulée',
];

$statut_colors = [
    'en_attente' => '#ffd166',
    'validee'    => '#06d6a0',
    'annulee'    => '#ef476f',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes réservations</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)), url("foodmood.jpg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            color: white;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header h1 {
            font-size: clamp(18px, 4vw, 26px);
            text-decoration: underline;
        }

        .header-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .header-links a {
            background: rgb(52,37,37);
            color: #ffd9b3;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
        }

        .header-links a:hover { background: rgb(80,60,60); }

        .bienvenue {
            font-size: 13px;
            opacity: 0.8;
            margin-bottom: 20px;
        }

        /* Carte réservation */
        .liste {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .carte {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(6px);
            border-radius: 12px;
            padding: 18px 20px;
            border-left: 4px solid transparent;
        }

        .carte.en_attente { border-left-color: #ffd166; }
        .carte.validee    { border-left-color: #06d6a0; }
        .carte.annulee    { border-left-color: #ef476f; opacity: 0.75; }

        .carte-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .carte-titre {
            font-size: 15px;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge.en_attente { background: rgba(255,209,102,0.2); color: #ffd166; border: 1px solid #ffd166; }
        .badge.validee    { background: rgba(6,214,160,0.2);   color: #06d6a0; border: 1px solid #06d6a0; }
        .badge.annulee    { background: rgba(239,71,111,0.2);   color: #ef476f; border: 1px solid #ef476f; }

        .carte-infos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
            font-size: 13px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-label { opacity: 0.6; font-size: 11px; }
        .info-val   { font-weight: bold; }

        .annulation-msg {
            margin-top: 12px;
            padding: 8px 12px;
            background: rgba(239,71,111,0.15);
            border-radius: 6px;
            font-size: 12px;
            color: #ef476f;
        }

        .vide {
            text-align: center;
            padding: 60px 20px;
            opacity: 0.5;
        }

        .vide a {
            display: inline-block;
            margin-top: 15px;
            background: rgb(52,37,37);
            color: #ffd9b3;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .carte-infos { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Mes réservations</h1>
    <div class="header-links">
        <a href="reservation.php">+ Nouvelle réservation</a>
        <a href="deconnexion.php">Se déconnecter</a>
    </div>
</div>

<p class="bienvenue">Connecté en tant que <?= htmlspecialchars($_SESSION['user_nom']) ?></p>

<?php if (empty($reservations)): ?>
    <div class="vide">
        <p>Vous n'avez pas encore de réservation.</p>
        <a href="reservation.php">Réserver maintenant</a>
    </div>
<?php else: ?>
    <div class="liste">
        <?php foreach ($reservations as $r): ?>
        <div class="carte <?= $r['statut'] ?>">
            <div class="carte-header">
                <span class="carte-titre">Réservation #<?= $r['id'] ?> — <?= htmlspecialchars($r['nom']) ?></span>
                <span class="badge <?= $r['statut'] ?>"><?= $statut_labels[$r['statut']] ?></span>
            </div>
            <div class="carte-infos">
                <div class="info-item">
                    <span class="info-label">Date</span>
                    <span class="info-val"><?= htmlspecialchars($r['date_resa']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Heure</span>
                    <span class="info-val"><?= htmlspecialchars(substr($r['heure_resa'], 0, 5)) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Personnes</span>
                    <span class="info-val"><?= $r['nombre'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Table</span>
                    <span class="info-val"><?= $tables[$r['table_prix']] ?? $r['table_prix'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Code</span>
                    <span class="info-val"><?= htmlspecialchars($r['code_reservation']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Reçu le</span>
                    <span class="info-val"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></span>
                </div>
            </div>
            <?php if ($r['statut'] === 'annulee'): ?>
            <div class="annulation-msg">
                ⚠️ Cette réservation a été annulée. Vous pouvez en faire une nouvelle.
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
