<?php
    session_start();
    include '../../connection.php';

    if (!isset($_SESSION['nom_etudiant'])) {
        header("Location: ../Main.php");
        exit();
    }

    $id_etudiant = $_SESSION['id_etudiant'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ملاحظاتي</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
            direction: rtl;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        td {
            font-size: 16px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h2>نتائج الطالب</h2>

<table>
    <tr>
        <th>المادة</th>
        <th>الفرض الأول</th>
        <th>الفرض الثاني</th>
        <th>الفرض الثالث</th>
        <th>المعدل</th>
    </tr>

    <?php
        // Récupère les matières de l'étudiant avec leur nom depuis la table matieres
        $sql_matieres = "SELECT DISTINCT m.id_matiere, m.nom_matiere 
                         FROM evaluations e 
                         JOIN matieres m ON e.id_matiere = m.id_matiere 
                         WHERE e.id_etudiant = ?";
        $stmt = $dba->prepare($sql_matieres);
        $stmt->execute([$id_etudiant]);
        $matieres = $stmt->fetchAll();

        foreach ($matieres as $matiere) {
            $id_matiere = $matiere['id_matiere'];
            $nom_matiere = $matiere['nom_matiere'];

            // Récupère les 3 notes triées par date
            $sql_notes = "SELECT note FROM evaluations 
                          WHERE id_etudiant = ? AND id_matiere = ?
                          ORDER BY date_evaluation ASC 
                          LIMIT 3";
            $stmt_notes = $dba->prepare($sql_notes);
            $stmt_notes->execute([$id_etudiant, $id_matiere]);
            $notes = $stmt_notes->fetchAll(PDO::FETCH_COLUMN);

            // Compléter les notes manquantes
            while (count($notes) < 3) $notes[] = '';

            // Calcul de la moyenne si 3 notes présentes
            $valide_notes = array_filter($notes, fn($n) => $n !== '');
            $moyenne = count($valide_notes) == 3 ? number_format(array_sum($valide_notes)/3, 2) : '—';

            echo "<tr>";
            echo "<td>$nom_matiere</td>";
            echo "<td>{$notes[0]}</td>";
            echo "<td>{$notes[1]}</td>";
            echo "<td>{$notes[2]}</td>";
            echo "<td>$moyenne</td>";
            echo "</tr>";
        }
    ?>
</table>

</body>
</html>
