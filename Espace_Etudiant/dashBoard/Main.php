<?php
session_start();
require_once '../../dataBase/connection.php';

if (!isset($_SESSION['nom_etudiant']) || !isset($_SESSION['email_etudiant'])) {
    echo "Vous n'êtes pas connecté.";
    exit;
}

$username = htmlspecialchars($_SESSION['nom_etudiant']);
$filiere = htmlspecialchars($_SESSION['nom_filiere']);
$id_etudiant = $_SESSION['id_etudiant'];
$sql = "SELECT * FROM notifications WHERE id_etudiant = ? AND vu = 0 ORDER BY date_notification DESC";
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
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f0f2f5;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 60px;
      height: 100vh;
      background-color: #34495e;
      color: white;
      transition: width 0.3s;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .sidebar:hover {
      width: 200px;
    }
    .sidebar nav {
      display: flex;
      flex-direction: column;
      padding: 10px 0;
      gap: 8px;
    }
    .sidebar nav a {
      color: white;
      text-decoration: none;
      padding: 12px 10px;
      display: flex;
      align-items: center;
      transition: background 0.3s;
    }
    .sidebar nav a:hover {
      background-color: #2c3e50;
    }
    .sidebar nav i {
      margin: 0 10px;
      font-size: 18px;
      min-width: 20px;
      text-align: center;
      transition: transform 0.3s ease;
    }
    .sidebar nav a:hover i {
      transform: scale(1.1);
    }
    .sidebar nav span {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      white-space: nowrap;
    }
    .sidebar:hover nav span {
      opacity: 1;
    }
    .logout {
      padding: 15px;
      text-align: center;
    }
    .logout a {
      color: #ecf0f1;
      text-decoration: none;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .logout span {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      white-space: nowrap;
    }
    .sidebar:hover .logout span {
      opacity: 1;
    }
    .main {
      margin-left: 60px;
      padding: 20px;
      transition: margin-left 0.3s;
    }
    .sidebar:hover ~ .main {
      margin-left: 200px;
    }
    h1 {
      color: #2c3e50;
    }
    .notif-box {
      background-color: #ecf0f1;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      display: flex;
      justify-content: space-between;
    }
    .notif-box a {
      color: #2c3e50;
      text-decoration: none;
    }
  </style>
  <!-- had link kay importi icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="sidebar">
    <nav>
      <a href="./Main.php"><i class="fas fa-home"></i> <span>Accueil</span></a>
      <a href="Notes/notes.php"><i class="fas fa-book"></i> <span>Mes Notes</span></a>
      <a href="#"><i class="fas fa-user"></i> <span>Mon Profil</span></a>
    </nav>
    <div class="logout">
      <a href="../../logOut/logOut.php">Log out</a>
    </div>
  </div>
  <div class="main">
    <h1>Bienvenue <?php echo $username;?> sur votre espace étudiant</h1>
    <p>Accédez à vos notes, cours et informations personnelles depuis ce tableau de bord.</p>

    <div>
      <h2><strong>Notifications:</strong></h2>
        <div class="notifications">
          <?php foreach ($notifications as $notif): ?>
                <div class="notif-box">
                <a href="voir_notification.php?id_notification= <?= $notif['id_notification'] ?>&id_etudiant=<?= $notif['id_etudiant'] ?>">
                    <?= htmlspecialchars($notif['message']) ?>
                </a>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
  </div>
</body>
</html>
