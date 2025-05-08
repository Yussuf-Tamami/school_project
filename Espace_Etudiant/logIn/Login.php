<?php
session_start();
require_once '../../dataBase/connection.php';

// Fonction pour vérifier si une demande existe déjà
function demandeExiste($dba, $email)
{
  $query = $dba->prepare('SELECT id_demande FROM demandes WHERE email = ? AND status = "waiting"');
  $query->execute([$email]);
  return $query->fetch() !== false;
}

// Gestion de la connexion
if (isset($_POST['login'])) {
  if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $email = $_POST['email'];
    $password_hashed = hash('sha256', $_POST['password']);

    $query = $dba->prepare('SELECT id_etudiant, nom, prenom, password, id_filiere FROM etudiants WHERE email = ?');
    $query->execute([$email]);

    $result = $query->fetch();
    $error = "";
    if ($result) {
      if ($result['password'] === $password_hashed) {
        $_SESSION['id_etudiant'] = $result['id_etudiant'];
        $_SESSION['nom_etudiant'] = $result['nom'] . " " . $result['prenom'];
        $_SESSION['email_etudiant'] = $email;
        $query = $dba->prepare('SELECT nom_filiere FROM filieres WHERE id_filiere = ?');
        $query->execute([$result['id_filiere']]);
        $_SESSION['id_filiere'] = $result['id_filiere'];
        $_SESSION['nom_filiere'] = $query->fetch();
        header('Location: ../dashBoard/Main.php');
        exit;
      } else {
        $error = "Mot de passe incorrect";
      }
    } else {
      $error = "Email introuvable";
    }
  }
}

// Récupération des filières
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
      echo "<script>alert('" . addslashes($error) . "');</script>";
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
        echo "<script>alert('" . addslashes($error) . "');</script>";
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
                        showSuccessMessage('Demande d\\'inscription envoyée avec succès !');
                        setTimeout(function() {
                            flipCard();
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
    echo "<script>showErrorMessage('Erreur: " . addslashes($error) . "');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Espace Étudiant</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
        url('school-background.jpg') no-repeat;
      background-size: 40% auto;
      background-position: left center;
      background-attachment: fixed;
    }

    .school-logo {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 100;
      max-width: 120px;
      height: auto;
    }

    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      perspective: 1000px;
    }

    .card {
      width: 850px;
      height: 500px;
      position: relative;
      transition: transform 0.8s ease;
      transform-style: preserve-3d;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      border-radius: 15px;
      overflow: hidden;
    }

    .card.flipped {
      transform: rotateY(180deg);
    }

    .face {
      position: absolute;
      width: 100%;
      height: 100%;
      display: flex;
      backface-visibility: hidden;
      border-radius: 15px;
      overflow: hidden;
    }

    .left-side {
      width: 40%;
      background: linear-gradient(135deg, #1e3a8a, #3b82f6);
      color: white;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .left-side::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('../../Images/intro.jpg') no-repeat center center;
      background-size: cover;
      opacity: 0.1;
      z-index: 0;
    }

    .left-content {
      position: relative;
      z-index: 1;
    }

    .left-side h2 {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: white;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .left-side p {
      font-size: 1rem;
      color: #e5e7eb;
      margin-bottom: 2rem;
    }

    .right-side {
      width: 60%;
      background-color: white;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .right-side h3 {
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: #1e293b;
      text-align: center;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    .form-group {
      margin-bottom: 1.2rem;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: #475569;
      font-size: 0.95rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    input,
    select {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 1rem;
      background-color: #f8fafc;
      transition: all 0.3s ease;
    }

    input:focus,
    select:focus {
      border-color: #3b82f6;
      outline: none;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    button {
      padding: 0.9rem;
      background-color: #2563eb;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-top: 0.5rem;
    }

    button:hover {
      background-color: #1d4ed8;
      transform: translateY(-2px);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    button:active {
      transform: translateY(0);
    }

    .toggle-btn {
      background: none;
      border: none;
      color: white;
      font-size: 1rem;
      cursor: pointer;
      text-decoration: underline;
      margin-top: 1.5rem;
      align-self: flex-start;
      transition: all 0.3s ease;
    }

    .toggle-btn:hover {
      color: #bfdbfe;
    }

    .error-message {
      text-align: center;
      color: #dc2626;
      margin-bottom: 1.5rem;
      padding: 0.8rem;
      background-color: #fee2e2;
      border-radius: 8px;
      font-size: 0.95rem;
      animation: fadeIn 0.3s ease-out;
    }

    .message-panel {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #16a34a;
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      display: none;
      animation: slideIn 0.5s ease-out;
    }

    .message-panel.error {
      background-color: #dc2626;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .floating-label {
      position: absolute;
      top: -10px;
      left: 10px;
      background: white;
      padding: 0 5px;
      font-size: 0.8rem;
      color: #3b82f6;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .card {
        width: 90%;
        height: auto;
        flex-direction: column;
      }

      .left-side,
      .right-side {
        width: 100%;
      }

      .left-side {
        padding: 20px;
      }

      .school-logo {
        position: static;
        margin: 20px auto;
        display: block;
      }
    }
  </style>
</head>

<body>
  <!-- School Logo -->
  <img src="../../Images/logo.png" alt="School Logo" class="school-logo">

  <div class="container">
    <div class="card" id="card">
      <!-- Front: Login -->
      <div class="face front">
        <div class="left-side">
          <div class="left-content">
            <h2><i class="fas fa-user-graduate"></i> Espace Étudiant</h2>
            <p>Connectez-vous pour accéder à votre espace personnel</p>
            <button class="toggle-btn" onclick="flipCard()">Créer un compte <i class="fas fa-arrow-right"></i></button>
          </div>
        </div>

        <div class="right-side">
          <h3>Connexion</h3>
          <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form action="login.php" method="POST">
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" name="email" id="email" placeholder="Votre email" required>
            </div>
            <div class="form-group">
              <label for="pass">Mot de passe</label>
              <input type="password" name="password" id="pass" placeholder="Votre mot de passe" required>
            </div>
            <button type="submit" name="login">Se connecter</button>
          </form>
        </div>
      </div>

      <!-- Back: Sign Up -->
      <div class="face back">
        <div class="left-side">
          <div class="left-content">
            <h2><i class="fas fa-user-plus"></i> Rejoignez-nous</h2>
            <p>Inscrivez-vous et commencez votre parcours académique</p>
            <button class="toggle-btn" onclick="flipCard()"><i class="fas fa-arrow-left"></i> J'ai déjà un
              compte</button>
          </div>
        </div>

        <div class="right-side">
          <h3>Créer un compte</h3>
          <form method="POST">
            <div class="form-group">
              <label for="nom">Nom</label>
              <input type="text" name="nom" id="nom" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
              <label for="prenom">Prénom</label>
              <input type="text" name="prenom" id="prenom" placeholder="Votre prénom" required>
            </div>
            <div class="form-group">
              <label for="date_naiss">Date de naissance</label>
              <input type="date" name="date_naiss" id="date_naiss" required>
            </div>
            <div class="form-group">
              <label for="email_signup">Email</label>
              <input type="email" name="email" id="email_signup" placeholder="Votre email" required>
            </div>
            <div class="form-group">
              <label for="password_signup">Mot de passe</label>
              <input type="password" name="password" id="password_signup" placeholder="Choisissez un mot de passe"
                required>
            </div>
            <div class="form-group">
              <label for="note">Note Baccalauréat</label>
              <input type="number" step="0.01" name="note" id="note" placeholder="Votre note" required>
            </div>
            <div class="form-group">
              <label for="filiere">Filière</label>
              <select name="filiere" id="filiere" required>
                <option value="" disabled selected>- Sélectionnez une filière -</option>
                <?php foreach ($filieres as $filiere): ?>
                  <option value="<?= $filiere['id_filiere'] ?>"><?= $filiere['nom_filiere'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" name="signup">S'inscrire</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div id="messagePanel" class="message-panel" style="display: none;">
    <p id="messageText"></p>
  </div>

  <script>
    // Card flip animation
    const card = document.getElementById('card');
    function flipCard() {
      card.classList.toggle('flipped');
    }

    // Show success/error messages
    function showMessage(message, isError = false) {
      const panel = document.getElementById('messagePanel');
      const text = document.getElementById('messageText');

      panel.className = isError ? 'message-panel error' : 'message-panel';
      text.textContent = message;
      panel.style.display = 'block';

      setTimeout(() => {
        panel.style.display = 'none';
      }, 5000);
    }

    function showSuccessMessage(message) {
      showMessage(message, false);
    }

    function showErrorMessage(message) {
      showMessage(message, true);
    }

    // Form input effects
    document.querySelectorAll('input, select').forEach(element => {
      // Add focus/blur effects
      element.addEventListener('focus', function () {
        const label = this.parentNode.querySelector('label');
        label.style.color = '#2563eb';
      });

      element.addEventListener('blur', function () {
        const label = this.parentNode.querySelector('label');
        label.style.color = '#475569';
      });

      // Add floating effect for filled inputs
      element.addEventListener('input', function () {
        if (this.value) {
          this.style.backgroundColor = '#ffffff';
        } else {
          this.style.backgroundColor = '#f8fafc';
        }
      });
    });

    // Date input default value (today - 18 years)
    window.addEventListener('DOMContentLoaded', () => {
      const dateInput = document.getElementById('date_naiss');
      if (dateInput) {
        const today = new Date();
        const minDate = new Date();
        minDate.setFullYear(today.getFullYear() - 18);
        dateInput.max = minDate.toISOString().split('T')[0];
      }
    });
  </script>
</body>

</html>