<?php 
session_start();
require_once "../../dataBase/connection.php";

if(!isset($_SESSION['admin_name'])){
  header('Location: ../logIn/logIn.php');
  exit;
}
$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

$num_departements_query = $dba->query('SELECT COUNT(id_departement) FROM departements');
$num_departements = $num_departements_query->fetchColumn();

$num_filieres_query = $dba->query('SELECT COUNT(id_filiere) FROM filieres;');
$num_filieres = $num_filieres_query->fetchColumn();

$num_etudiants_query = $dba->query('SELECT COUNT(id_etudiant) FROM etudiants;');
$num_etudiants = $num_etudiants_query->fetchColumn();

$num_enseignants_query = $dba->query('SELECT COUNT(id_enseignant) FROM enseignants;');
$num_enseignants = $num_enseignants_query->fetchColumn();

$num_demandes_query = $dba->query('SELECT COUNT(id_demande) FROM demandes WHERE status = "waiting";');
$num_demandes = $num_demandes_query->fetchColumn();


?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #3f37c9;
      --accent: #4895ef;
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

    /* ========== Sidebar ========== */
    .sidebar {
      width: var(--sidebar-collapsed);
      height: 100vh;
      background: linear-gradient(180deg, var(--primary), var(--secondary));
      color: white;
      transition: all var(--transition-speed) ease;
      position: fixed;
      box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
      z-index: 100;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .sidebar:hover {
      width: var(--sidebar-width);
    }

    .sidebar-header {
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      height: 70px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-icon {
      font-size: 24px;
      min-width: 40px;
    }

    .logo-text {
      font-size: 18px;
      font-weight: 600;
      white-space: nowrap;
      opacity: 0;
      transition: opacity var(--transition-speed);
    }

    .sidebar:hover .logo-text {
      opacity: 1;
    }

    .sidebar-menu {
      flex: 1;
      padding: 20px 0;
      overflow-y: auto;
    }

    .menu-title {
      color: rgba(255, 255, 255, 0.7);
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 0 20px 10px;
      margin-top: 20px;
      white-space: nowrap;
      opacity: 0;
      transition: opacity var(--transition-speed);
    }

    .sidebar:hover .menu-title {
      opacity: 1;
    }

    .menu-item {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all 0.2s ease;
      margin: 5px 0;
      white-space: nowrap;
    }

    .menu-item:hover {
      background: rgba(255, 255, 255, 0.1);
      border-left-color: white;
    }

    .menu-item.active {
      background: rgba(255, 255, 255, 0.2);
      border-left-color: white;
    }

    .menu-icon {
      font-size: 20px;
      min-width: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .menu-text {
      opacity: 0;
      transition: opacity var(--transition-speed);
    }

    .sidebar:hover .menu-text {
      opacity: 1;
    }

    .menu-badge {
      margin-left: auto;
      background: var(--danger);
      color: white;
      font-size: 12px;
      padding: 2px 8px;
      border-radius: 10px;
      display: none;
    }

    .sidebar-footer {
      padding: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
    }

    .user-info {
      opacity: 0;
      transition: opacity var(--transition-speed);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .sidebar:hover .user-info {
      opacity: 1;
    }

    .user-name {
      font-weight: 500;
      font-size: 14px;
    }

    .user-role {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.7);
    }

    .logout-btn {
      background: none;
      border: none;
      color: white;
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      padding: 10px;
      margin-top: 15px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.2s;
    }

    .logout-btn:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    /* ========== Main Content ========== */
    .main {
      flex: 1;
      margin-left: var(--sidebar-collapsed);
      transition: margin-left var(--transition-speed);
      padding: 20px;
    }

    .sidebar:hover ~ .main {
      margin-left: var(--sidebar-width);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e0e0e0;
    }

    .page-title h1 {
      font-size: 28px;
      color: var(--dark);
      margin-bottom: 5px;
    }

    .page-title p {
      color: #6c757d;
      font-size: 14px;
    }

    .search-bar {
      display: flex;
      align-items: center;
      background: white;
      border-radius: 30px;
      padding: 8px 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .search-bar input {
      border: none;
      outline: none;
      padding: 5px 10px;
      width: 200px;
    }

    .search-bar i {
      color: #6c757d;
    }

    /* ========== Cards ========== */
    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s, box-shadow 0.3s;
      position: relative;
      overflow: hidden;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: var(--primary);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .card-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(67, 97, 238, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-size: 20px;
    }

    .card-title {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 5px;
    }

    .card-value {
      font-size: 24px;
      font-weight: 600;
      color: var(--dark);
    }


    /* ========== Stats & Notifications ========== */
    .stats-notifs {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }

    .stat-box, .notif-box {
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .section-title {
      font-size: 18px;
      margin-bottom: 20px;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-title i {
      color: var(--primary);
    }

    .stat-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .stat-item:last-child {
      border-bottom: none;
    }

    .stat-label {
      color: #6c757d;
      font-size: 14px;
    }

    .stat-value {
      font-weight: 600;
      color: var(--dark);
    }



    /* Responsive adjustments */
    @media (max-width: 992px) {
      .stats-notifs {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 0;
      }
      
      .sidebar:hover {
        width: var(--sidebar-width);
      }
      
      .main {
        margin-left: 0;
      }
      
      .sidebar:hover ~ .main {
        margin-left: 0;
      }
      
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .search-bar {
        width: 100%;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <div class="logo-icon">
          <i class="fas fa-university"></i>
        </div>
        <div class="logo-text">Admin Panel</div>
      </div>
    </div>
    
    <div class="sidebar-menu">
      <div class="menu-title">Main</div>
      <a href="./Main.php" class="menu-item active">
        <div class="menu-icon">
          <i class="fas fa-tachometer-alt"></i>
        </div>
        <div class="menu-text">Dashboard</div>
      </a>
      
      <div class="menu-title">Management</div>
      <a href="./gerer_Filieres/filieres.php" class="menu-item">
        <div class="menu-icon">
          <i class="fas fa-layer-group"></i>
        </div>
        <div class="menu-text">Filières</div>
      </a>
      <a href="./gerer_Enseignants/enseignants.php" class="menu-item">
        <div class="menu-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="menu-text">Enseignants</div>
      </a>
      <a href="./gerer_Demandes/Demandes.php" class="menu-item">
        <div class="menu-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <div class="menu-text">Demandes</div>
        <div class="menu-badge">3</div>
      </a>
    </div>
    
    <div class="sidebar-footer">
      <div class="user-profile">
        <div class="user-avatar">
          <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
          <a href="./pageAccount.php">
          <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
          <div class="user-role">Administrator</div>
        </a>
        </div>
      </div>
      
        <button type="submit" name="logout" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          <a href="../../logOut/logOut.php"></a>
        </button>
    </div>
  </div>

  <div class="main">
    <div class="header">
      <div class="page-title">
        <h1>Tableau de bord Administrateur</h1>
        <p>Bienvenue sur votre espace d'administration</p>
      </div>
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Rechercher...">
      </div>
    </div>
    
    <div class="card-container">
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Filières</div>
            <div class="card-value"><?php echo htmlspecialchars($num_filieres); ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-layer-group"></i>
          </div>
        </div>

      </div>
      
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Enseignants</div>
            <div class="card-value"><?php echo htmlspecialchars($num_enseignants); ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Étudiants</div>
            <div class="card-value"><?php echo htmlspecialchars($num_etudiants); ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-users"></i>
          </div>
        </div>

      </div>
      
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Demandes</div>
            <div class="card-value"><?php echo htmlspecialchars($num_demandes); ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-envelope"></i>
          </div>
        </div>

      </div>
    </div>
    
    <div class="stats-notifs">
      <div class="stat-box">
        <h3 class="section-title"><i class="fas fa-chart-bar"></i> Statistiques détaillées</h3>
        
        <div class="stat-item">
          <span class="stat-label">Départements</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_departements); ?></span>
        </div>
        
        <div class="stat-item">
          <span class="stat-label">Filières actives</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_filieres); ?></span>
        </div>
        
        <div class="stat-item">
          <span class="stat-label">Étudiants inscrits</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_etudiants); ?></span>
        </div>
        
        <div class="stat-item">
          <span class="stat-label">Enseignants</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_enseignants); ?></span>
        </div>
        
        <div class="stat-item">
          <span class="stat-label">Demandes en attente</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_demandes); ?></span>
        </div>
      </div>
      
     
</body>
</html>