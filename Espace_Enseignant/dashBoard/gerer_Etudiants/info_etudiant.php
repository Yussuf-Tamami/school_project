<?php
session_start();
include '../../../dataBase/connection.php';

$id_etudiant = $_GET['id_etudiant'];
$q = $dba->prepare('select  * from etudiants where id_etudiant = ?');
$q->execute([$id_etudiant]);
$info_etudiant = $q->fetch();

$query = $dba->prepare('select nom_filiere from filieres where id_filiere = ?');
$query->execute([$info_etudiant['id_filiere']]);
$filiere = $query->fetch()['nom_filiere'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($info_etudiant['nom']); ?></title>
    <style>
       body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f5;
            margin: 0;
            padding: 0;
            color: #333;
        }

        section {
            max-width: 700px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border-left: 6px solid #3498db;
        }

        section h1 {
            color: #3498db;
            font-size: 1.8em;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        p {
            margin: 12px 0;
            font-size: 1.1em;
            line-height: 1.6;
        }

        strong {
            color: #2980b9;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 1em;
            color: #7f8c8d;
        }

        footer a {
            color: #3498db;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section>
        <h1>Informations de l'Étudiant</h1>
        <p><strong>Nom : </strong> <?= htmlspecialchars($info_etudiant['nom'])?></p>
        <p><strong>Prénom : </strong> <?= htmlspecialchars($info_etudiant['prenom'])?></p>
        <p><strong>Date de naissance : </strong> <?= htmlspecialchars($info_etudiant['date_naissance'])?></p>
        <p><strong>Numéro d'Étudiant : </strong> <?= htmlspecialchars($info_etudiant['id_etudiant'])?></p>
        <p><strong>Filière : </strong> <?= htmlspecialchars($filiere)?> </p>
    </section>
    <footer>
        <p>Retourner à <a href="../Main.php">l'espace enseignant</a>.</p>
    </footer>
</body>
</html>
