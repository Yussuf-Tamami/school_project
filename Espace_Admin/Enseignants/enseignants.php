<?php
session_start();
require_once '../../connection.php';

// Check admin authentication
if (!isset($_SESSION['admin_name'])) {
    header('Location: Login.php');
    exit;
}

// Fetch all teachers
try {
    $stmt = $dba->prepare("
        SELECT id_enseignant, nom, prenom, email, specialite 
        FROM enseignants 
        ORDER BY nom, prenom
    ");
    $stmt->execute();
    $enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Enseignants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: rgba(246, 234, 180, 0.88);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .enseignants-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .enseignants-table th, .enseignants-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .enseignants-table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }
        .enseignants-table tr:hover {
            background-color:rgba(192, 251, 255, 0.8);
            
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
        .info-btn {
            background-color: #3498db;
        }
        .edit-btn {
            background-color: #f39c12;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .error-message {
            color: #e74c3c;
            padding: 10px;
            background: #fadbd8;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Liste des Enseignants</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <table class="enseignants-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Spécialité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enseignants as $enseignant): ?>
                    <tr>
                        <td><?= htmlspecialchars($enseignant['nom']) ?></td>
                        <td><?= htmlspecialchars($enseignant['prenom']) ?></td>
                        <td><?= htmlspecialchars($enseignant['email']) ?></td>
                        <td><?= htmlspecialchars($enseignant['specialite']) ?></td>
                        <td>
                            <a href="enseignant_info.php?id=<?= $enseignant['id_enseignant'] ?>" 
                               class="action-btn info-btn">
                                <i class="fas fa-info-circle"></i> Détails
                            </a>
                            <a href="modify_enseignant.php?id=<?= $enseignant['id_enseignant'] ?>" 
                               class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>