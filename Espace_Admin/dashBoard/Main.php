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
      --primary:rgb(45, 67, 168);
      --primary-dark:rgb(45, 75, 172);
      --secondary:rgb(50, 76, 169);
      --accent:rgb(51, 112, 204);
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
      background-color:rgb(251, 242, 200);
      color: var(--dark);
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
    }

    /* ========== Main Content ========== */
    .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-profile span {
            font-weight: 500;
        }
    .main {
      flex: 1;
      margin-left:  var(--sidebar-width);
      transition: margin-left var(--transition-speed);
      padding: 20px;
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
    gap: 10px;
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

    /* Responsive adjustments */
    @media (max-width: 992px) {
      .stats-notifs {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .main {
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
<aside class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-graduation-cap"></i> <span>SchoolAdmin</span></h3>
    </div>
    
    <nav class="sidebar-menu">
        <a href="./Main.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Main.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Acceuil</span>
        </a>
        <a href="./gerer_Filieres/filieres.php" class="<?= basename($_SERVER['PHP_SELF']) == 'filieres.php' ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i>
            <span>Filières</span>
        </a>
        <a href="./gerer_Enseignants/enseignants.php" class="<?= basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : '' ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Enseignants</span>
        </a>
        <a href="./gerer_Demandes/Demandes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Demandes.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i>
            <span>Demandes</span>
        </a>
        <a href="./pageAccount.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pageAccount.php' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        </a>
    </nav>
    
    <button class="logout-btn" onclick="window.location.href='../../logOut/logOut.php'">
        <i class="fas fa-sign-out-alt"></i>
        <span>Déconnexion</span>
    </button>
</aside>


  <div class="main">
    <div class="header">
      <div class="page-title">
        <h1>Tableau de bord Administrateur</h1>
        <p>Bienvenue sur votre espace d'administration</p>
      </div>
      <div class="user-profile">
                    <a href="pageAccount.php">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random" alt="Admin"></a>
                    <span><?php echo htmlspecialchars($admin_name); ?></span>
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
        <hr>
        <div class="stat-item">
          <span class="stat-label">Filières actives</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_filieres); ?></span>
        </div>
        <hr>
        <div class="stat-item">
          <span class="stat-label">Étudiants inscrits</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_etudiants); ?></span>
        </div>
        <hr>
        <div class="stat-item">
          <span class="stat-label">Enseignants</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_enseignants); ?></span>
        </div>
        <hr>
        <div class="stat-item">
          <span class="stat-label">Demandes en attente</span>
          <span class="stat-value"><?php echo htmlspecialchars($num_demandes); ?></span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>