<?php
session_start();
require_once '../../dataBase/connection.php';

// Fonction pour vérifier si une demande existe déjà
function demandeExiste($dba, $email) {
    $query = $dba->prepare('SELECT id_demande FROM demandes WHERE email = ? AND status = "waiting"');
    $query->execute([$email]);
    return $query->fetch() !== false;
}

// Gestion de la connexion
if(isset($_POST['login'])) {  // Changé de 'submitted' à 'login'
    if(!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $_POST['email'];
        $password_hashed = hash('sha256', $_POST['password']);

        $query = $dba->prepare('SELECT id_etudiant, nom, prenom, password, id_filiere FROM etudiants WHERE email = ?');
        $query->execute([$email]);

        $result = $query->fetch();
        $error = "";
        if($result) {
          
            if($result['password'] === $password_hashed) {
                $_SESSION['id_etudiant'] = $result['id_etudiant'];
                $_SESSION['nom_etudiant'] = $result['nom'] . " " . $result['prenom'];
                $_SESSION['email_etudiant'] = $email;
                $query = $dba->prepare('SELECT nom_filiere FROM filieres WHERE id_filiere = ?');
                $query->execute([$result['id_filiere']]);
                $_SESSION['id_filiere'] = $result['id_filiere'];
                $_SESSION['nom_filiere'] = $query->fetch();
                header('Location: ../dashBoard/Main/Main.php');
                exit;
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Email introuvable";
        }
    }
}

// Récupération des filières (inchangé)
$filieres = [];
$stmt = $dba->query("SELECT id_filiere, nom_filiere FROM filieres");
$filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de l'inscription
if (isset($_POST['signup'])) {
    try {
        $email = $_POST['email'];
        
        // Vérifier si l'utilisateur a déjà une demande en attente
        if (demandeExiste($dba, $email)) {
            $error = "Vous avez déjà une demande en attente de traitement.";
            echo "<script>alert('".addslashes($error)."');</script>";
        } else {
            $id_filiere = $_POST['filiere'];

            // Get filiere name for the demande
            $stmt = $dba->prepare("SELECT nom_filiere FROM filieres WHERE id_filiere = ?");
            $stmt->execute([$id_filiere]);
            $filiere = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier si l'email est déjà utilisé par un etudiant
            $stmt = $dba->prepare("SELECT id_etudiant FROM etudiants WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé par un etudiant.";
                echo "<script>alert('".addslashes($error)."');</script>";
            } else {
                // Prepare the insert statement
                $query = $dba->prepare('INSERT INTO demandes (nom, prenom, date_naissance, email, password, id_filiere_demandé, identite, note, status, date_demande) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, "waiting", NOW())');

                // Execute with parameters
                $insert_result = $query->execute([
                    $_POST['nom'],
                    $_POST['prenom'],
                    $_POST['date_naiss'],
                    $email,
                    hash('sha256', $_POST['password']),
                    $id_filiere,
                    "etudiant",
                    $_POST['note']
                ]);

                if ($insert_result) {
                    echo "<script>
                        document.getElementById('demandeMessage').style.display = 'block';
                        setTimeout(function() {
                            document.getElementById('demandeMessage').style.display = 'none';
                            switchToLogin();
                        }, 3000);
                    </script>";
                } else {
                    $errorInfo = $query->errorInfo();
                    throw new Exception("Erreur de base de données: " . $errorInfo[2]);
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        echo "<script>alert('Erreur: " . addslashes($error) . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Etudiant Login</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: url(../../supplements/ESTS.jpg) no-repeat fixed center;
      background-size: cover;
    }

    .overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(79, 79, 79, 0.23);
      z-index: -1;
    }

    .container {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      perspective: 1000px;
    }

    .card {
      width: 800px;
      height: 80%;
      position: relative;
      transition: transform 0.8s;
      transform-style: preserve-3d;
    }

    .flipped {
      transform: rotateY(180deg);
    }

    .face {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 15px;
      box-shadow: 0 0 30px rgba(171, 171, 171, 0.3);
      display: flex;
      overflow: hidden;
      backface-visibility: hidden;
    }

    .face .left,
    .face .right {
       width: 50%;
       padding: 40px;
    }

    .face .left {
      background-color: rgba(172, 246, 233, 0.19); /* شفافية */
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border-radius: 8px 0px 0px 8px;
      padding: 20px;
      color:  rgb(254, 255, 255);
      text-shadow:  rgba(0, 0, 0, 0.89) 3px 3px ;
    }

    .face .right {
       background-color: white;
        color: #000;
       border-radius: 0px 8px 8px 0px ;
    }



    .face .left h2 {
      font-size: 30px;
      margin-bottom: 15px;
    }

    .face .left p {
      font-size: 16px;
    }

    .face .right h3 {
      margin-bottom: 20px;
      margin-top: -5px;
      text-align: center;
    }

    .face .right form {
      display: flex;
      flex-direction: column;
    }

    .face .right input, .face .right select {
      margin-bottom: 15px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .face .right button {
      padding: 10px;
      background-color: #004d7a;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .face .right button:hover {
      background-color: #003a5c;
    }

    .back {
      transform: rotateY(180deg);
    }

    .toggle-btn {
      margin-top: 20px;
      background: none;
      border: none;
      color: white;
      font-size: 16px;
      cursor: pointer;
      text-decoration: underline;
    }

    .error-message {
      text-align: center;
      color: red;
      margin-bottom: 1rem;
      font-size: 0.95rem;
    }

    .duplicate-warning {
      color: #d97706;
      background-color: #fef3c7;
      padding: 0.5rem;
      border-radius: 4px;
      margin-bottom: 1rem;
      text-align: center;
      font-size: 0.9rem;
    }

  </style>
</head>
<body>
<div class="overlay"></div>

<div class="container">
  <div class="card" id="card">
    <!-- Front: Login -->
    <div class="face front">
      <div class="left">
        <h2><i class="fas fa-user-graduate"></i> Espace Étudiant</h2>
        <p>Connectez-vous pour accéder à votre espace</p>
        <button class="toggle-btn" onclick="flipCard()">Créer un compte</button>
      </div>



      <div class="right">
        <h3>Connexion</h3>
        <?php if (!empty($error)) : ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form action="login.php" method="POST">
  <label for="email">Email:</label>
  <input type="email" name="email" id="email" placeholder="Email" required>
  <label for="pass">Password</label>
  <input type="password" name="password" id="pass" placeholder="Mot de passe" required>
  <button type="submit" name="login">Se connecter</button>  <!-- Changé name="login" -->
</form>
      </div>
    </div>


    <div id="demandeMessage" class="message-panel" style="display: none;">
      <p>Demande d'inscription envoyée avec succès !</p>
    </div>

    <!-- Back: Sign Up -->
    <div class="face back">
      <div class="left">
        <h2><i class="fas fa-user-plus"></i> Rejoignez-nous</h2>
        <p>Inscrivez-vous et commencez votre parcours</p>
        <button class="toggle-btn" onclick="flipCard()">J'ai déjà un compte</button>
      </div>
      <div class="right">
        <h3>Créer un compte</h3>
        <form method="POST">
          <input type="text" name="nom" placeholder="Nom" required>
          <input type="text" name="prenom" placeholder="Prénom" required>
          <input type="date" name="date_naiss" placeholder="Date de naissance" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Mot de passe" required>
          <input type="number" step="0.01" name="note" placeholder="Note Bacalaureat" required>
          <select name="filiere" required>
            <?php foreach($filieres as $filiere):?>
              <option value="<?=$filiere['id_filiere']?>"><?=$filiere['nom_filiere']?></option>
              <?php endforeach;?>
            
          </select>
          <button type="submit" name="signup">S'inscrire</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const card = document.getElementById('card');
  function flipCard() {
    card.classList.toggle('flipped');
  }
  function handleSignupResponse() {
  // This will be triggered by the PHP response
  document.getElementById("demandeMessage").style.display = "block";
  setTimeout(function() {
      document.getElementById("demandeMessage").style.display = "none";
      switchToLogin();
  }, 3000);
}
</script>

</body>
</html>