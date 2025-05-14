<?php
session_start();
require_once '../../dataBase/connection.php';
$error = "";

if (isset($_POST['submitted'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $pass_hashed = hash('sha256', $password);

  $query = $dba->prepare('SELECT * FROM admin WHERE email = ?');
  $query->execute([$email]);
  $result = $query->fetch();

  if ($result) {
    if ($pass_hashed === $result['password']) {
      $_SESSION['admin_name'] = $result['nom'] . " " . $result['prenom'];
      $_SESSION['admin_email'] = $email;
      header('Location: ../dashBoard/Main.php');
      exit;
    } else {
      $error = "Mot de passe incorrecte";
    }
  } else {
    $error = "Email incorrecte";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <title>Admin Login | ESTS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
     :root {
      --primary-color: #3498db;
      --secondary-color: #2980b9;
      --accent-color: #f39c12;
      --dark-color: #2c3e50;
      --light-color: #ecf0f1;
      --danger-color: #e74c3c;
      --success-color: #2ecc71;
    }

    * {
      box-sizing: border-box;
      margin: 0;
padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      display: flex;
      background: url('../../Images/intro.jpg') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
    }

    .login-container {
         display: flex;
      width: 100%;
      max-width: 1000px;
      margin: 0 auto;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      border-radius: 15px;
      overflow: hidden;
      height: 600px;
    }

    .login-left {
      flex: 1;
      background-color: var(--dark-color);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      position: relative;
      overflow: hidden;
    }

    .login-left::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(52, 152, 219, 0.7));
      z-index: 1;
    }


    .logo-container {
      position: relative;
      z-index: 3;
      text-align: center;
      margin-bottom: 3rem;
      transition: all 0.4s ease;
        }

        .logo-container:hover {
            transform: scale(1.3);
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.2));
        }

    .logo-container img {
      max-width: 190px;
      height: auto;
      margin-bottom: 1rem;
    }

    .welcome-text {
      color: white;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .welcome-text h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }


    .welcome-text p {
      font-size: 1rem;
      opacity: 0.9;
    }

    .login-right {
    flex: 1;
      background-color: white;
      padding: 3rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-form h2 {
      color: var(--dark-color);
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      t
ext-align: center;
    }

    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
      font-weight: 500;
    }

    .form-group input {
      width: 100%;
      padding: 0.8rem 1rem 0.8rem 2.5rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-group input:focus {
      outline: none;
      border-color: var(--accent-color);
      box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.2);
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 2.5rem;
      color: #7f8c8d;
    }

    .login-btn {
      width: 100%;
      padding: 0.8rem;
      background-color: var(--accent-color);
      border: none;
      color: white;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 1rem;
    }

    .login-btn:hover {
      background-color: #e67e22;
      transform: translateY(-2px);
    }

    .error-message {
      text-align: center;
      color: var(--danger-color);
      margin-bottom: 1rem;
      padding: 0.8rem;
      background-color: rgba(231, 76, 60, 0.1);
      border-radius: 8px;
      font-size: 0.95rem;
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        height: auto;
      }
      
      .login-left {
        padding: 2rem 1rem;
      }
      
      .login-right {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>

  <div class="login-container">

    <div class="login-left">
      <div class="logo-container">
        <img src="../../Images/logo.png" alt="School Logo">
      </div>
      <div class="welcome-text">
        <h1>Bienvenue Administrateur</h1>
        <p>Système de gestion scolaire de l'ESSI</p>
      </div>
    </div>

    <div class="login-right">
      <form class="login-form" method="post">
        <h2>Connexion Admin</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label for="email">Email Administrateur</label>
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" id="email" name="email" placeholder="admin@domain.com" required />
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="password" name="password" placeholder="••••••••" required />
        </div>

        <button type="submit" class="login-btn" name="submitted">
          <i class="fas fa-sign-in-alt"></i> Se connecter
        </button>
      </form>
    </div>
  </div>

</body>
</html>