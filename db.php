<?php
// Connexion centralisée à la base de données
// Modifie ces valeurs si besoin (host, user, password sont les valeurs par défaut de WAMP)

$db_host = 'localhost';
$db_name = 'app_restauration';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données. Veuillez réessayer plus tard.');
}
