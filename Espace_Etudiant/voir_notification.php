<?php
session_start();
include '../connection.php';

$id_notification = $_GET['id_notification'] ?? null;
$id_etudiant = $_GET['id_etudiant'] ?? null;


if (!$id_notification || !$id_etudiant) {
    echo "Paramètres manquants.";
    exit;
}

// Mark as seen
$update = $dba->prepare("UPDATE notifications SET vu = 1 WHERE id_notification = ?");
$update->execute([$id_notification]);

// Redirect to the target page (for example, page des notes)
header("Location:Notes/notes.php");
exit;
?>
