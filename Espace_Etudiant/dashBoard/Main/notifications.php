<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['id_etudiant'])) {
    echo "Non autorisé.";
    exit;
}

$id_etudiant = $_SESSION['id_etudiant'];


$updateStmt = $dba->prepare("UPDATE notifications SET vu = 1 WHERE id_etudiant = ? AND vu = 0");
$updateStmt->execute([$id_etudiant]);


$stmt = $dba->prepare("SELECT * FROM notifications WHERE id_etudiant = ? ORDER BY date_notification DESC LIMIT 10");
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
      transition: background-color 0.3s;
    }
    .notif:hover {
      background-color: #f5f5f5;
    }
    .notif:last-child {
      border-bottom: none;
    }
    .notif-time {
      color: #888;
      font-size: 0.8em;
      margin-top: 5px;
    }
    .notif-message {
      color: #333;
      font-weight: normal;
    }
    a {
      text-decoration: none;
      color: rgb(10, 61, 120);
    }
    .no-notifications {
      color: #666;
      text-align: center;
      padding: 20px;
    }
    .notification-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="notif-box">
    <div class="notification-header">
      <i class="fas fa-bell" style="font-size: 1.5em;"></i>
      <h2>Mes Notifications</h2>
    </div>
    
    <?php if (count($notifications) == 0): ?>
      <div class="no-notifications">
        <i class="far fa-bell-slash" style="font-size: 2em; margin-bottom: 10px;"></i>
        <p>Aucune notification disponible</p>
      </div>
    <?php else: ?>
      <?php foreach ($notifications as $notif): ?>
        <div class="notif">
          <a href="./voir_notification.php?id_notification=<?= $notif['id_notification'] ?>&id_etudiant=<?= $id_etudiant ?>">
            <div class="notif-message"><?= htmlspecialchars($notif['message']) ?></div>
            <div class="notif-time">
              <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($notif['date_notification'])) ?>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>