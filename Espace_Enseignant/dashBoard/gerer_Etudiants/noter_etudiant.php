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

$etudiant_stmt = $dba->prepare("SELECT nom, prenom FROM etudiants WHERE id_etudiant = ?");
$etudiant_stmt->execute([$id_etudiant]);
$etudiant = $etudiant_stmt->fetch();

$matiere_stmt = $dba->prepare("SELECT nom_matiere FROM matieres WHERE id_matiere = ?");
$matiere_stmt->execute([$id_matiere]);
$matiere = $matiere_stmt->fetch();
$nom_matiere = $matiere['nom_matiere'] ?? 'Inconnue';

$message = "";

$notes_stmt = $dba->prepare("SELECT num_devoir, note FROM evaluations WHERE id_etudiant = ? AND id_matiere = ?");
$notes_stmt->execute([$id_etudiant, $id_matiere]);
$notes = $notes_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d');

    if (isset($_POST['submit_devoir'])) {
        $devoir = intval($_POST['submit_devoir']);
        $note = floatval($_POST['note_' . $devoir]);

        $check = $dba->prepare("SELECT * FROM evaluations WHERE id_etudiant = ? AND id_matiere = ? AND num_devoir = ?");
        $check->execute([$id_etudiant, $id_matiere, $devoir]);

        if ($check->fetch()) {
            $update = $dba->prepare("UPDATE evaluations SET note = ?, date_evaluation = ? 
                                     WHERE id_etudiant = ? AND id_matiere = ? AND num_devoir = ?");
            $update->execute([$note, $date, $id_etudiant, $id_matiere, $devoir]);
            $message = "<p class='success'>✅ Note modifiée avec succès.</p>";
        } else {
            $insert = $dba->prepare("INSERT INTO evaluations 
                                    (id_etudiant, id_enseignant, id_matiere, num_devoir, note, date_evaluation) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$id_etudiant, $id_enseignant, $id_matiere, $devoir, $note, $date]);
            $message = "<p class='success'>✅ Note enregistrée avec succès.</p>";
        }

        $notification = "La note du devoir $devoir de la matière $nom_matiere a été ajoutée/modifiée.";
        $notif_stmt = $dba->prepare("INSERT INTO notifications (id_etudiant, message) VALUES (?, ?)");
        $notif_stmt->execute([$id_etudiant, $notification]);

    } elseif (isset($_POST['delete_devoir'])) {
        $devoir = intval($_POST['delete_devoir']);
        $delete = $dba->prepare("DELETE FROM evaluations WHERE id_etudiant = ? AND id_matiere = ? AND num_devoir = ?");
        $delete->execute([$id_etudiant, $id_matiere, $devoir]);

        $notification = "La note du devoir $devoir de la matière $nom_matiere a été supprimée.";
        $notif_stmt = $dba->prepare("INSERT INTO notifications (id_etudiant, message) VALUES (?, ?)");
        $notif_stmt->execute([$id_etudiant, $notification]);

        $message = "<p class='success'>🗑️ Note supprimée avec succès.</p>";
    }

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
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg,rgb(220, 234, 253),rgb(240, 236, 220));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            background: #fff;
            padding: 50px 60px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 900px;
        }

        h2 {
            font-size: 36px;
            color: #2b6cb0;
            text-align: center;
            margin-bottom: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 18px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 16px;
            text-align: center;
        }

        th {
            background-color: #ebf4ff;
            color: #2c5282;
        }

        input[type="number"] {
            padding: 10px;
            width: 80px;
            font-size: 16px;
            text-align: center;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn {
            padding: 10px 16px;
            font-size: 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-save {
            background-color: #38a169;
            color: white;
        }

        .btn-save:hover {
            background-color: #2f855a;
        }

        .btn-delete {
            background-color: #e53e3e;
            color: white;
            margin-left: 8px;
        }

        .btn-delete:hover {
            background-color: #c53030;
        }

        .success, .error {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        @media screen and (max-width: 700px) {
            .container {
                padding: 30px;
            }

            h2 {
                font-size: 28px;
            }

            table, th, td {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Noter : <?= htmlspecialchars($etudiant['prenom']) . ' ' . htmlspecialchars($etudiant['nom']) ?></h2>

    <?= $message ?>

    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>📚 Devoir</th>
                    <th>Note actuelle</th>
                    <th>Nouvelle note</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <tr>
                    <td>Devoir <?= $i ?></td>
                    <td><?= isset($notes[$i]) ? htmlspecialchars($notes[$i]) : '—' ?></td>
                    <td>
                        <input type="number" step="0.01" min="0" max="20"
                               name="note_<?= $i ?>" value="<?= $notes[$i] ?? '' ?>">
                    </td>
                    <td>
                        <button class="btn btn-save" type="submit" name="submit_devoir" value="<?= $i ?>">💾 Enregistrer</button>
                        <?php if (isset($notes[$i])): ?>
                            <button class="btn btn-delete" type="submit" name="delete_devoir" value="<?= $i ?>">🗑️ Supprimer</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </form>
</div>

</body>
</html>
