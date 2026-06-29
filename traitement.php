<?php
session_start();
require_once('db.php');
require_once('phpqrcode/qrlib.php');

// --- Sécurité : il faut être connecté pour réserver ---
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// --- Sécurité : vérifier que la requête vient bien d'un formulaire POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Méthode non autorisée.');
}

// --- Tables autorisées (clé => libellé) ---
$tables_autorisees = [
    '15000' => 'Table à 15 000 FCFA',
    '25000' => 'Table à 25 000 FCFA',
    '50000' => 'Table à 50 000 FCFA',
];

// --- Récupération + validation des champs ---
$erreurs = [];

$nom = trim($_POST['nom'] ?? '');
if ($nom === '') {
    $erreurs[] = "Le nom est obligatoire.";
}

$date = trim($_POST['date'] ?? '');
if ($date === '' || !DateTime::createFromFormat('Y-m-d', $date)) {
    $erreurs[] = "La date est invalide.";
}

$heure = trim($_POST['heure'] ?? '');
if ($heure === '' || !DateTime::createFromFormat('H:i', $heure)) {
    $erreurs[] = "L'heure est invalide.";
}

$nombre = $_POST['nombre'] ?? '';
if (!ctype_digit((string)$nombre) || (int)$nombre < 1 || (int)$nombre > 20) {
    $erreurs[] = "Le nombre de personnes doit être entre 1 et 20.";
}
$nombre = (int)$nombre;

$table = $_POST['table'] ?? '';
if (!array_key_exists($table, $tables_autorisees)) {
    $erreurs[] = "La table sélectionnée n'est pas valide.";
}

// --- Si erreurs, on arrête tout proprement ---
if (!empty($erreurs)) {
    http_response_code(400);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title></head><body>';
    echo '<h1>Erreur dans la réservation</h1><ul>';
    foreach ($erreurs as $e) {
        echo '<li>' . htmlspecialchars($e) . '</li>';
    }
    echo '</ul><p><a href="javascript:history.back()">Retour</a></p>';
    echo '</body></html>';
    exit;
}

// --- Création du dossier QR codes (protégé contre le listage) ---
$dossier_qr = 'qr_codes';
if (!file_exists($dossier_qr)) {
    mkdir($dossier_qr, 0755, true);
}

$htaccess_path = $dossier_qr . '/.htaccess';
if (!file_exists($htaccess_path)) {
    file_put_contents($htaccess_path, "Options -Indexes\n");
}

// --- Génération du code unique ---
$code = strtoupper(substr(md5(uniqid((string)random_int(0, PHP_INT_MAX), true)), 0, 6));

// --- Contenu du QR code ---
$qr_content = "Nom: $nom\nPersonnes: $nombre\nDate: $date\nHeure: $heure\nTable: {$tables_autorisees[$table]}\nCode: $code";

$qr_filename = $dossier_qr . '/' . $code . '.png';

// --- Génération du QR code avec gestion d'erreur ---
try {
    QRcode::png($qr_content, $qr_filename);
} catch (Throwable $e) {
    http_response_code(500);
    die('Erreur lors de la génération du QR code. Veuillez réessayer.');
}

if (!file_exists($qr_filename)) {
    http_response_code(500);
    die('Le QR code n\'a pas pu être créé. Veuillez réessayer.');
}

// --- Enregistrement en base de données ---
try {
    $stmt = $pdo->prepare(
        "INSERT INTO reservations (utilisateur_id, nom, date_resa, heure_resa, nombre, table_prix, code_reservation, qr_code_path)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $_SESSION['user_id'],
        $nom,
        $date,
        $heure,
        $nombre,
        (int)$table,
        $code,
        $qr_filename,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Erreur lors de l\'enregistrement de la réservation. Veuillez réessayer.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation de réservation</title>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: Arial, Helvetica, sans-serif;
      background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)), url("foodmood.jpg");
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      padding: 30px;
      box-sizing: border-box;
    }

    h1 {
      text-align: center;
      color: white;
      text-decoration: underline;
    }

    .box {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      padding: 30px;
      border-radius: 15px;
      display: inline-block;
      box-shadow: 0 0 15px rgba(0,0,0,0.5);
      color: white;
      text-align: center;
      max-width: 360px;
    }

    .box p {
      margin: 8px 0;
    }

    .box strong {
      color: #ffd9b3;
    }

    img {
      margin-top: 20px;
      width: 200px;
      height: 200px;
      border-radius: 8px;
      background: white;
      padding: 8px;
    }
  </style>
</head>
<body>

  <div>
    <h1>🎉 Réservation réussie !</h1>
    <div class="box">
      <p><strong>Nom :</strong> <?= htmlspecialchars($nom) ?></p>
      <p><strong>Nombre de personnes :</strong> <?= htmlspecialchars((string)$nombre) ?></p>
      <p><strong>Date :</strong> <?= htmlspecialchars($date) ?></p>
      <p><strong>Heure :</strong> <?= htmlspecialchars($heure) ?></p>
      <p><strong>Table choisie :</strong> <?= htmlspecialchars($tables_autorisees[$table]) ?></p>
      <p><strong>Code de réservation :</strong> <?= htmlspecialchars($code) ?></p>

      <p>📲 Présentez ce QR code à l'entrée :</p>
      <img src="<?= htmlspecialchars($qr_filename) ?>" alt="QR Code de réservation">

      <p style="margin-top: 20px;">
        <a href="reservation.php" style="color: #ffd9b3; text-decoration: none;">↩ Nouvelle réservation</a>
        &nbsp;|&nbsp;
        <a href="deconnexion.php" style="color: #ffd9b3; text-decoration: none;">Se déconnecter</a>
      </p>
    </div>
  </div>

</body>
</html>
