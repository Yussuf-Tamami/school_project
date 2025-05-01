<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['admin_name'])) {
  header('Location: Login.php');
  exit;
}

$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mon compte</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color:rgba(255, 245, 183, 0.74);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .account-card {
  background-color: #fff;
  padding: 50px 60px;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  width: 80%;
  height: 50%;
  max-width: 900px;
  min-width: 400px;
}


    .account-card h2 {
      margin-top: 0;
      margin-bottom: 25px;
      font-size: 24px;
      color: #2c3e50;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }

    .account-info {
      margin-bottom: 20px;
    }

    .account-info label {
      font-weight: bold;
      display: block;
      margin-bottom: 5px;
      color: #555;
    }

    .account-info p {
      margin: 0;
      font-size: 16px;
      color: #333;
    }

    .back-link {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #4a89dc;
      font-weight: bold;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="account-card">
    <h2>Mon Compte</h2>

    <div class="account-info">
      <label>Nom :</label>
      <p><?= htmlspecialchars($admin_name) ?></p>
    </div>

    <div class="account-info">
      <label>Email :</label>
      <p><?= htmlspecialchars($admin_email) ?></p>
    </div>

    <a class="back-link" href="Main.php">← Retour au tableau de bord</a>
  </div>

</body>
</html>
