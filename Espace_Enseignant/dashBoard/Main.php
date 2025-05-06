<?php
session_start();
if (!isset($_SESSION['nom_enseignant']) || !isset($_SESSION['email_enseignant'])) {
    echo "Vous n'êtes pas connecté.";
    exit;
}
$id_enseignant = $_SESSION['id_enseignant'];
$nom_enseignant = htmlspecialchars($_SESSION['nom_enseignant']) ;
$email_enseignant = htmlspecialchars($_SESSION['email_enseignant']) ?? 'unknown@unknown.unknown';

$

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Enseignant</title>
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --dark: #1b263b;
      --light: #f8f9fa;
      --success: #4cc9f0;
      --warning:rgb(246, 23, 23);
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
      background: linear-gradient(135deg, #e0e5ec, #c8d6e5);
      min-height: 100vh;
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
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    
    .welcome-message h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    
    .welcome-message p {
      color: #666;
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
    
    /* Dashboard cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .card {
      padding: 25px;
      transition: transform 0.3s;
    }
    
    .card:hover {
      transform: translateY(-5px);
    }
    
    .card-icon {
      font-size: 2rem;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .card-title {
      font-size: 1rem;
      margin-bottom: 10px;
      color: #666;
    }
    
    .card-value {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .card-description {
      font-size: 0.9rem;
      color: #888;
    }
    
    /* Recent activity */
    .recent-activity {
      padding: 25px;
      margin-bottom: 30px;
    }
    
    .section-title {
      font-size: 1.2rem;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .section-title i {
      color: var(--primary);
    }
    
    .activity-list {
      list-style: none;
    }
    
    .activity-item {
      display: flex;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .activity-item:last-child {
      border-bottom: none;
    }
    
    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(67, 97, 238, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--primary);
    }
    
    .activity-content h4 {
      font-size: 0.9rem;
      margin-bottom: 5px;
    }
    
    .activity-content p {
      font-size: 0.8rem;
      color: #888;
    }
    
    .activity-time {
      margin-left: auto;
      font-size: 0.8rem;
      color: #aaa;
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
      }
      
      .sidebar:hover ~ .main {
        margin-left: 200px;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <aside class="sidebar glass-panel">
    <nav class="sidebar-nav">
      <div class="logo">
        <i class="fas fa-chalkboard-teacher logo-icon"></i>
        <span class="logo-text">Espace Enseignant</span>
      </div>
      
      <ul class="nav-links">
        <li class="nav-item active">
          <a href="./Main.php" class="nav-link">
            <i class="fas fa-home"></i>
            <span class="nav-text">Accueil</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="gerer_Etudiants/etudiants.php" class="nav-link">
            <i class="fas fa-users"></i>
            <span class="nav-text">Étudiants</span>
          </a>
        </li>
        <li class="nav-item">
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
  
  <main class="main">
    <header class="header">
      <div class="welcome-message">
        <h1>Bienvenue, <?= $nom_enseignant; ?></h1>
        <p>Gérez vos cours et consultez les étudiants depuis ce tableau de bord.</p>
      </div>
      
      <div class="user-profile">
        <div class="avatar">
          <?= strtoupper(substr($nom_enseignant, 0, 1)) ?>
        </div>
        <div class="user-info">
          <h3><?= $nom_enseignant ?></h3>
          <p><?= $email_enseignant ?></p>
        </div>
      </div>
    </header>
    
    <section class="dashboard-cards">
      <div class="card glass-panel">
        <div class="card-icon">
          <i class="fas fa-users"></i>
        </div>
        <h3 class="card-title">Étudiants de votre filiere</h3>
        <p class="card-value"><?= $num_etudiants ?></p>
      </div>
      
      
      
      <div class="card glass-panel">
        <div class="card-icon">
          <i class="fas fa-tasks"></i>
        </div>
        <h3 class="card-title">Devoirs à corriger</h3>
        <p class="card-value"><?= $chhal_mndevoir_corrigee ?></p>
      </div>
    </section>
    
    
  </main>
</body>
</html>