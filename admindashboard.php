<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['admin'])) {
    header('Location: adminconnexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0) {
        // Récupérer les infos de la réservation + email du client
        $resa = $pdo->prepare(
            "SELECT r.*, u.email, u.nom as client_nom
             FROM reservations r
             LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
             WHERE r.id = ?"
        );
        $resa->execute([$id]);
        $resa = $resa->fetch();

        if ($action === 'valider') {
            $pdo->prepare("UPDATE reservations SET statut = 'validee' WHERE id = ?")->execute([$id]);

            // Email au client
            if ($resa && $resa['email']) {
                $sujet = "Votre reservation a ete validee - YanYan Mood";
                $message = "Bonjour " . $resa['client_nom'] . ",\n\n"
                    . "Votre reservation a ete confirmee et validee par notre equipe.\n\n"
                    . "Details :\n"
                    . "- Date : " . $resa['date_resa'] . "\n"
                    . "- Heure : " . substr($resa['heure_resa'], 0, 5) . "\n"
                    . "- Personnes : " . $resa['nombre'] . "\n"
                    . "- Code : " . $resa['code_reservation'] . "\n\n"
                    . "Presentez votre QR code a l'entree. A bientot !\n\n"
                    . "YanYan Mood Restaurant";
                $headers = "From: noreply@geria.infinityfree.me\r\nContent-Type: text/plain; charset=UTF-8";
                mail($resa['email'], $sujet, $message, $headers);
            }

        } elseif ($action === 'annuler') {
            $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?")->execute([$id]);

            // Email au client
            if ($resa && $resa['email']) {
                $sujet = "Votre reservation a ete annulee - YanYan Mood";
                $message = "Bonjour " . $resa['client_nom'] . ",\n\n"
                    . "Nous vous informons que votre reservation du " . $resa['date_resa']
                    . " a " . substr($resa['heure_resa'], 0, 5)
                    . " a ete annulee par notre equipe.\n\n"
                    . "Vous pouvez faire une nouvelle reservation sur notre site.\n\n"
                    . "Nous nous excusons pour la gene occasionnee.\n\n"
                    . "YanYan Mood Restaurant";
                $headers = "From: noreply@geria.infinityfree.me\r\nContent-Type: text/plain; charset=UTF-8";
                mail($resa['email'], $sujet, $message, $headers);
            }

        } elseif ($action === 'supprimer') {
            $pdo->prepare("DELETE FROM reservations WHERE id = ?")->execute([$id]);
        }
    }
    header('Location: admindashboard.php');
    exit;
}

$filtre = $_GET['filtre'] ?? 'tous';
$where  = '';
if ($filtre === 'en_attente') $where = "WHERE r.statut = 'en_attente'";
elseif ($filtre === 'validee') $where = "WHERE r.statut = 'validee'";
elseif ($filtre === 'annulee') $where = "WHERE r.statut = 'annulee'";

$reservations = $pdo->query(
    "SELECT r.*, u.email, u.telephone
     FROM reservations r
     LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
     $where
     ORDER BY r.created_at DESC"
)->fetchAll();

$total      = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$en_attente = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'")->fetchColumn();
$validees   = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'validee'")->fetchColumn();
$annulees   = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'annulee'")->fetchColumn();

$tables = ['15000' => '15 000 FCFA', '25000' => '25 000 FCFA', '50000' => '50 000 FCFA'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>Admin - Tableau de bord</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-image: linear-gradient(rgba(0,0,0,0.85),rgba(0,0,0,0.85)), url("foodmood.jpg");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            color: white;
            padding: 20px;
        }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .header h1 { font-size: clamp(18px, 4vw, 26px); text-decoration: underline; }
        .header a { background: rgb(52,37,37); color: #ffd9b3; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 13px; }
        .header a:hover { background: rgb(80,60,60); }
        .stats { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
        .stat { background: rgba(255,255,255,0.08); backdrop-filter: blur(5px); border-radius: 10px; padding: 15px 20px; text-align: center; flex: 1; min-width: 120px; }
        .stat .nombre { font-size: 28px; font-weight: bold; }
        .stat .label { font-size: 12px; opacity: 0.75; margin-top: 4px; }
        .stat.attente .nombre { color: #ffd166; }
        .stat.validee .nombre { color: #06d6a0; }
        .stat.annulee .nombre { color: #ef476f; }
        .refresh-info { text-align: right; font-size: 11px; opacity: 0.5; margin-bottom: 15px; }
        .filtres { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filtres a { padding: 7px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; background: rgba(255,255,255,0.1); color: white; }
        .filtres a:hover, .filtres a.actif { background: rgb(52,37,37); color: #ffd9b3; }
        .table-wrap { overflow-x: auto; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; background: rgba(255,255,255,0.05); backdrop-filter: blur(5px); min-width: 700px; }
        thead tr { background: rgba(52,37,37,0.8); }
        th { padding: 12px 10px; text-align: left; font-size: 13px; color: #ffd9b3; }
        td { padding: 11px 10px; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.07); vertical-align: middle; }
        tr:hover td { background: rgba(255,255,255,0.05); }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge.en_attente { background: rgba(255,209,102,0.2); color: #ffd166; border: 1px solid #ffd166; }
        .badge.validee    { background: rgba(6,214,160,0.2);   color: #06d6a0; border: 1px solid #06d6a0; }
        .badge.annulee    { background: rgba(239,71,111,0.2);   color: #ef476f; border: 1px solid #ef476f; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn { padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; color: white; }
        .btn-valider  { background: #06d6a0; color: #000; }
        .btn-annuler  { background: #ffd166; color: #000; }
        .btn-supprimer { background: #ef476f; }
        .btn:hover { opacity: 0.85; }
        tr.nouvelle td { background: rgba(255,209,102,0.07); }
        tr.nouvelle td:first-child { border-left: 3px solid #ffd166; }
        .vide { text-align: center; padding: 40px; opacity: 0.5; font-size: 15px; }
        @media (max-width: 600px) {
            .header { flex-direction: column; align-items: flex-start; }
            .stat { min-width: 100px; padding: 12px; }
            .stat .nombre { font-size: 22px; }
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Tableau de bord — Reservations</h1>
    <a href="admindeconnexion.php">Se deconnecter</a>
</div>

<div class="stats">
    <div class="stat"><div class="nombre"><?= $total ?></div><div class="label">Total</div></div>
    <div class="stat attente"><div class="nombre"><?= $en_attente ?></div><div class="label">En attente</div></div>
    <div class="stat validee"><div class="nombre"><?= $validees ?></div><div class="label">Validees</div></div>
    <div class="stat annulee"><div class="nombre"><?= $annulees ?></div><div class="label">Annulees</div></div>
</div>

<div class="refresh-info">Rafraichissement automatique toutes les 30 secondes</div>

<div class="filtres">
    <a href="?filtre=tous"       class="<?= $filtre === 'tous'       ? 'actif' : '' ?>">Toutes</a>
    <a href="?filtre=en_attente" class="<?= $filtre === 'en_attente' ? 'actif' : '' ?>">En attente (<?= $en_attente ?>)</a>
    <a href="?filtre=validee"    class="<?= $filtre === 'validee'    ? 'actif' : '' ?>">Validees</a>
    <a href="?filtre=annulee"    class="<?= $filtre === 'annulee'    ? 'actif' : '' ?>">Annulees</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Nom</th><th>Email</th><th>Telephone</th>
                <th>Date</th><th>Heure</th><th>Pers.</th><th>Table</th>
                <th>Code</th><th>Statut</th><th>Recu le</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($reservations)): ?>
            <tr><td colspan="12" class="vide">Aucune reservation trouvee</td></tr>
        <?php else: ?>
            <?php foreach ($reservations as $r):
                $est_nouvelle = (time() - strtotime($r['created_at'])) < 300;
            ?>
            <tr class="<?= $est_nouvelle ? 'nouvelle' : '' ?>">
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['nom']) ?><?= $est_nouvelle ? ' NEW' : '' ?></td>
                <td><?= htmlspecialchars($r['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['telephone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['date_resa']) ?></td>
                <td><?= htmlspecialchars(substr($r['heure_resa'], 0, 5)) ?></td>
                <td><?= $r['nombre'] ?></td>
                <td><?= $tables[$r['table_prix']] ?? $r['table_prix'] ?></td>
                <td><code><?= htmlspecialchars($r['code_reservation']) ?></code></td>
                <td><span class="badge <?= $r['statut'] ?>"><?= str_replace('_', ' ', ucfirst($r['statut'])) ?></span></td>
                <td><?= date('d/m H:i', strtotime($r['created_at'])) ?></td>
                <td>
                    <div class="actions">
                        <?php if ($r['statut'] !== 'validee'): ?>
                        <form method="POST"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="valider"><button class="btn btn-valider">Valider</button></form>
                        <?php endif; ?>
                        <?php if ($r['statut'] !== 'annulee'): ?>
                        <form method="POST"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="annuler"><button class="btn btn-annuler">Annuler</button></form>
                        <?php endif; ?>
                        <form method="POST" onsubmit="return confirm('Supprimer cette reservation ?')"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="supprimer"><button class="btn btn-supprimer">Sup.</button></form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
