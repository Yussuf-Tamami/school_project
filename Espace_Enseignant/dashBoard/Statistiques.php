<?php
session_start();
include_once("../../dataBase/connection.php");

if (!isset($_SESSION['id_enseignant'])) {
    header("Location: ../Main.php");
    exit();
}

$id_enseignant = $_SESSION['id_enseignant'];

// Récupérer la filière
$sql_filiere = "SELECT id_filiere FROM enseignants WHERE id_enseignant = ?";
$stmt = $dba->prepare($sql_filiere);
$stmt->execute([$id_enseignant]);
$id_filiere = $stmt->fetchColumn();

// Récupérer tous les étudiants de la filière
$sql_etudiants = "SELECT id_etudiant, nom FROM etudiants WHERE id_filiere = ?";
$stmt = $dba->prepare($sql_etudiants);
$stmt->execute([$id_filiere]);
$etudiants = $stmt->fetchAll();

// Récupérer les moyennes des étudiants depuis la table moyennes_generaux
$sql_moyennes = "SELECT id_etudiant, moyenne FROM moyennes_generaux WHERE id_filiere = ?";
$stmt = $dba->prepare($sql_moyennes);
$stmt->execute([$id_filiere]);
$moyennes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id_etudiant => moyenne

$etudiants_valides = [];
$total_moyenne_filiere = 0;
$nb_reussite = 0;
$meilleur_etudiant = null;
$meilleure_moyenne = -1;

// Vérifier les conditions pour chaque étudiant
foreach ($etudiants as $etudiant) {
    $id = $etudiant['id_etudiant'];
    $nom = $etudiant['nom'];

    // 1. Vérifier s’il a une moyenne dans la table
    if (!array_key_exists($id, $moyennes) || $moyennes[$id] === null) {
        continue;
    }

    // 2. Vérifier s’il a au moins 3 notes pour chaque matière
    $sql_notes = "SELECT id_matiere, note FROM evaluations WHERE id_etudiant = ?";
    $stmt = $dba->prepare($sql_notes);
    $stmt->execute([$id]);
    $notes = $stmt->fetchAll();

    $notes_par_matiere = [];
    foreach ($notes as $note) {
        $notes_par_matiere[$note['id_matiere']][] = $note['note'];
    }

    $matiere_incomplete = false;
    foreach ($notes_par_matiere as $notes_matiere) {
        if (count($notes_matiere) < 3) {
            $matiere_incomplete = true;
            break;
        }
    }

    if ($matiere_incomplete) {
        continue;
    }

    // Si tout est OK, on ajoute à la liste des valides
    $etudiants_valides[] = [
        'nom' => $nom,
        'moyenne' => $moyennes[$id]
    ];

    $total_moyenne_filiere += $moyennes[$id];

    if ($moyennes[$id] >= 10) {
        $nb_reussite++;
    }

    if ($moyennes[$id] > $meilleure_moyenne) {
        $meilleure_moyenne = $moyennes[$id];
        $meilleur_etudiant = $nom;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des Étudiants</title>
</head>
<body>

<h2>Statistiques des Étudiants de la Filière</h2>

<?php if (count($etudiants_valides) < count($etudiants)): ?>
    <p><strong>Le semestre n’est pas encore terminé.</strong></p>
<?php else: ?>
    <table border="1">
        <tr>
            <th>Nom de l'Étudiant</th>
            <th>Moyenne Générale</th>
        </tr>
        <?php foreach ($etudiants_valides as $etudiant): ?>
            <tr>
                <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                <td><?= number_format($etudiant['moyenne'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Moyenne Générale de la Filière:</strong> <?= number_format($total_moyenne_filiere / count($etudiants_valides), 2) ?></p>
    <p><strong>Taux de Réussite (moyenne ≥ 10):</strong> <?= number_format(($nb_reussite / count($etudiants_valides)) * 100, 2) ?>%</p>
    <p><strong>Meilleur Étudiant:</strong> <?= htmlspecialchars($meilleur_etudiant) ?> (<?= number_format($meilleure_moyenne, 2) ?>)</p>
<?php endif; ?>

</body>
</html>
