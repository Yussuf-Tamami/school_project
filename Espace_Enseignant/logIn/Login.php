<?php
session_start();
require_once '../../dataBase/connection.php';

function demandeExiste($dba, $email) {
  $query = $dba->prepare('SELECT id_demande FROM demandes WHERE email = ? AND status = "waiting"');
  $query->execute([$email]);
  return $query->fetch() !== false;
}


    if(isset($_POST['submitted'])){
        if(!empty($_POST['email']) && !empty($_POST['password'])){
            $email = $_POST['email'];
            $password = $_POST['password'];
            $password_hashed = hash('sha256', $password);
    
            $query = $dba->prepare('SELECT * FROM enseignants WHERE email = ?');
            $query->execute([$email]);
            $result = $query->fetch();
    
            $error = "";
            if($result){
                if($password_hashed === $result['password']){
                    $_SESSION['id_enseignant'] = $result['id_enseignant'];
                    $_SESSION['id_filiere'] = $result['id_filiere'];
                    $_SESSION['nom_enseignant'] = $result['nom'] . " " . $result['prenom'];
                    $_SESSION['email_enseignant'] = $email;
                    header('Location: ../dashBoard/Main.php');
                    exit;
                }else{
                    $error = "Mot de passe incorrecte";
                }
            }else{
                $error = "Email introuvable";
            }
          }
    }


// At the top of your file (before any HTML output)
$filieres = [];

    $stmt = $dba->query("SELECT id_filiere, nom_filiere FROM filieres");
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);




if (isset($_POST['signup'])) {
 try {
        $email = $_POST['email'];
        
        // Vérifier si l'utilisateur a déjà une demande en attente
        if (demandeExiste($dba, $email)) {
            $error = "Vous avez déjà une demande en attente de traitement.";
            echo "<script>alert('".addslashes($error)."');</script>";
        } else {
            $id_filiere = $_POST['domain'];
            $id_matiere = $_POST['matiere'];
            $password = $_POST['password'];
            $password_hashed = hash('sha256', $password);
            // Get filiere name for the demande
            $stmt = $dba->prepare("SELECT nom_filiere FROM filieres WHERE id_filiere = ?");
            $stmt->execute([$id_filiere]);
            $filiere = $stmt->fetch(PDO::FETCH_ASSOC);
            $specialite_nom = $filiere['nom_filiere'];

            // Vérifier si l'email est déjà utilisé comme enseignant
            $stmt = $dba->prepare("SELECT id_enseignant FROM enseignants WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé par un enseignant.";
                echo "<script>alert('".addslashes($error)."');</script>";
            } else {
                // Prepare the insert statement
               // Dans votre code d'inscription (enseignant_login.php)
$query = $dba->prepare('INSERT INTO demandes (nom, prenom, email, password, id_filiere_demandé, id_matiere_demandé, identite, specialité, status, date_demande) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, "waiting", NOW())');

                // Execute with parameters
                $insert_result = $query->execute([
                    $_POST['nom'],
                    $_POST['prenom'],
                    $email,
                    $password_hashed,
                    $id_filiere,
                    $id_matiere,
                    "enseignant",
                    $specialite_nom
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
  <title>Enseignant Login</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      background: linear-gradient(135deg, rgb(207, 255, 202), rgb(244, 247, 255));
    }

    .main-container {
      display: flex;
      height: 100%;
    }

    .info-side {
      flex: 1;
      background: linear-gradient(135deg, rgb(255, 228, 141), rgb(240, 109, 21));
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      text-align: center;
    }

    .info-side h1 {
      font-size: 2.2rem;
      margin-bottom: 1rem;
    }

    .info-side p {
      font-size: 1.1rem;
      max-width: 300px;
    }

    .form-side {
      flex: 1;
      background: linear-gradient(135deg, rgb(255, 244, 244), rgb(227, 233, 255));
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-form {
      width: 100%;
      max-width: 360px;
      background-color: white;
      padding: 2rem;
      box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
      border-radius: 12px;
      border: 1px solid rgb(169, 170, 172);
    }

    .login-form h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #1e293b;
    }

    .login-form label {
      display: block;
      margin-bottom: 0.5rem;
      color: #475569;
      font-size: 0.95rem;
    }

    .login-form input {
      width: 100%;
      padding: 0.6rem;
      margin-bottom: 1rem;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #f9fafb;
    }

    .login-form input:focus {
      border-color: #3b82f6;
      outline: none;
      background-color: white;
    }

    #domain{
      width: 100%;
      padding: 0.6rem;
      margin-bottom: 1rem;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #f9fafb;
    }


    #domain:focus{
        border-color: #3b82f6;
        outline: none;
        background-color: white;
      }

    .login-form button {
      width: 100%;
      padding: 0.7rem;
      background-color: #3b82f6;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .login-form button:hover {
      background-color: #2563eb;
    }

    .error-message {
      text-align: center;
      color: red;
      margin-bottom: 1rem;
    }

    .message-panel {
      background-color: #4CAF50;
      color: white;
      padding: 1rem;
      text-align: center;
      margin-top: 20px;
      border-radius: 8px;
      font-size: 1rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      width: 400px;
      height: 60px;
      position: absolute; /* Position it relative to the nearest positioned ancestor */
      top: 50%; /* Center vertically */
      left: 50%; /* Center horizontally */
      transform: translate(-50%, -50%); /* Adjust the position to truly center it */
      z-index: 10; /* Make sure it's above the form */
      display: none; /* Hidden by default */
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

    #matiere {
    display: none; /* Already hidden initially */
    padding: 8px;
    border: 2px solid #007bff;
    border-radius: 5px;
    background-color: #f8f9fa;
    font-size: 16px;
    color: #333;
    transition: all 0.3s ease;
    }

    #matiere:focus {
      border-color: #0056b3;
      outline: none;
      background-color: #fff;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <div class="info-side">
      <h1>Welcome, Mr. Enseignant!</h1>
      <p>Access your schoolarship dashboard, students, courses, and more.</p>
    </div>

    <div class="form-side" id="form-side">
      <!-- Login Form -->
      <form class="login-form" method="post" id="login-form">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
          <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required />

        <button type="submit" name="submitted">Login</button>

        <button type="button" onclick="switchToSignup()" style="margin-top: 10px; background: #22c55e;">Demande d'inscription</button>
      </form>

      <div id="demandeMessage" class="message-panel" style="display: none;">
        <p>Demande d'inscription envoyée avec succès !</p>
      </div>

      <!-- Signup Form (hidden by default) -->
      <form class="login-form" method="post" id="signup-form" style="display: none;">
        <h2>Demande d'inscription</h2>
        <?php if (!empty($error)): ?>
          <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required />

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required />

        <label for="email_signup">Email</label>
        <input type="email" id="email_signup" name="email" placeholder="Entrez votre email" required />

        <label for="password_signup">Mot de passe</label>
        <input type="password" id="password_signup" name="password" placeholder="Choisissez un mot de passe" required />

        <!-- Domain Selection -->
        <label for="domain">Choisissez votre domaine :</label>
        <select name="domain" id="domain" required onchange="fetchMatieres(this.value)">
  <option value="" disabled selected>- Sélectionnez un domaine -</option>
  <?php foreach ($filieres as $filiere): ?>
    <option value="<?= htmlspecialchars($filiere['id_filiere']) ?>">
      <?= htmlspecialchars($filiere['nom_filiere']) ?>
    </option>
  <?php endforeach; ?>
</select>

<!-- matieres de la filiere selectionee -->

<select name="matiere" id="matiere" style="display: none;">
  </select>
<br>
        <input type="submit" name="signup" value="Envoyer la demande">

        <button type="button" onclick="switchToLogin()" style="margin-top: 10px; background: #94a3b8;">Retour au login</button>
      </form>
    </div>
  </div>

  <script>
    function switchToSignup() {
      document.getElementById("login-form").style.display = "none";
      document.getElementById("signup-form").style.display = "block";
      document.querySelector(".info-side").style.background = "linear-gradient(135deg, #3b82f6, #60a5fa)";
      document.querySelector(".info-side h1").textContent = "Demande d'inscription";
      document.querySelector(".info-side p").textContent = "Remplissez ce formulaire pour demander un accès en tant qu'enseignant.";
    }

    function switchToLogin() {
      document.getElementById("signup-form").style.display = "none";
      document.getElementById("login-form").style.display = "block";
      document.querySelector(".info-side").style.background = "linear-gradient(135deg, rgb(255, 228, 141), rgb(240, 109, 21))";
      document.querySelector(".info-side h1").textContent = "Welcome, Mr. Enseignant!";
      document.querySelector(".info-side p").textContent = "Access your schoolarship dashboard, students, courses, and more.";
    }

    function fetchMatieres(idFiliere) {
    if (!idFiliere) return;

    fetch('get_matieres.php?id_filiere=' + idFiliere)
      .then(response => response.json())
      .then(data => {
        const matiereSelect = document.getElementById('matiere');
        matiereSelect.innerHTML = ''; // Clear old options

        if (data.length > 0) {
          matiereSelect.style.display = 'block';
          data.forEach(matiere => {
            const option = document.createElement('option');
            option.value = matiere.id_matiere;
            option.textContent = matiere.nom_matiere;
            matiereSelect.appendChild(option);
          });
        } else {
          matiereSelect.style.display = 'none';
        }
      })
      .catch(error => console.error('Erreur lors de la récupération des matières:', error));
  }
  </script>
</body>
</html>