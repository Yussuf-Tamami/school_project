
<?php
session_start();
if (!isset($_SESSION['nom_enseignant']) || !isset($_SESSION['email_enseignant'])) {
    echo "Vous n'êtes pas connecté.";
    exit;
}
$id_enseignant = $_SESSION['id_enseignant'];
$nom_enseignant = htmlspecialchars($_SESSION['nom_enseignant']) ;
$email_enseignant = htmlspecialchars($_SESSION['email_enseignant']) ?? 'unknown@unknown.unknown';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Enseignant</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
    }
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 60px;
      height: 100vh;
      background-color: #2d3436;
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
      background-color: #636e72;
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
      margin-left: 60px;
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
      color: #2d3436;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <div class="sidebar">
    <nav>
      <a href="./Main.php"><i class="fas fa-home"></i> <span>Accueil</span></a>
      <a href="gerer_Etudiants/etudiants.php"><i class="fas fa-users"></i> <span>Étudiants</span></a>
      <a href="#"><i class="fas fa-chalkboard"></i> <span>Mes Cours</span></a>
    </nav>
    <div class="logout">
     <a href="../../logOut/logOut.php">Log out</a>
    </div>
  </div>
  <div class="main">
    <h1>Bienvenue <?= $nom_enseignant;?> sur votre espace enseignant</h1>
    <p>Gérez vos cours et consultez les étudiants depuis ce tableau de bord.</p>
  </div>
</body>
</html>