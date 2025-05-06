<?php
session_start();
require_once '../../dataBase/connection.php';

if (!isset($_SESSION['nom_etudiant']) || !isset($_SESSION['email_etudiant'])) {
    echo "Vous n'êtes pas connecté.";
    exit;
}

$username = htmlspecialchars($_SESSION['nom_etudiant']);
$id_filiere = $_SESSION['id_filiere'];
$filiere = $_SESSION['nom_filiere'];
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
      font-family: 'Arial', sans-serif;
      background-color: #f4f6f9;
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
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }
    .sidebar:hover {
      width: 220px;
    }
    .sidebar .logo {
      text-align: center;
      margin-top: 20px;
    }
    .sidebar .logo img {
      width: 40px;
      border-radius: 50%;
    }
    .sidebar nav {
      display: flex;
      flex-direction: column;
      padding: 20px 0;
      gap: 15px;
    }
    .sidebar nav a {
      color: white;
      text-decoration: none;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      border-radius: 5px;
      transition: background 0.3s;
    }
    .sidebar nav a:hover {
      background-color: #2c3e50;
    }
    .sidebar nav i {
      margin-right: 10px;
      font-size: 18px;
    }
    .sidebar nav span {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      white-space: nowrap;
    }
    .sidebar:hover nav span {
      opacity: 1;
    }
    .sidebar .logout {
      padding: 15px;
      text-align: center;
    }
    .sidebar .logout a {
      color: #ecf0f1;
      text-decoration: none;
      font-size: 14px;
    }
    .main {
      margin-left: 60px;
      padding: 20px;
      transition: margin-left 0.3s;
    }
    .sidebar:hover ~ .main {
      margin-left: 220px;
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
    .notifications {
      margin-top: 20px;
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .header .user-info {
      display: flex;
      align-items: center;
      font-size: 18px;
      color: #34495e;
    }
    .header .user-info img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 15px;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="sidebar">
    <div class="logo">
      <img src="../../logo.png" alt="Logo Ecole" />
    </div>
    <nav>
      <a href="./Main.php"><i class="fas fa-home"></i> <span>Accueil</span></a>
      <a href="Notes/notes.php"><i class="fas fa-book"></i> <span>Mes Notes</span></a>
      <a href="#"><i class="fas fa-user"></i> <span>Mon Profil</span></a>
      <a href="attestation.php" class="btn-attestation"><i class="fas fa-download"></i> <span>Télécharger Attestation</span></a>
      <a href="relver_note.php" class="btn-attestation"><i class="fas fa-download"></i> <span>Télécharger Attestation</span></a>
      <a href="bulletin.php" class="btn-attestation"><i class="fas fa-download"></i> <span>Télécharger Attestation</span></a>
    </nav>
    <div class="logout">
      <a href="../../logOut/logOut.php">Log out</a>
    </div>
  </div>
  <div class="main">
    <div class="header">
      <h1>Bienvenue, <?php echo $username; ?>!</h1>
      <div class="user-info">
        <img src="../../logo.png" alt="User Avatar" />
        <span><?php echo $filiere[0]; ?></span>
      </div>
    </div>
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