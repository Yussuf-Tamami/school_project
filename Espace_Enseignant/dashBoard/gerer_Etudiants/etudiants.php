<?php
session_start();
include '../../../dataBase/connection.php';

$id_enseignant = $_SESSION['id_enseignant'] ?? null;
if (!$id_enseignant) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

// Récupérer la matière et la filière de l'enseignant
$query = $dba->prepare("SELECT m.id_matiere, m.nom_matiere, f.id_filiere, f.nom_filiere
                        FROM matieres m
                        JOIN enseignants e ON m.id_enseignant = e.id_enseignant
                        JOIN filieres f ON f.id_filiere = e.id_filiere
                        WHERE e.id_enseignant = ?");
$query->execute([$id_enseignant]);
$info = $query->fetch();

if (!$info) {
    echo "<p style='color: red; text-align: center;'>Aucune matière ou filière trouvée pour vous.</p>";
    exit;
}

$id_matiere = $info['id_matiere'];
$id_filiere = $info['id_filiere'];
$nom_filiere = $info['nom_filiere'];
$nom_matiere = $info['nom_matiere'];


// les matieres qui port id d'enseignant
$q = $dba->prepare('select * from matieres where id_enseignant = ?');
$q->execute([$id_enseignant]);
$matieres = $q->fetchAll();


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial;
            background-color: #f4f4f4;
            padding: 30px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th:last-child {
            width: 100px;
        }

        tr:hover {
            background-color: #e8f0ff;
        }

        .actions {
            display: none;
        }

        tr:hover .actions {
            display: inline-block;
        }

        .icon {
            cursor: pointer;
            margin-right: 10px;
            font-size: 18px;
            color: #333;
        }

        .icon:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
<?php include'../navbar.php'; ?>
<div class="main">

    <?php foreach($matieres as $matiere): ?>
        <h1><?= htmlspecialchars($matiere['nom_matiere']); ?></h1>
        
        <?php 
    $stmt = $dba->prepare("SELECT * FROM etudiants WHERE id_filiere = ?");
    $stmt->execute([$id_filiere]);
    $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>    

<table>
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th></th> <!-- colonne actions sans titre -->
    </tr>
    
    <?php foreach ($etudiants as $etudiant): ?>
        <tr>
            <td><?= htmlspecialchars($etudiant['nom']) ?></td>
            <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
            <td>
                <span class="actions">
                    <a href="info_etudiant.php?id_etudiant=<?= $etudiant['id_etudiant'] ?>" title="Infos">
                        <i class="fa-solid fa-circle-info icon"></i>
                    </a>
                    <a href="noter.php?id_etudiant=<?= $etudiant['id_etudiant'] ?>&id_matiere=<?= $matiere['id_matiere'] ?>" title="Noter">
                        <i class="fa-solid fa-pen-to-square icon"></i>
                    </a>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endforeach; ?>
</div>

</body>
</html>
