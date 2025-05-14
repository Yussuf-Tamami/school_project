<?php
session_start();
require_once '../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
  header('Location: ../logIn/logIn.php');
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
  <title>Profil Administrateur</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #e0eafc, #cfdef3);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .profile-card {
      background-color: #fff;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      max-width: 600px;
      width: 90%;
      text-align: center;
    }

    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #2c3e50;
      margin-bottom: 20px;
    }

    .profile-card h2 {
      margin-bottom: 10px;
      color: #2c3e50;
      font-size: 28px;
    }

    .info-section {
      margin-top: 20px;
      text-align: left;
      padding: 20px;
      border-top: 1px solid #ccc;
    }

    .info-item {
      margin-bottom: 15px;
    }

    .info-item label {
      font-weight: bold;
      color: #555;
      display: block;
      margin-bottom: 5px;
    }

    .info-item p {
      margin: 0;
      color: #333;
      font-size: 16px;
    }

    .btn-group {
      margin-top: 30px;
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      gap: 10px;
    }

    .btn {
      padding: 10px 20px;
      background-color: #2c3e50;
      color: white;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .btn:hover {
      background-color: #1a252f;
    }

    @media (max-width: 500px) {
      .btn {
        width: 100%;
        text-align: center;
      }

      .info-section {
        padding: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="profile-card">
    <img src="https://cdn-icons-png.flaticon.com/512/219/219986.png" alt="Avatar admin" class="profile-avatar">
    <h2><?= htmlspecialchars($admin_name) ?></h2>

    <div class="info-section">
      <div class="info-item">
        <label>Nom complet :</label>
        <p><?= htmlspecialchars($admin_name) ?></p>
      </div>
      <div class="info-item">
        <label>Email :</label>
        <p><?= htmlspecialchars($admin_email) ?></p>
      </div>
      <div class="info-item">
        <label for="role">Role :</label>
        <p>Administrateur</p>
      </div>
    </div>

    <div class="btn-group">
      <a href="Main.php" class="btn"><i class="fas fa-home"></i> Tableau de bord</a>
      <a href="changer_modpass.php" class="btn"><i class="fas fa-key"></i> Modifier mot de passe</a>
      <a href="../../logOut/logOut.php" class="btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
  </div>
</body>
</html>
