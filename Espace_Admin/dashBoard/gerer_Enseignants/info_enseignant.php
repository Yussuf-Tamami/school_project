<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: enseignants.php');
    exit;
}

try {
    $stmt = $dba->prepare("
        SELECT e.*, f.nom_filiere 
        FROM enseignants e
        JOIN filieres f ON e.id_filiere = f.id_filiere
        WHERE e.id_enseignant = ?
    ");
    $stmt->execute([$id]);
    $enseignant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Enseignants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .filiere-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .filiere-title {
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .enseignants-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        .enseignant-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            position: relative;
            transition: all 0.3s;
        }
        .enseignant-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        .enseignant-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .enseignant-email {
            color: #666;
            font-size: 14px;
        }
        .action-icons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
        }
        .enseignant-card:hover .action-icons {
            display: flex;
            gap: 8px;
        }
        .action-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .info-icon {
            background-color: #3498db;
            color: white;
        }
        .modify-icon {
            background-color: #f39c12;
            color: white;
        }
        .info-icon:hover, .modify-icon:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Détails de l'Enseignant</h1>
        
        <?php if ($enseignant): ?>
            <div class="detail-card">
                <div class="detail-row">
                    <span class="detail-label">Nom complet:</span>
                    <span><?= htmlspecialchars($enseignant['prenom'] . ' ' . htmlspecialchars($enseignant['nom'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span><?= htmlspecialchars($enseignant['email']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Filière:</span>
                    <span><?= htmlspecialchars($enseignant['nom_filiere']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Spécialité:</span>
                    <span><?= htmlspecialchars($enseignant['specialite'] ?? 'Non spécifiée') ?></span>
                </div>
                <!-- Ajoutez d'autres champs selon votre table enseignants -->
                
                <a href="enseignants.php" class="back-btn">Retour à la liste</a>
            </div>
        <?php else: ?>
            <p>Enseignant non trouvé</p>
        <?php endif; ?>
    </div>
</body>
</html>