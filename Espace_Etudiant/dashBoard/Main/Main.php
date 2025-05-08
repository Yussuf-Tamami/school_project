<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['nom_etudiant']) || !isset($_SESSION['email_etudiant'])) {
    echo "Vous n'êtes pas connecté.";
    exit;
}

$username = htmlspecialchars($_SESSION['nom_etudiant']);
$id_filiere = $_SESSION['id_filiere'];
$filiere = $_SESSION['nom_filiere'];
$id_etudiant = $_SESSION['id_etudiant'];
if(isset($_POST['theme'])){
  $current = $_COOKIE['theme'] ?? 'light';
  $newTheme = ($current === 'light') ? 'dark' : 'light';
  setcookie('theme', $newTheme, time() + (86400 * 30), '/');
  header("Location: Main.php"); 
  exit;
}
$theme = $_COOKIE['theme'] ?? 'light';

// Get unread notifications count
$sql_count = "SELECT COUNT(*) FROM notifications WHERE id_etudiant = ? AND vu = 0";
$stmt_count = $dba->prepare($sql_count);
$stmt_count->execute([$id_etudiant]);
$unread_count = $stmt_count->fetchColumn();

// Get all notifications
$sql = "SELECT * FROM notifications WHERE id_etudiant = ? ORDER BY date_notification DESC LIMIT 5";
$stmt = $dba->prepare($sql);
$stmt->execute([$id_etudiant]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Espace Étudiant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="<?php echo $theme; ?>.css"> <!-- Appliquer le style en fonction du thème -->

  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --dark: #1b263b;
      --light: #f8f9fa;
      --success: #4cc9f0;
      --warning: #f61717;
      --glass: rgba(255, 255, 255, 0.2);
      --sidebar-width: 250px;
      --sidebar-collapsed: 80px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

   

    /* Sidebar */
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

    /* Main Content */
    .main {
      margin-left: var(--sidebar-width);
      padding: 20px;
      transition: margin-left 0.3s;
    }

    .sidebar.collapsed ~ .main {
      margin-left: var(--sidebar-collapsed);
    }

    /* Header */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e0e0e0;
    }

    

    .user-actions {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }

    .filiere-badge {
      background-color: var(--primary);
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }

    /* Notifications */
    .notification-container {
      position: relative;
    }

    .notification-icon {
      position: relative;
      font-size: 1.5rem;
      color: var(--dark);
      cursor: pointer;
      transition: transform 0.3s;
    }

    .notification-icon:hover {
      transform: scale(1.1);
    }

    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      width: 18px;
      height: 18px;
      background-color: var(--warning);
      color: white;
      border-radius: 50%;
      font-size: 0.7rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .notification-dropdown {
      position: absolute;
      top: 50px;
      right: 0;
      width: 350px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s;
    }

    .notification-dropdown.active {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .notification-header {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .notification-header h3 {
      font-size: 1.1rem;
      color: var(--dark);
    }

    .mark-all-read {
      color: var(--primary);
      cursor: pointer;
      font-size: 0.9rem;
    }

    .notification-list {
      max-height: 400px;
      overflow-y: auto;
    }

    .notification-item {
      padding: 15px;
      border-bottom: 1px solid #eee;
      transition: background 0.3s;
    }

    .notification-item:hover {
      background-color: #f9f9f9;
    }

    .notification-item.unread {
      background-color: #f0f7ff;
    }

    .notification-item a {
      color: var(--dark);
      text-decoration: none;
      display: block;
    }

    .notification-time {
      font-size: 0.8rem;
      color: #777;
      margin-top: 5px;
    }

    .no-notifications {
      padding: 20px;
      text-align: center;
      color: #777;
    }

    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .card-title {
      font-size: 1rem;
      color: #777;
      margin-bottom: 10px;
    }

    .card-value {
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--dark);
    }

    /* Welcome Section */
    .welcome-section {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 30px;
      border-radius: 10px;
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
    }

    .welcome-section::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .welcome-section h2 {
      font-size: 1.5rem;
      margin-bottom: 10px;
    }

    .welcome-section p {
      opacity: 0.9;
      max-width: 600px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: var(--sidebar-collapsed);
      }
      
      .sidebar .logo-text,
      .sidebar nav span {
        display: none;
      }
      
      .main {
        margin-left: var(--sidebar-collapsed);
      }
      
      .dashboard-cards {
        grid-template-columns: 1fr;
      }
      
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .user-actions {
        width: 100%;
        justify-content: space-between;
      }
      
      .notification-dropdown {
        width: 280px;
        right: -100px;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="logo">
      <img src="../../../Images/logo.png" alt="Logo Ecole" />
      <span class="logo-text">Espace Étudiant</span>
    </div>
    
    <nav>
      <a href="./Main.php" class="active">
        <i class="fas fa-home"></i>
        <span>Accueil</span>
      </a>
      <a href="./Account.php">
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

  <div class="main">
    <div class="header">
      <h1>Bonjour, <?php echo $username; ?>!</h1>
      


      <div class="user-actions">

      <div class="theme-container">
      <form action="" method="post">
        <button type="submit" name="theme">
          <i class="fas fa-moon"></i>
        </button>
      </form>
      </div>

        <div class="notification-container">
        <a href="notifications.php" class="notification-icon" style="position: relative;">
  <i class="fas fa-bell"></i>
  <?php if ($unread_count > 0): ?>
    <span class="notification-badge"><?php echo $unread_count; ?></span>
  <?php endif; ?>
</a>

          
          <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
              <h3>Notifications</h3>
              <span class="mark-all-read">Tout marquer comme lu</span>
            </div>
            
            <div class="notification-list">
              <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                  <div class="notification-item <?= $notif['vu'] == 0 ? 'unread' : '' ?>">
                    <a href="voir_notification.php?id_notification=<?= $notif['id_notification'] ?>&id_etudiant=<?= $notif['id_etudiant'] ?>">
                      <?= htmlspecialchars($notif['message']) ?>
                      <div class="notification-time">
                        <?= date('d/m/Y H:i', strtotime($notif['date_notification'])) ?>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="no-notifications">
                  <p>Aucune notification disponible</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="user-info">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($username) ?>&background=random" alt="Etudiant" style="border-radius: 50%; scale: .7;">
          <span class="filiere-badge"><?php echo $filiere[0]; ?></span>
        </div>
      </div>
    </div>

    <div class="welcome-section">
      <h2>Bienvenue sur votre tableau de bord</h2>
      <p>Accédez à vos notes, cours et informations personnelles depuis cet espace dédié.</p>
    </div>

    
  </div>
</body>
</html>