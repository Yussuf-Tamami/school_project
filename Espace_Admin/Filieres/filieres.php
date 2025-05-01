<?php
session_start();
require_once '../../connection.php';

// Vérifier l'authentification admin
if (!isset($_SESSION['admin_name'])) {
    header('Location: Login.php');
    exit;
}

// Récupérer la structure hiérarchique
try {
    // Récupérer tous les départements
    $stmt = $dba->query("SELECT * FROM departements ORDER BY nom_departement");
    $departements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tableau pour organiser les départements, filières et matières
    $structure = [];

    // Pour chaque département, récupérer ses filières
    foreach ($departements as $departement) {
        $structure[$departement['id_departement']] = [
            'id_departement' => $departement['id_departement'],
            'nom_departement' => $departement['nom_departement'],
            'filieres' => []
        ];

        $stmt = $dba->prepare("
            SELECT f.* 
            FROM filieres f 
            WHERE f.id_departement = ? 
            ORDER BY f.nom_filiere
        ");
        $stmt->execute([$departement['id_departement']]);
        $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque filière, récupérer ses matières
        foreach ($filieres as $filiere) {
            $structure[$departement['id_departement']]['filieres'][] = [
                'id_filiere' => $filiere['id_filiere'],
                'nom_filiere' => $filiere['nom_filiere'],
                'matieres' => []
            ];

            $stmt = $dba->prepare("
                SELECT m.*, e.nom AS nom_enseignant, e.prenom AS prenom_enseignant
                FROM matieres m
                LEFT JOIN enseignants e ON m.id_enseignant = e.id_enseignant
                WHERE m.id_filiere = ?
                ORDER BY m.nom_matiere
            ");
            $stmt->execute([$filiere['id_filiere']]);
            $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajouter les matières à la filière correspondante
            foreach ($matieres as $matiere) {
                $structure[$departement['id_departement']]['filieres'][count($structure[$departement['id_departement']]['filieres'])-1]['matieres'][] = [
                    'id_matiere' => $matiere['id_matiere'],
                    'nom_matiere' => $matiere['nom_matiere'],
                    'nom_enseignant' => $matiere['nom_enseignant'],
                    'prenom_enseignant' => $matiere['prenom_enseignant']
                ];
            }
        }
    }
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Filières</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color:rgb(255, 244, 198);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .departement {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .departement-title {
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .filiere {
            margin-left: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .filiere-title {
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .matiere {
            margin-left: 20px;
            padding: 10px;
            background-color: #fff;
            border-radius: 3px;
            margin-bottom: 8px;
            border: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .matiere-info {
            flex: 1;
        }
        .matiere-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .edit-btn {
            background-color: #f39c12;
        }
        .edit-btn:hover {
            background-color: #e67e22;
        }
        .add-btn {
            background-color: #2ecc71;
            padding: 8px 15px;
        }
        .add-btn:hover {
            background-color: #27ae60;
        }
        .no-data {
            color: #7f8c8d;
            font-style: italic;
            padding: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Gestion des Filières et Matières</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php foreach ($structure as $departement): ?>
            <div class="departement">
                <div class="departement-title">
                    <span><?= htmlspecialchars($departement['nom_departement']) ?></span>
                    <a href="add_filiere.php?id_departement=<?= htmlspecialchars($departement['id_departement'])?>" class="action-btn add-btn">
                        <i class="fas fa-plus"></i> Ajouter Filière
                    </a>
                </div>

                <?php if (!empty($departement['filieres'])): ?>
                    <?php foreach ($departement['filieres'] as $filiere): ?>
                        <div class="filiere">
                            <div class="filiere-title">
                                <span><?= htmlspecialchars($filiere['nom_filiere']) ?></span>
                                <a href="add_matiere.php?id_filiere=<?= $filiere['id_filiere'] ?>" class="action-btn add-btn">
                                    <i class="fas fa-plus"></i> Ajouter Matière
                                </a>
                            </div>

                            <?php if (!empty($filiere['matieres'])): ?>
                                <?php foreach ($filiere['matieres'] as $matiere): ?>
                                    <div class="matiere">
                                        <div class="matiere-info">
                                            <?= htmlspecialchars($matiere['nom_matiere']) ?>
                                            <?php if ($matiere['nom_enseignant']): ?>
                                                - Enseignant: <?= htmlspecialchars($matiere['prenom_enseignant'] . ' ' . $matiere['nom_enseignant']) ?>
                                            <?php else: ?>
                                                <span style="color:#e74c3c">(Aucun enseignant assigné)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="matiere-actions">
                                            <a href="edit_matiere.php?id_matiere=<?= $matiere['id_matiere'] ?>" class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-data">Aucune matière dans cette filière</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">Aucune filière dans ce département</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
