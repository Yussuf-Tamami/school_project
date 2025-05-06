<?php
session_start();
include '../../../dataBase/connection.php';

if (!isset($_SESSION['nom_etudiant'])) {
    header("Location: ../Main.php");
    exit();
}

$id_etudiant = $_SESSION['id_etudiant'];
$nom_etudiant =  htmlspecialchars($_SESSION['nom_etudiant']);
$id_filiere = $_SESSION['id_filiere'];
$filiere = $_SESSION['nom_filiere'];

$q = $dba->prepare('select * from etudiants where id_etudiant = ?');
$q->execute([$id_etudiant]);
$result= $q->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --accent: #f39c12;
            --dark: #1a252f;
            --light: #ecf0f1;
            --sidebar-width: 250px;
            --sidebar-collapsed: 80px;
            --warning: #f61717;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            color: #333;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: var(--dark);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 5px solid var(--secondary);
        }

        .profile-section {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary);
            margin-left: 30px;
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h2 {
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 1.8rem;
        }

        .profile-info p {
            color: var(--secondary);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .info-card {
            background-color: white;
            border-radius: 5px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-top: 4px solid var(--secondary);
        }

        .info-card h3 {
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-size: 1.3rem;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
            display: block;
        }

        .info-value {
            padding: 10px;
            background-color: var(--light);
            border-radius: 3px;
            font-size: 1.1rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark);
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            text-align: center;
            font-weight: bold;
        }

        .action-btn:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .action-btn i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #2c3e50, #1b263b);
            color: white;
            transition: width 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar.collapsed .logo-text,
        .sidebar.collapsed nav span {
            display: none;
        }

        .logo {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .logo-text {
            font-weight: 600;
            font-size: 1.2rem;
            transition: opacity 0.3s;
        }

        nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        nav a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        nav a:hover, nav a.active {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        nav a i {
            font-size: 1.2rem;
            margin-right: 15px;
            width: 24px;
            text-align: center;
        }

        .logout-btn {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn a {
            display: flex;
            align-items: center;
            color: var(--warning);
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .logout-btn a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn i {
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            body {
                margin-left: var(--sidebar-collapsed);
            }
            
            .sidebar {
                width: var(--sidebar-collapsed);
            }
            
            .sidebar .logo-text,
            .sidebar nav span {
                display: none;
            }
            
            .profile-section {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-pic {
                margin-left: 0;
                margin-bottom: 20px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="../../../Images/logo.png" alt="Logo Ecole" />
            <span class="logo-text">Espace Étudiant</span>
        </div>
        
        <nav>
            <a href="./Main.php">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            <a href="./Account.php" class="active">
                <i class="fas fa-user"></i>
                <span>Mon Profil</span>
            </a>
            <a href="../Notes/notes.php">
                <i class="fas fa-book"></i>
                <span>Mes Notes</span>
            </a>
            <a href="../attestation.php">
                <i class="fas fa-download"></i>
                <span>Attestation</span>
            </a>
            <a href="../relver_note.php">
                <i class="fas fa-file-alt"></i>
                <span>Relevé de Notes</span>
            </a>
            <a href="../bulletin.php">
                <i class="fas fa-file-pdf"></i>
                <span>Bulletin</span>
            </a>
        </nav>
        
        <div class="logout-btn">
            <a href="../../../logOut/logOut.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <header>
            <h1>Mon Espace Étudiant</h1>
            <p>Gérez votre compte et accédez à vos documents</p>
        </header>

        <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/219/219986.png" alt="Avatar etudiant" class="profile-pic" style="margin-right: 2em;" >
                    <div class="profile-info">
                <h2><?php echo $nom_etudiant; ?></h2>
                <p><?php echo $filiere[0]; ?></p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3><i class="fas fa-id-card"></i> Informations Personnelles</h3>
                <div class="info-item">
                    <span class="info-label">Nom complet</span>
                    <div class="info-value"><?php echo $nom_etudiant; ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <div class="info-value"><?php echo $result['email']?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">Date de naissance</span>
                    <div class="info-value"><?php echo $result['date_naissance'] ?></div>
                </div>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-university"></i> Informations Académiques</h3>
                <div class="info-item">
                    <span class="info-label">Filière</span>
                    <div class="info-value"><?php echo $filiere[0]; ?></div>
                </div>
                <div class="info-item">
                    <span class="info-label">Numéro d'etudiant</span>
                    <div class="info-value"><?= $id_etudiant?></div>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="../Notes/notes.php" class="action-btn">
                <i class="fas fa-chart-bar"></i>
                Mes Notes
            </a>
            <a href="../attestation.php" class="action-btn">
                <i class="fas fa-file-download"></i>
                Attestation
            </a>
            <a href="../relver_note.php" class="action-btn">
                <i class="fas fa-file-alt"></i>
                Relevé de Notes
            </a>
            <a href="../bulletin.php" class="action-btn">
                <i class="fas fa-file-pdf"></i>
                Bulletin
            </a>
        </div>
    </div>

    <script>
        // Toggle sidebar collapse
        document.addEventListener('DOMContentLoaded', function() {
            // You can add JavaScript here to handle sidebar toggling
            // For example, add a button to toggle the sidebar
        });
    </script>
</body>
</html>