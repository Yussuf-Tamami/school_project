<?php

$host = 'localhost';
$dbname = 'gestion_scolarite';
$username = 'root';
$password = '';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion reussie!";
} catch (PDOException $e) {
    echo "erreur..." . $e->getMessage();
}


?>

