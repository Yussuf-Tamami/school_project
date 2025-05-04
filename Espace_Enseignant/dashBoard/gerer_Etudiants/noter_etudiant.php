<?php
session_start();
include '../../../dataBase/connection.php';

$id_etudiant = $_GET['id_etudiant'] ?? null;
$id_matiere = $_GET['id_matiere'] ?? null;
$id_enseignant = $_SESSION['id_enseignant'] ?? null;

if (!$id_etudiant || !$id_matiere || !$id_enseignant) {
    echo "Paramètres manquants.";
    exit;
}

// Récupérer infos étudiant
$etudiant_stmt = $dba->prepare("SELECT nom, prenom FROM etudiants WHERE id_etudiant = ?");
$etudiant_stmt->execute([$id_etudiant]);
$etudiant = $etudiant_stmt->fetch();

// Récupérer nom de matière
$matiere_stmt = $dba->prepare("SELECT nom_matiere FROM matieres WHERE id_matiere = ?");
$matiere_stmt->execute([$id_matiere]);
$matiere = $matiere_stmt->fetch();
$nom_matiere = $matiere['nom_matiere'] ?? 'Inconnue';

$message = "";

// Récupérer les notes existantes
$notes_stmt = $dba->prepare("SELECT num_devoir, note FROM evaluations WHERE id_etudiant = ? AND id_matiere = ?");
$notes_stmt->execute([$id_etudiant, $id_matiere]);
$notes = $notes_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // num_devoir => note

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_devoir'])) {
    $devoir = intval($_POST['submit_devoir']);
    $note = floatval($_POST['note_' . $devoir]);
    $date = date('Y-m-d');

    // Vérifier si note existe déjà
    $check = $dba->prepare("SELECT * FROM evaluations WHERE id_etudiant = ? AND id_matiere = ? AND num_devoir = ?");
    $check->execute([$id_etudiant, $id_matiere, $devoir]);

    if ($check->fetch()) {
        // Update
        $update = $dba->prepare("UPDATE evaluations SET note = ?, date_evaluation = ? 
                                 WHERE id_etudiant = ? AND id_matiere = ? AND num_devoir = ?");
        $update->execute([$note, $date, $id_etudiant, $id_matiere, $devoir]);
        $message = "<p class='success'>✅ Note modifiée avec succès.</p>";
    } else {
        // Insert
        $insert = $dba->prepare("INSERT INTO evaluations 
                                (id_etudiant, id_enseignant, id_matiere, num_devoir, note, date_evaluation) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$id_etudiant, $id_enseignant, $id_matiere, $devoir, $note, $date]);
        $message = "<p class='success'>✅ Note enregistrée avec succès.</p>";
    }

    // Ajouter notification
    $notification = "La note du devoir $devoir de la matière $nom_matiere a été ajoutée/modifiée.";
    $notif_stmt = $dba->prepare("INSERT INTO notifications (id_etudiant, message) 
                                 VALUES (?, ?)");
    $notif_stmt->execute([$id_etudiant, $notification]);

    // Rafraîchir pour afficher nouvelles notes
    header("Location: noter_etudiant.php?id_etudiant=$id_etudiant&id_matiere=$id_matiere");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Noter un étudiant</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 500px;
        }

        h2 {
            margin-bottom: 20px;
            color: #1877f2;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f3f3f3;
        }

        input {
            width: 60px;
            padding: 5px;
        }

        button {
            background-color: #1877f2;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .success {
            color: green;
            text-align: center;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="card">
    <h2>Noter <?= htmlspecialchars($etudiant['prenom']) . ' ' . htmlspecialchars($etudiant['nom']) ?></h2>
    <?= $message ?>

    <form method="POST">
        <table border="1">
            <tr>
                <th>Devoir</th>
                <th>Note actuelle</th>
                <th>Nouvelle note</th>
                <th>Action</th>
            </tr>

            <?php for ($i = 1; $i <= 3; $i++): ?>
                <tr>
                    <td>Devoir <?= $i ?></td>
                    <td><?= isset($notes[$i]) ? htmlspecialchars($notes[$i]) : '—' ?></td>
                    <td>
                        <input type="number" name="note_<?= $i ?>" step="0.01" min="0" max="20"
                               value="<?= $notes[$i] ?? '' ?>">
                    </td>
                    <td>
                        <button type="submit" name="submit_devoir" value="<?= $i ?>">Enregistrer</button>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
    </form>
</div>
</body>
</html>
