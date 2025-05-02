<?php
require_once '../../dataBase/connection.php';

if (isset($_GET['id_filiere'])) {
    $id_filiere = $_GET['id_filiere'];

    $stmt = $dba->prepare("SELECT id_matiere, nom_matiere FROM matieres WHERE id_filiere = ?");
    $stmt->execute([$id_filiere]);
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($matieres);
}
?>