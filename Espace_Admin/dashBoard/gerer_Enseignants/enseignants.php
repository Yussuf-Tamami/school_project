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
            font-family: 'Poppins', sans-serif;
            background-color:rgb(255, 252, 228);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
    width: 250px;
    background: linear-gradient(180deg, #1b263b, #3a0ca3);
    color: white;
    padding: 20px 0;
    transition: all 0.3s ease;
    position: fixed;
    height: 100%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 100;
}

.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h3 {
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: px;
}

.sidebar-header h3 i {
    font-size: 1.5rem;
}

.sidebar-menu {
    padding: 20px 0;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-menu a:hover, 
.sidebar-menu a.active {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-left: 3px solid #4cc9f0;
}

.sidebar-menu a i {
    margin-right: 10px;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.logout-btn {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
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
<aside class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-graduation-cap"></i> <span>SchoolAdmin</span></h3>
    </div>
    
    <nav class="sidebar-menu">
        <a href="../Main.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Main.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Acceuil</span>
        </a>
        <a href="../gerer_Filieres/filieres.php" class="<?= basename($_SERVER['PHP_SELF']) == 'filieres.php' ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i>
            <span>Filières</span>
        </a>
        <a href="./enseignants.php" class="<?= basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : '' ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Enseignants</span>
        </a>
        <a href="../gerer_Demandes/Demandes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Demandes.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i>
            <span>Demandes</span>
        </a>
        <a href="../pageAccount.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pageAccount.php' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        </a>
    </nav>
    
    <button class="logout-btn" onclick="window.location.href='../../../logOut/logOut.php'">
        <i class="fas fa-sign-out-alt"></i>
        <span>Déconnexion</span>
    </button>
</aside>


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