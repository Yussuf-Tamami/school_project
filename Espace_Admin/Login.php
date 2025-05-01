<?php

session_start();
require_once '../connection.php';
$error = "";

if(isset($_POST['submitted'])){
  $email = $_POST['email'];
  $password = $_POST['password'];
  $pass_hashed = hash('sha256', $password);

  $query = $dba->prepare('SELECT * FROM admin WHERE email = ?');
  $query->execute([$email]);
  $result = $query->fetch();

  if($result){
    
    if($pass_hashed === $result['password']){
      $_SESSION['admin_name'] = $result['nom'] . " " . $result['prenom'];
      $_SESSION['admin_email'] = $email;
      header('Location: Main.php');
      exit;
    }else{
      $error = "Mot de passe incorrecte";
    }
  }else{
    $error = "Email incorrecte";
  }

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      background: url(../ESTS.jpg) fixed no-repeat center ;
      background-size: cover;
      
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container {
      background-color: white;
      padding: 3rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.25);
      width: 100%;
      max-width: 420px;
    }

    .login-container h2 {
      text-align: center;
      margin-bottom: 1rem;
      color: #222f3e;
    }

    .login-container label {
      display: block;
      margin-bottom: 0.5rem;
      color: #34495e;
      font-weight: 600;
    }

    .login-container input {
      width: 100%;
      padding: 0.6rem;
      margin-bottom: 1.2rem;
      border: 1px solid #bdc3c7;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #f9f9f9;
    }

    .login-container input:focus {
      outline: none;
      border-color: #f39c12;
      background-color: #fff;
    }

    .login-container button {
      width: 100%;
      padding: 0.75rem;
      background-color: #f39c12;
      border: none;
      color: white;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .login-container button:hover {
      background-color: #e67e22;
    }

    .error-message {
      text-align: center;
      color: red;
      margin-bottom: 1rem;
      font-size: 0.95rem;
    }
  </style>
</head>
<body>

  <form class="login-container" method="post" >
    <h2>Connexion Admin</h2>

    <?php if (!empty($error)) : ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <label for="email">Email Administrateur</label>
    <input type="email" id="email" name="email" placeholder="admin@domain.com" required />

    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" placeholder="••••••••" required />

    <button type="submit" name="submitted">Se connecter</button>
  </form>

</body>
</html>