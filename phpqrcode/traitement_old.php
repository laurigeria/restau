
<?php
require_once('phpqrcode/qrlib.php'); // Assure-toi que le dossier phpqrcode contient bien qrlib.php
// Connexion à la base MySQL (WAMP)
$host = "localhost";
$dbname = "restauration"; // Ton nom de base
$user = "root";           // Utilisateur par défaut sous WAMP
$pass = "";               // Mot de passe vide par défaut

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des champs du formulaire
    $nom = htmlspecialchars(trim($_POST['nom']));
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $personnes = (int)$_POST['personnes'];
    $table_prix = (int)$_POST['table'];

    // Vérification du stock de la table choisie
    $stmt = $pdo->prepare("SELECT quantite_max, quantite_reservee FROM tables_stock WHERE prix_table = ?");
    $stmt->execute([$table_prix]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        die("Type de table non reconnu.");
    }

    $disponible = $stock['quantite_max'] - $stock['quantite_reservee'];

    if ($disponible <= 0) {
        echo "<h2>Désolé 😢</h2><p>Il n'y a plus de tables disponibles à $table_prix FCFA.</p>";
        exit;
    }

    // Contenu du QR code
    $contenuQR = "Nom: $nom\nDate: $date\nHeure: $heure\nPersonnes: $personnes\nTable: $table_prix FCFA";

    // Création du dossier qr_codes s’il n’existe pas
    $qrFolder = "qr_codes";
    if (!file_exists($qrFolder)) {
        mkdir($qrFolder, 0755, true);
    }

    // Nom unique pour le fichier QR
    $qrFilename = $qrFolder . "/qr_" . uniqid() . ".png";

    // Génération du QR code
    QRcode::png($contenuQR, $qrFilename, QR_ECLEVEL_H, 5);

    // Enregistrement de la réservation
    $insert = $pdo->prepare("INSERT INTO reservations (nom, date, heure, personnes, table_prix, qr_code_path) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->execute([$nom, $date, $heure, $personnes, $table_prix, $qrFilename]);

    // Mise à jour du stock de la table choisie
    $update = $pdo->prepare("UPDATE tables_stock SET quantite_reservee = quantite_reservee + 1 WHERE prix_table = ?");
    $update->execute([$table_prix]);

    // Affichage de la confirmation avec le QR code
    echo "<h2>Réservation enregistrée ✅</h2>";
    echo "<p><strong>Nom :</strong> $nom</p>";
    echo "<p><strong>Date :</strong> $date</p>";
    echo "<p><strong>Heure :</strong> $heure</p>";
    echo "<p><strong>Nombre de personnes :</strong> $personnes</p>";
    echo "<p><strong>Table choisie :</strong> $table_prix FCFA</p>";
    echo "<h3>Votre QR code :</h3>";
    echo "<img src='$qrFilename' alt='QR Code'>";
} else {
    echo "<p>Accès interdit 🚫</p>";
}
?>