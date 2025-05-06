<?php
session_start();
include '../../../dataBase/connection.php';

if (!isset($_SESSION['nom_etudiant'])) {
    header("Location: ../Main.php");
    exit();
}
$username = $_SESSION['nom_etudiant'];
$id_etudiant = $_SESSION['id_etudiant'];
$id_filiere = $_SESSION['id_filiere'];
$filiere = $_SESSION['nom_filiere'];

// Récupération des matières de l'étudiant
$sql_matieres = "
    SELECT DISTINCT m.id_matiere, m.nom_matiere
    FROM etudiants e
    JOIN filieres f ON e.id_filiere = f.id_filiere
    JOIN matieres m ON f.id_filiere = m.id_filiere
    WHERE e.id_etudiant = ?
";
$stmt = $dba->prepare($sql_matieres);
$stmt->execute([$id_etudiant]);
$matieres = $stmt->fetchAll();

$somme_moyennes = 0;
$nombre_matieres_validees = 0;

// Calcul de la moyenne de la classe
$sql_etudiants_filiere = "SELECT id_etudiant FROM etudiants WHERE id_filiere = ?";
$stmt_etuds = $dba->prepare($sql_etudiants_filiere);
$stmt_etuds->execute([$id_filiere]);
$etudiants_filiere = $stmt_etuds->fetchAll(PDO::FETCH_COLUMN);

$total_moyennes = 0;
$etudiants_valides = 0;
$classe_valide = true;

foreach ($etudiants_filiere as $id_etud) {
    $stmt_mat = $dba->prepare($sql_matieres);
    $stmt_mat->execute([$id_etud]);
    $matieres_etud = $stmt_mat->fetchAll();

    $somme = 0;
    $nb = 0;

    foreach ($matieres_etud as $matiere) {
        $id_matiere = $matiere['id_matiere'];
        $sql_notes = "SELECT note FROM evaluations 
                      WHERE id_etudiant = ? AND id_matiere = ?
                      ORDER BY num_devoir ASC";
        $stmt_note = $dba->prepare($sql_notes);
        $stmt_note->execute([$id_etud, $id_matiere]);
        $notes = $stmt_note->fetchAll(PDO::FETCH_COLUMN);

        $valid = array_filter($notes, fn($n) => $n !== '');
        if (count($valid) == 3) {
            $somme += array_sum($valid) / 3;
            $nb++;
        } else {
            $classe_valide = false;
            break 2;
        }
    }

    if ($nb > 0) {
        $total_moyennes += $somme / $nb;
        $etudiants_valides++;
    }
}

$moyenne_classe = $classe_valide && $etudiants_valides > 0
    ? number_format($total_moyennes / $etudiants_valides, 2)
    : 'Incomplet';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Résultats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(219, 217, 217);
            margin: 0;
            padding: 20px;
            direction: ltr;
        }

        .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            color: #1b263b;
        }

        .header p {
            color: #555;
        }

        .grades-table,
        .average-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .grades-table th,
        .grades-table td,
        .average-table th,
        .average-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        .grades-table th {
            background-color: #1b263b;
            color: white;
        }

        .average-table th {
            background-color: #f0f0f0;
            color: #333;
            text-align: left;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .warning {
            color: red;
            font-weight: bold;
        }

        .highlight {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .no-data {
            color: #aaa;
        }

        .subject-name {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="user-info">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($username) ?>&background=random" alt="Etudiant" style="border-radius: 50%; scale: .7;">
        <span style="font-weight: bold;"><?php echo $username . " | "?></span> 
        <span class="filiere-badge"><?php echo $filiere[0]; ?></span>
        </div>
    <br><br>
    <div style="width: 177px; height: 100px; background-color:rgba(246, 231, 231, 0.91); box-shadow:#1b263b 2px 2px 15px; border-radius:8px; position:absolute; bottom:25px; left:25px; align-items:center;text-align:center;">
        <h4 style=" font-size: larger; color:#1b263b;">Retour à l'accueil</h4>
        <a href="../Main/Main.php" style="color: #1b263b; position:absolute; left: 65px; top: 55px; font-size: 1.7em;">
            <i class="fas fa-undo"></i>
        </a>
    </div>

    <div class="container">
        <div class="header">
            <h1>Mes Résultats</h1>
            <p>Affichage des notes et moyennes de l'année scolaire</p>
        </div>

        <table class="grades-table">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Devoir 1</th>
                    <th>Devoir 2</th>
                    <th>Devoir 3</th>
                    <th>Moyenne</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($matieres as $matiere) {
                    $id_matiere = $matiere['id_matiere'];
                    $nom_matiere = $matiere['nom_matiere'];

                    $sql_notes = "SELECT note FROM evaluations 
                                  WHERE id_etudiant = ? AND id_matiere = ?
                                  ORDER BY num_devoir ASC";
                    $stmt_notes = $dba->prepare($sql_notes);
                    $stmt_notes->execute([$id_etudiant, $id_matiere]);
                    $notes = $stmt_notes->fetchAll(PDO::FETCH_COLUMN);

                    while (count($notes) < 3) $notes[] = '';

                    $valid_notes = array_filter($notes, fn($n) => $n !== '');
                    $moyenne = count($valid_notes) == 3 ? number_format(array_sum($valid_notes) / 3, 2) : '—';

                    if ($moyenne !== '—') {
                        $somme_moyennes += $moyenne;
                        $nombre_matieres_validees++;
                    }

                    echo "<tr>";
                    echo "<td class='subject-name'>$nom_matiere</td>";
                    echo "<td>".($notes[0] !== '' ? $notes[0] : '<span class="no-data">—</span>')."</td>";
                    echo "<td>".($notes[1] !== '' ? $notes[1] : '<span class="no-data">—</span>')."</td>";
                    echo "<td>".($notes[2] !== '' ? $notes[2] : '<span class="no-data">—</span>')."</td>";
                    echo "<td class='".($moyenne >= 10 ? 'success' : 'warning')."'>".($moyenne !== '—' ? $moyenne : '<span class="no-data">—</span>')."</td>";
                    echo "</tr>";
                }

                $moyenne_generale = ($nombre_matieres_validees == count($matieres) && $nombre_matieres_validees > 0)
                    ? round($somme_moyennes / $nombre_matieres_validees, 2)
                    : '';

                if ($moyenne_generale !== '') {
                    $adding_moyenne = $dba->prepare('
                        INSERT INTO moyennes_generaux (id_etudiant, id_filiere, moyenne) 
                        VALUES (:id_etudiant, :id_filiere, :moyenne)
                        ON DUPLICATE KEY UPDATE moyenne = :moyenne
                    ');
                    $adding_moyenne->execute([
                        'id_etudiant' => $id_etudiant,
                        'id_filiere' => $id_filiere,
                        'moyenne' => $moyenne_generale
                    ]);
                }
                ?>
            </tbody>
        </table>

        <table class="average-table">
            <tr>
                <th>Moyenne Générale</th>
                <td class="highlight"><?php echo $moyenne_generale ?: '<span class="no-data">Incomplète</span>'; ?></td>
            </tr>
            <tr>
                <th>Moyenne de la Classe</th>
                <td class="highlight"><?php echo $moyenne_classe; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
