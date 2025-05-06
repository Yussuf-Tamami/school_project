<?php
session_start();
require_once '../../dataBase/connection.php';

if (!isset($_SESSION['id_etudiant'])) {
    echo "Non autorisé.";
    exit;
}

$id_etudiant = $_SESSION['id_etudiant'];


// Récupérer les notifications
$stmt = $dba->prepare("SELECT * FROM notifications WHERE id_etudiant = ? and vu = 0 ORDER BY date_notification DESC LIMIT 10");
$stmt->execute([$id_etudiant]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes Notifications</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  
  <style>
    body {
      font-family: Arial;
      background-color: #f9f9f9;
      padding: 30px;
    }
    .notif-box {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px #ccc;
    }
    .notif {
      border-bottom: 1px solid #eee;
      padding: 10px 0;
    }
    .notif:last-child {
      border-bottom: none;
    }
    .notif-time {
      color: #888;
      font-size: 0.8em;
    }
    a{
        text-decoration: none;
        color:rgb(10, 61, 120);
    }
  </style>
</head>
<body>
  <div class="notif-box">
    <h2><i class="fas fa-bell"></i> Mes Notifications</h2>
    <?php if (count($notifications) == 0): ?>
      <p>Aucune notification.</p>
    <?php else: ?>
      <?php foreach ($notifications as $notif): ?>
        <div class="notif">
          <a href="./voir_notification.php?id_notification=<?= $notif['id_notification']?>&&id_etudiant=<?= $id_etudiant ?>"><p><?= htmlspecialchars($notif['message']) ?></p></a>
          <div class="notif-time"><?= date('d/m/Y H:i', strtotime($notif['date_notification'])) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
