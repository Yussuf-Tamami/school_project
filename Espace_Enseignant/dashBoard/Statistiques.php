<?php
session_start();
include_once("../../dataBase/connection.php");

if (!isset($_SESSION['id_enseignant'])) {
    header("Location: ../Main.php");
    exit();
}

$id_enseignant = $_SESSION['id_enseignant'];


// Récupérer l'ID de la filière de l'enseignant
$sql_filiere = "SELECT id_filiere FROM enseignants WHERE id_enseignant = ?";
$stmt_filiere = $dba->prepare($sql_filiere);
$stmt_filiere->execute([$id_enseignant]);
$id_filiere = $stmt_filiere->fetchColumn();

// Récupérer tous les étudiants de la filière
$sql_etudiants = "SELECT id_etudiant, nom FROM etudiants WHERE id_filiere = ?";
$stmt_etudiants = $dba->prepare($sql_etudiants);
$stmt_etudiants->execute([$id_filiere]);
$etudiants = $stmt_etudiants->fetchAll();

// Calcul des statistiques
$total_moyenne_filiere = 0;
$etudiants_valides = 0;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Étudiants</title>
</head>
<body>

<h2>Statistiques des Étudiants dans votre Filière</h2>

<table border="1">
    <tr>
        <th>Nom de l'Étudiant</th>
        <th>Moyenne Générale</th>
    </tr>

    <?php
    // Afficher les statistiques des étudiants
    foreach ($etudiants as $etudiant) {
        $id_etudiant = $etudiant['id_etudiant'];

        // Récupérer les notes de l'étudiant pour toutes ses matières
        $sql_notes = "SELECT id_matiere, note FROM evaluations WHERE id_etudiant = ?";
        $stmt_notes = $dba->prepare($sql_notes);
        $stmt_notes->execute([$id_etudiant]);
        $notes = $stmt_notes->fetchAll();

        // Vérifier si l'étudiant a toutes ses notes pour chaque matière
        $notes_par_matiere = [];
        foreach ($notes as $note) {
            $notes_par_matiere[$note['id_matiere']][] = $note['note'];
        }

        // Vérifier si toutes les matières ont 3 notes
        $toutes_les_notes = true;
        foreach ($notes_par_matiere as $matiere => $notes_matiere) {
            if (count($notes_matiere) < 3) {
                $toutes_les_notes = false;
                break;
            }
        }

        if ($toutes_les_notes) {
            // Si l'étudiant a toutes ses notes, calculer la moyenne générale
            $total_notes = 0;
            $nombre_de_notes = 0;

            foreach ($notes_par_matiere as $notes_matiere) {
                $total_notes += array_sum($notes_matiere);
                $nombre_de_notes += count($notes_matiere);
            }

            $moyenne_generale = $total_notes / $nombre_de_notes;

            // Calculer les statistiques de la filière
            $total_moyenne_filiere += $moyenne_generale;
            $etudiants_valides++;
        } else {
            // Si l'étudiant n'a pas toutes ses notes, afficher "N/A"
            $moyenne_generale = 'N/A';
        }

        // Afficher l'étudiant et sa moyenne générale
        echo "<tr>";
        echo "<td>{$etudiant['nom']}</td>";
        echo "<td>$moyenne_generale</td>";
        echo "</tr>";
    }
    ?>

</table>

<p><strong>Statistiques globales de la filière :</strong></p>

<?php
// Calculer la moyenne de la filière
$moyenne_filiere = $etudiants_valides > 0 ? $total_moyenne_filiere / $etudiants_valides : 'N/A';
echo "<p>Moyenne générale de la filière : $moyenne_filiere</p>";

// Calculer le taux de réussite (par exemple, étudiants ayant une moyenne générale >= 10)
$taux_reussite = 0;
foreach ($etudiants as $etudiant) {
    $id_etudiant = $etudiant['id_etudiant'];

    // Récupérer les notes de l'étudiant pour toutes ses matières
    $sql_notes = "SELECT id_matiere, note FROM evaluations WHERE id_etudiant = ?";
    $stmt_notes = $dba->prepare($sql_notes);
    $stmt_notes->execute([$id_etudiant]);
    $notes = $stmt_notes->fetchAll();

    // Vérifier si l'étudiant a toutes ses notes pour chaque matière
    $notes_par_matiere = [];
    foreach ($notes as $note) {
        $notes_par_matiere[$note['id_matiere']][] = $note['note'];
    }

    $toutes_les_notes = true;
    foreach ($notes_par_matiere as $matiere => $notes_matiere) {
        if (count($notes_matiere) < 3) {
            $toutes_les_notes = false;
            break;
        }
    }

    if ($toutes_les_notes) {
        // Calculer la moyenne générale de l'étudiant
        $total_notes = 0;
        $nombre_de_notes = 0;

        foreach ($notes_par_matiere as $notes_matiere) {
            $total_notes += array_sum($notes_matiere);
            $nombre_de_notes += count($notes_matiere);
        }

        $moyenne_generale = $total_notes / $nombre_de_notes;
        
        // Si la moyenne est >= 10, l'étudiant est considéré comme ayant réussi
        if ($moyenne_generale >= 10) {
            $taux_reussite++;
        }
    }
}

$pourcentage_reussite = $etudiants_valides > 0 ? ($taux_reussite / $etudiants_valides) * 100 : 0;
echo "<p>Taux de réussite (moyenne >= 10) : $pourcentage_reussite%</p>";
?>

</body>
</html>
