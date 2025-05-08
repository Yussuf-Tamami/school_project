<?php
session_start();
include_once("../../dataBase/connection.php");

if (!isset($_SESSION['id_filiere'])) {
    die("Accès non autorisé.");
}

$id_filiere = $_SESSION['id_filiere'];
$nom_filiere_stmt = $dba->prepare('select nom_filiere from filieres where id_filiere = ?');
$nom_filiere_stmt->execute([$id_filiere]);
$nom_filiere = $nom_filiere_stmt->fetch();

$stmt = $dba->prepare('SELECT COUNT(id_etudiant) FROM etudiants WHERE id_filiere = ?');
$stmt->execute([$id_filiere]);
$num_etudiants = $stmt->fetchColumn();

$stmt = $dba->prepare('SELECT COUNT(id_etudiant) FROM moyennes_generaux WHERE id_filiere = ?');
$stmt->execute([$id_filiere]);
$num_moyennes = $stmt->fetchColumn();

$afficher_stats = ($num_etudiants == $num_moyennes);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques du Semestre</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --dark: #1b263b;
            --light:rgb(230, 238, 247);
            --success: #4cc9f0;
            --warning: rgb(246, 23, 23);
            --glass: rgba(255, 255, 255, 0.2);
            --glass-border: rgba(255, 255, 255, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg,rgb(211, 228, 255), #e4e8ed);
            min-height: 100vh;
            color: var(--dark);
            overflow-x: hidden;
            padding-left: 80px;
            transition: padding 0.4s ease;
        }

        .sidebar:hover ~ body {
            padding-left: 250px;
        }

        /* Main Content */
        .main-content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Cards */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .card-success .card-value {
            color: #28a745;
        }

        .card-warning .card-value {
            color: #dc3545;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f3f5;
        }

        .highlight {
            font-weight: bold;
            color: var(--primary);
        }

        .success {
            color: #28a745;
        }

        .danger {
            color: #dc3545;
        }

        /* Message */
        .message {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 800px;
            margin: 50px auto;
        }

        .message-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .message-text {
            font-size: 1.2rem;
            color: #495057;
            margin-bottom: 20px;
        }

        /* Sidebar (unchanged from your original) */
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
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
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

        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--dark);
            font-weight: 700;
        }

        .filiere-badge {
            background: var(--primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }
            
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .sidebar:hover {
                width: 250px;
                z-index: 1000;
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
                <a href="./Main.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Accueil</span>
                </a>
            </li>
            <li class="nav-item">
    <a href="./gerer_Etudiants/etudiants.php" class="nav-link">
        <i class="fas fa-users"></i>
        <span class="nav-text">Étudiants</span>
    </a>
</li>
<li class="nav-item active">
    <a href="./Statistiques.php" class="nav-link">
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

<div class="main-content">
    <?php if ($afficher_stats): ?>
        <div class="page-header">
            <h1 class="page-title">Statistiques du Semestre</h1>
            <span class="filiere-badge">Filière <?= $nom_filiere[0] ?></span>
        </div>

        <?php
        // Récupérer les moyennes
        $stmt = $dba->prepare("
            SELECT e.nom, e.prenom, m.moyenne
            FROM etudiants e
            JOIN moyennes_generaux m ON e.id_etudiant = m.id_etudiant
            WHERE m.id_filiere = ?
            ORDER BY m.moyenne DESC
        ");
        $stmt->execute([$id_filiere]);
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculs supplémentaires
        $total_moyennes = 0;
        $nb_admis = 0;
        $meilleure = null;
        $pire = null;

        foreach ($etudiants as $e) {
            $m = $e['moyenne'];
            $total_moyennes += $m;
            if ($m >= 10) $nb_admis++;
            if (is_null($meilleure) || $m > $meilleure) $meilleure = $m;
            if (is_null($pire) || $m < $pire) $pire = $m;
        }

        $moyenne_filiere = $total_moyennes / count($etudiants);
        $taux_reussite = ($nb_admis / count($etudiants)) * 100;
        ?>

        <div class="card-container">
            <div class="card">
                <div class="card-title">Nombre total d'étudiants</div>
                <div class="card-value"><?= $num_etudiants ?></div>
            </div>
            
            <div class="card card-success">
                <div class="card-title">Étudiants admis</div>
                <div class="card-value"><?= $nb_admis ?></div>
            </div>
            
            <div class="card">
                <div class="card-title">Taux de réussite</div>
                <div class="card-value"><?= number_format($taux_reussite, 2) ?>%</div>
            </div>
            
            <div class="card">
                <div class="card-title">Moyenne générale</div>
                <div class="card-value"><?= number_format($moyenne_filiere, 2) ?></div>
            </div>
            
            <div class="card card-success">
                <div class="card-title">Meilleure moyenne</div>
                <div class="card-value"><?= number_format($meilleure, 2) ?></div>
            </div>
            
            <div class="card card-warning">
                <div class="card-title">Pire moyenne</div>
                <div class="card-value"><?= number_format($pire, 2) ?></div>
            </div>
        </div>

        <div class="table-container">
            <h2>Classement des étudiants</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Moyenne Générale</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['nom']) ?></td>
                            <td><?= htmlspecialchars($e['prenom']) ?></td>
                            <td class="<?= $e['moyenne'] >= 10 ? 'success' : 'danger' ?>">
                                <?= number_format($e['moyenne'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="message">
            <i class="fas fa-hourglass-half message-icon"></i>
            <p class="message-text">Le semestre n'est pas encore terminé. Les statistiques seront disponibles une fois que toutes les moyennes seront calculées.</p>
            <a href="../Main.php" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>