<?php
session_start();
require_once '../../../dataBase/connection.php';

// Check admin authentication
if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
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
        :root {
            --primary:rgb(44, 66, 165);
            --primary-dark:rgb(29, 64, 122);
            --secondary:rgb(35, 64, 152);
            --accent:rgb(27, 75, 138);
            --danger: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
        }

        .main {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed);
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        h1 {
            color: var(--dark);
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
        }

        .enseignants-table {
            width: 100%;
            border-collapse: collapse;
            background:  rgba(207, 206, 206, 0.65);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            overflow: hidden;
        }

        .enseignants-table th, 
        .enseignants-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .enseignants-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .enseignants-table tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-right: 8px;
        }

        .info-btn {
            background-color: var(--accent);
        }

        .edit-btn {
            background-color: var(--warning);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .action-btn i {
            font-size: 14px;
        }

        .error-message {
            color: var(--danger);
            padding: 15px;
            background: rgba(247, 37, 133, 0.1);
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid var(--danger);
        }

        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 20px 15px;
            }
            
            .sidebar:hover ~ .main,
            .sidebar.expanded ~ .main {
                margin-left: 0;
            }
            
            .enseignants-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main">
        <div class="container">
            <h1><i class="fas fa-chalkboard-teacher"></i> Liste des Enseignants</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
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
                                <a href="info_enseignant.php?id=<?= $enseignant['id_enseignant'] ?>" 
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
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.sidebar');
        
        // Keep sidebar expanded when clicking menu items
        sidebar.addEventListener('click', function(e) {
            if (e.target.closest('.menu-item')) {
                this.classList.add('expanded');
            }
        });
        
        // Handle hover
        sidebar.addEventListener('mouseenter', function() {
            this.classList.add('expanded');
        });
        
        // Collapse when mouse leaves if not clicked
        sidebar.addEventListener('mouseleave', function() {
            if (!this.classList.contains('keep-expanded')) {
                this.classList.remove('expanded');
            }
        });
    });
    </script>
</body>
</html>