<?php
    session_start();
    include '../../../dataBase/connection.php';

    if (!isset($_SESSION['nom_etudiant'])) {
        header("Location: ../Main.php");
        exit();
    }

    $id_etudiant = $_SESSION['id_etudiant'];
    $id_filiere = $_SESSION['id_filiere'];

    // Get student grades data
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

    // Calculate class average
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

            $sql_notes = "SELECT note FROM evaluations 
                          WHERE id_etudiant = ? AND id_matiere = ?
                          ORDER BY num_devoir asc";
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

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملاحظاتي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --dark: #1b263b;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --warning: #f61717;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', Arial, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .grades-table th, 
        .grades-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .grades-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .grades-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .grades-table tr:hover {
            background-color: #f1f5ff;
        }

        .average-table {
            width: 100%;
            max-width: 500px;
            margin: 0 auto 30px;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .average-table th, 
        .average-table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .average-table th {
            background-color: var(--secondary);
            color: white;
            font-weight: 600;
        }

        .highlight {
            font-weight: bold;
            color: var(--primary);
        }

        .success {
            color: #28a745;
            font-weight: bold;
        }

        .warning {
            color: var(--warning);
        }

        .subject-name {
            font-weight: 600;
            color: var(--dark);
        }

        .no-data {
            color: #6c757d;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .grades-table th, 
            .grades-table td {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
        }
        
    </style>
</head>
<body>
    <div style="width: 177px; height: 100px; background-color:rgba(246, 231, 231, 0.91);box-shadow:#1b263b 2px 2px 15px;border-radius:8px;position:absolute;bottom:5px; left:5px;align-items:center;">
        <h4 style="position:absolute; left: 25px; top: 25px;font-size: larger; color:#1b263b;">Back to main</h4>
        <a href="../Main/Main.php" style="color: #1b263b; position:absolute; left: 65px; top: 55px; font-size: 1.7em;">
            <i class="fas fa-undo"></i>
        </a>
    </div>
    <div class="container">
        <div class="header">
            <h1>نتائج الطالب</h1>
            <p>عرض جميع النتائج والمعدلات للعام الدراسي</p>
        </div>

        <table class="grades-table">
            <thead>
                <tr>
                    <th>المادة</th>
                    <th>الفرض الأول</th>
                    <th>الفرض الثاني</th>
                    <th>الفرض الثالث</th>
                    <th>المعدل</th>
                </tr>
            </thead>
            <tbody>
                <?php
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
                    echo "<td class='subject-name'>$nom_matiere</td>";
                    echo "<td>".($notes[0] ?: '<span class="no-data">—</span>')."</td>";
                    echo "<td>".($notes[1] ?: '<span class="no-data">—</span>')."</td>";
                    echo "<td>".($notes[2] ?: '<span class="no-data">—</span>')."</td>";
                    echo "<td class='".($moyenne >= 10 ? 'success' : 'warning')."'>".($moyenne !== '—' ? $moyenne : '<span class="no-data">—</span>')."</td>";
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
                ?>
            </tbody>
        </table>

        <table class="average-table">
            <tr>
                <th>المعدل السنوي</th>
                <td class="highlight"><?php echo $mo3adal_l3am ?: '<span class="no-data">غير مكتمل</span>'; ?></td>
            </tr>
            <tr>
                <th>معدل القسم</th>
                <td class="highlight"><?php echo $moyenne_classe; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>