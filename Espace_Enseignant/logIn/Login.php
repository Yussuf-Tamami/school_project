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
      background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.9)), 
                  url('school-background.jpg') no-repeat;
      background-size: 40% auto;
      background-position: left center;
      background-attachment: fixed;
    }

    .main-container {
      display: flex;
      height: 100%;
      position: relative;
    }

    .school-logo {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
            max-width: 120px;
            height: auto;
            transition: all 0.3s ease;
        }

        .school-logo:hover {
            transform: scale(1.05);
            filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.2));
        }


    .info-side {
      flex: 1;
      background: linear-gradient(135deg, #1e3a8a, #3b82f6);
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .info-side::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('../../Images/intro.jpg') no-repeat center center;
      background-size: cover;
      opacity: 0.2;
      z-index: 0;
    }

    .info-content {
      position: relative;
      z-index: 1;
    }

    .info-side h1 {
      font-size: 2.2rem;
      margin-bottom: 1rem;
      color: white;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }

    .info-side p {
      font-size: 1.1rem;
      max-width: 300px;
      color: #e5e7eb;
    }

    .form-side {
      flex: 1;
      background-color: #f8fafc;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    .login-form {
      width: 100%;
      max-width: 400px;
      background-color: white;
      padding: 2.5rem;
      box-shadow: 0 12px 28px rgba(30, 58, 138, 0.15);
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      transform-style: preserve-3d;
      transition: all 0.5s ease;
    }

    .login-form:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(30, 58, 138, 0.2);
    }

    .login-form h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #1e293b;
      font-size: 1.8rem;
    }

    .login-form label {
      display: block;
      margin-bottom: 0.5rem;
      color: #475569;
      font-size: 0.95rem;
      font-weight: 500;
    }

    .login-form input {
      width: 100%;
      padding: 0.8rem;
      margin-bottom: 1.2rem;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      font-size: 1rem;
      background-color: #f8fafc;
      transition: all 0.3s ease;
    }

    .login-form input:focus {
      border-color: #3b82f6;
      outline: none;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    #domain {
      width: 100%;
      padding: 0.8rem;
      margin-bottom: 1.2rem;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      font-size: 1rem;
      background-color: #f8fafc;
      transition: all 0.3s ease;
    }

    #domain:focus {
      border-color: #3b82f6;
      outline: none;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .login-form button {
      width: 100%;
      padding: 0.9rem;
      background-color: #2563eb;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .login-form button:hover {
      background-color: #1d4ed8;
      transform: translateY(-2px);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    .login-form button:active {
      transform: translateY(0);
    }

    .secondary-btn {
      background-color: #64748b !important;
      margin-top: 15px;
    }

    .secondary-btn:hover {
      background-color: #475569 !important;
    }

    .success-btn {
      background-color: #16a34a !important;
    }

    .success-btn:hover {
      background-color: #15803d !important;
    }

    .error-message {
      text-align: center;
      color: #dc2626;
      margin-bottom: 1.5rem;
      padding: 0.8rem;
      background-color: #fee2e2;
      border-radius: 8px;
      font-size: 0.95rem;
    }

    .message-panel {
      background-color: #16a34a;
      color: white;
      padding: 1.2rem;
      text-align: center;
      border-radius: 12px;
      font-size: 1rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      width: 400px;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 100;
      display: none;
      animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translate(-50%, -60%); }
      to { opacity: 1; transform: translate(-50%, -50%); }
    }

    .duplicate-warning {
      color: #92400e;
      background-color: #fef3c7;
      padding: 0.8rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-size: 0.9rem;
      border-left: 4px solid #d97706;
    }

    #matiere {
      width: 100%;
      padding: 0.8rem;
      margin-bottom: 1.2rem;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      font-size: 1rem;
      background-color: #f8fafc;
      transition: all 0.3s ease;
      display: none;
    }

    #matiere:focus {
      border-color: #3b82f6;
      outline: none;
      background-color: white;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    /* Animation for form switch */
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(-100%); opacity: 0; }
    }

    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    .form-slide-out {
      animation: slideOut 0.4s forwards;
    }

    .form-slide-in {
      animation: slideIn 0.4s forwards;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .main-container {
        flex-direction: column;
      }
      
      body {
        background-size: cover;
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
  <div class="main-container">
    <!-- School Logo -->
    <img src="../../Images/logo.png" alt="School Logo" class="school-logo">

    <div class="info-side">
      <div class="info-content">
        <h1>Welcome, Mr. Enseignant!</h1>
        <p>Access your schoolarship dashboard, students, courses, and more.</p>
      </div>
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

        <button type="button" onclick="switchToSignup()" class="secondary-btn success-btn">Demande d'inscription</button>
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
        <select name="matiere" id="matiere">
        </select>
        <br>
        <input type="submit" name="signup" value="Envoyer la demande" class="success-btn">

        <button type="button" onclick="switchToLogin()" class="secondary-btn">Retour au login</button>
      </form>
    </div>
  </div>

  <script>
    // Enhanced form switching with animations
    function switchToSignup() {
      const loginForm = document.getElementById("login-form");
      const signupForm = document.getElementById("signup-form");
      const infoSide = document.querySelector(".info-side");
      
      loginForm.classList.add("form-slide-out");
      
      setTimeout(() => {
        loginForm.style.display = "none";
        signupForm.style.display = "block";
        signupForm.classList.add("form-slide-in");
        
        infoSide.style.background = "linear-gradient(135deg, #1e40af, #3b82f6)";
        document.querySelector(".info-content h1").textContent = "Demande d'inscription";
        document.querySelector(".info-content p").textContent = "Remplissez ce formulaire pour demander un accès en tant qu'enseignant.";
      }, 400);
    }

    function switchToLogin() {
      const loginForm = document.getElementById("login-form");
      const signupForm = document.getElementById("signup-form");
      const infoSide = document.querySelector(".info-side");
      
      signupForm.classList.add("form-slide-out");
      
      setTimeout(() => {
        signupForm.style.display = "none";
        loginForm.style.display = "block";
        loginForm.classList.add("form-slide-in");
        
        infoSide.style.background = "linear-gradient(135deg, #1e3a8a, #3b82f6)";
        document.querySelector(".info-content h1").textContent = "Welcome, Mr. Enseignant!";
        document.querySelector(".info-content p").textContent = "Access your schoolarship dashboard, students, courses, and more.";
      }, 400);
    }

    // Remove animation classes after they complete
    document.querySelectorAll('.login-form').forEach(form => {
      form.addEventListener('animationend', function() {
        this.classList.remove('form-slide-in', 'form-slide-out');
      });
    });

    // Enhanced matieres fetching with loading indicator
    function fetchMatieres(idFiliere) {
      if (!idFiliere) return;

      const matiereSelect = document.getElementById('matiere');
      matiereSelect.innerHTML = '<option value="">Chargement...</option>';
      matiereSelect.style.display = 'block';
      
      // Add slight delay to show loading state
      setTimeout(() => {
        fetch('get_matieres.php?id_filiere=' + idFiliere)
          .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
          })
          .then(data => {
            matiereSelect.innerHTML = '';
            
            if (data.length > 0) {
              const defaultOption = document.createElement('option');
              defaultOption.value = "";
              defaultOption.textContent = "- Sélectionnez une matière -";
              defaultOption.disabled = true;
              defaultOption.selected = true;
              matiereSelect.appendChild(defaultOption);
              
              data.forEach(matiere => {
                const option = document.createElement('option');
                option.value = matiere.id_matiere;
                option.textContent = matiere.nom_matiere;
                matiereSelect.appendChild(option);
              });
            } else {
              matiereSelect.innerHTML = '<option value="">Aucune matière disponible</option>';
            }
          })
          .catch(error => {
            console.error('Erreur:', error);
            matiereSelect.innerHTML = '<option value="">Erreur de chargement</option>';
          });
      }, 300);
    }

    // Form input effects
    document.querySelectorAll('input, select').forEach(element => {
      // Add focus/blur effects
      element.addEventListener('focus', function() {
        this.parentNode.querySelector('label').style.color = '#2563eb';
      });
      
      element.addEventListener('blur', function() {
        this.parentNode.querySelector('label').style.color = '#475569';
      });
      
      // Add floating label effect on input
      if (element.tagName === 'INPUT') {
        element.addEventListener('input', function() {
          if (this.value) {
            this.style.backgroundColor = '#ffffff';
          } else {
            this.style.backgroundColor = '#f8fafc';
          }
        });
      }
    });

    // Show success message with animation
    <?php if (isset($insert_result) && $insert_result): ?>
        document.addEventListener('DOMContentLoaded', function() {
          const messagePanel = document.getElementById('demandeMessage');
          messagePanel.style.display = 'block';
        
          setTimeout(function() {
            messagePanel.style.opacity = '1';
          
            setTimeout(function() {
              messagePanel.style.opacity = '0';
              setTimeout(function() {
                messagePanel.style.display = 'none';
                switchToLogin();
              }, 500);
            }, 2500);
          }, 100);
        });
    <?php endif; ?>
  </script>
</body>
</html>