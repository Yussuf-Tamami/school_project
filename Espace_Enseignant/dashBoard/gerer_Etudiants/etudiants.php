<?php
session_start();
include '../../../dataBase/connection.php';

$id_enseignant = $_SESSION['id_enseignant'] ?? null;
if (!$id_enseignant) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

// Récupérer la matière et la filière de l'enseignant
$query = $dba->prepare("SELECT m.id_matiere, m.nom_matiere, f.id_filiere, f.nom_filiere
                        FROM matieres m
                        JOIN enseignants e ON m.id_enseignant = e.id_enseignant
                        JOIN filieres f ON f.id_filiere = e.id_filiere
                        WHERE e.id_enseignant = ?");
$query->execute([$id_enseignant]);
$info = $query->fetch();

if (!$info) {
    echo "<p style='color: red; text-align: center;'>Aucune matière ou filière trouvée pour vous.</p>";
    exit;
}

$id_matiere = $info['id_matiere'];
$id_filiere = $info['id_filiere'];
$nom_filiere = $info['nom_filiere'];
$nom_matiere = $info['nom_matiere'];

// les matieres qui port id d'enseignant
$q = $dba->prepare('select * from matieres where id_enseignant = ?');
$q->execute([$id_enseignant]);
$matieres = $q->fetchAll();

$nom_enseignant = $_SESSION['nom_enseignant'] ?? 'Enseignant';
$email_enseignant = $_SESSION['email_enseignant'] ?? 'unknown@unknown.unknown';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Étudiants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --dark: #1b263b;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --warning:rgb(247, 37, 37);
            --glass: rgba(255, 255, 255, 0.87);
            --glass-border: rgba(255, 255, 255, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0e5ec,rgb(235, 244, 254));
            color: var(--dark);
            overflow-x: hidden;
        }
        
        /* Glass panel effect */
        .glass-panel {
            background: var(--glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 80px;
            height: 100vh;
            padding: 20px 0;
            transition: all 0.4s ease;
            z-index: 100;
            overflow: hidden;
            background: var(--glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid var(--glass-border);
        }
        
        .sidebar:hover {
            width: 250px;
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .logo {
            display: flex;
            align-items: center;
            padding: 0 20px;
            margin-bottom: 40px;
            opacity: 0;
            transition: opacity 0.3s 0.2s;
        }
        
        .sidebar:hover .logo {
            opacity: 1;
        }
        
        .logo-icon {
            font-size: 28px;
            color: var(--primary);
            margin-right: 15px;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark);
        }
        
        .nav-links {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 0 15px;
        }
        
        .nav-item {
            position: relative;
            list-style: none;
            transition: all 0.3s;
        }
        
        .nav-item:hover {
            transform: translateX(5px);
        }
        
        .nav-item.active {
            transform: translateX(10px);
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -15px;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 80%;
            background: var(--primary);
            border-radius: 0 5px 5px 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--dark);
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .nav-link:hover {
            background: var(--glass);
            color: var(--primary);
        }
        
        .nav-link i {
            font-size: 1.2rem;
            margin-right: 15px;
            width: 24px;
            text-align: center;
        }
        
        .nav-text {
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .sidebar:hover .nav-text {
            opacity: 1;
        }
        
        .logout-btn {
            margin-top: auto;
            padding: 0 15px;
        }
        
        .logout-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--warning);
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .logout-link:hover {
            background: rgba(247, 37, 133, 0.1);
        }
        
        .logout-link i {
            font-size: 1.2rem;
            margin-right: 15px;
            width: 24px;
            text-align: center;
        }
        
        /* Main content */
        .main {
            margin-left: 80px;
            padding: 30px;
            transition: margin-left 0.4s;
        }
        
        .sidebar:hover ~ .main {
            margin-left: 250px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .page-subtitle {
            color: #666;
            margin-bottom: 20px;
        }
        
        /* Subject cards */
        .subject-card {
            margin-bottom: 40px;
            padding: 16px;
        }
        
        .subject-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 20px;
            padding: 15px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.2);
        }
        
        /* Students table */
        .students-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
        }
        
        .students-table thead th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            position: sticky;
            top: 0;
        }
        
        .students-table th:first-child {
            border-radius: 10px 0 0 0;
        }
        
        .students-table th:last-child {
            border-radius: 0 10px 0 0;
        }
        
        .students-table td {
            padding: 15px;
            background: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .students-table tr:hover td {
            background: rgba(67, 97, 238, 0.1);
            transform: translateX(5px);
            transition: all 0.3s ease;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .info-btn {
            background: var(--success);
        }
        
        .grade-btn {
            background: var(--primary);
        }
        
        /* User profile in header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .user-info h3 {
            font-size: 1rem;
            margin-bottom: 5px;
        }
        
        .user-info p {
            font-size: 0.8rem;
            color: #666;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            
            .sidebar:hover {
                width: 200px;
            }
            
            .main {
                margin-left: 60px;
                padding: 15px;
            }
            
            .sidebar:hover ~ .main {
                margin-left: 200px;
            }
            
            .students-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <div class="logo">
                <i class="fas fa-chalkboard-teacher logo-icon"></i>
                <span class="logo-text">TeachDash</span>
            </div>
            
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="../Main.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Accueil</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="etudiants.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Étudiants</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Statistiques.php" class="nav-link">
                        <i class="fas fa-chalkboard"></i>
                        <span class="nav-text">Statistiques</span>
                    </a>
                </li>
            </ul>
            
            <div class="logout-btn">
                <a href="../../logOut/logOut.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Déconnexion</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <div class="main">
        <header class="header">
            <div class="page-header">
                <h1 class="page-title">Gestion des Étudiants</h1>
                <p class="page-subtitle">Liste des étudiants de la filière <?= htmlspecialchars($nom_filiere) ?></p>
            </div>
            
            <div class="user-profile">
                <div class="avatar">
                    <?= strtoupper(substr($nom_enseignant, 0, 1)) ?>
                </div>
                <div class="user-info">
                    <h3><?= htmlspecialchars($nom_enseignant) ?></h3>
                    <p><?= htmlspecialchars($email_enseignant) ?></p>
                </div>
            </div>
        </header>
        
        <?php foreach($matieres as $matiere): ?>
        <section class="subject-card glass-panel">
            <h1>Votre matieres</h1>
            <h2 class="subject-title"><?= htmlspecialchars($matiere['nom_matiere']) ?></h2>
            
            <?php 
                $stmt = $dba->prepare("SELECT * FROM etudiants WHERE id_filiere = ?");
                $stmt->execute([$id_filiere]);
                $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>    
            
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                        <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="info_etudiant.php?id_etudiant=<?= $etudiant['id_etudiant'] ?>" class="action-btn info-btn" title="Infos">
                                    <i class="fa-solid fa-circle-info"></i>
                                </a>
                                <a href="noter_etudiant.php?id_etudiant=<?= $etudiant['id_etudiant'] ?>&id_matiere=<?= $matiere['id_matiere'] ?>" class="action-btn grade-btn" title="Noter">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endforeach; ?>
    </div>
</body>
</html>