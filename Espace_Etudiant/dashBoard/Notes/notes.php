<?php
    session_start();
    include '../../../dataBase/connection.php';

    if (!isset($_SESSION['nom_etudiant'])) {
        header("Location: ../Main.php");
        exit();
    }

    $id_etudiant = $_SESSION['id_etudiant'];
    $id_filiere = $_SESSION['id_filiere'];

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
            margin-bottom: 20px;
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

        foreach ($matieres as $matiere) {
            $id_matiere = $matiere['id_matiere'];
            $nom_matiere = $matiere['nom_matiere'];

            $sql_notes = "SELECT note FROM evaluations 
                          WHERE id_etudiant = ? AND id_matiere = ?
                          ORDER BY num_devoir asc";
            $stmt_notes = $dba->prepare($sql_notes);
            $stmt_notes->execute([$id_etudiant, $id_matiere]);
            $notes = $stmt_notes->fetchAll(PDO::FETCH_COLUMN);

            while (count($notes) < 3) $notes[] = '';

            $valide_notes = array_filter($notes, fn($n) => $n !== '');
            $moyenne = count($valide_notes) == 3 ? number_format(array_sum($valide_notes)/3, 2) : '—';

            if ($moyenne !== '—') {
                $somme_moyennes += $moyenne;
                $nombre_matieres_validees++;
            }

            echo "<tr>";
            echo "<td>$nom_matiere</td>";
            echo "<td>{$notes[0]}</td>";
            echo "<td>{$notes[1]}</td>";
            echo "<td>{$notes[2]}</td>";
            echo "<td>$moyenne</td>";
            echo "</tr>";
        }

        $mo3adal_l3am = $nombre_matieres_validees == count($matieres) && $nombre_matieres_validees > 0
    ? round($somme_moyennes / $nombre_matieres_validees, 2)
    : '';


            if ($mo3adal_l3am !== '') {
                $adding_moyenne = $dba->prepare('
                    INSERT INTO moyennes_generaux (id_etudiant, id_filiere, moyenne) 
                    VALUES (:id_etudiant, :id_filiere, :moyenne)
                    ON DUPLICATE KEY UPDATE moyenne = :moyenne
                ');
                $adding_moyenne->bindValue(':id_etudiant', $id_etudiant);
                $adding_moyenne->bindValue(':id_filiere', $id_filiere);
                $adding_moyenne->bindValue(':moyenne', $mo3adal_l3am);
                $adding_moyenne->execute();
            }
            
        // Moyenne de classe
        $sql_etudiants_filiere = "SELECT id_etudiant FROM etudiants WHERE id_filiere = (
            SELECT id_filiere FROM etudiants WHERE id_etudiant = ?
        )";
        $stmt_etuds = $dba->prepare($sql_etudiants_filiere);
        $stmt_etuds->execute([$id_etudiant]);
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
            : 'غير مكتمل';
    ?>
</table>

<table style="width: 50%;">
    <tr>
        <td>المعدل السنوي</td>
        <td><?php echo $mo3adal_l3am; ?></td>
    </tr>
    <tr>
        <td>معدل القسم</td>
        <td><?php echo $moyenne_classe; ?></td>
    </tr>
</table>

</body>
</html>