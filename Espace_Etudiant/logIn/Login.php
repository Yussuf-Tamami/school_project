<?php
session_start();
require_once '../../dataBase/connection.php';

function demandeExiste($dba, $email)
{
    $query = $dba->prepare('SELECT id_demande FROM demandes WHERE email = ? AND status = "waiting"');
    $query->execute([$email]);
    return $query->fetch() !== false;
}

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

$filieres = [];
$stmt = $dba->query("SELECT id_filiere, nom_filiere FROM filieres");
$filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['signup'])) {
    try {
        $email = $_POST['email'];

        if (demandeExiste($dba, $email)) {
            $error = "Vous avez déjà une demande en attente de traitement.";
            echo "<script>showErrorMessage('" . addslashes($error) . "');</script>";
        } else {
            $id_filiere = $_POST['filiere'];
            $stmt = $dba->prepare("SELECT id_etudiant FROM etudiants WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé par un etudiant.";
                echo "<script>showErrorMessage('" . addslashes($error) . "');</script>";
            } else {
                $query = $dba->prepare('INSERT INTO demandes (nom, prenom, date_naissance, email, password, id_filiere_demandé, identite, note, status, date_demande) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, "waiting", NOW())');

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
                        setTimeout(switchToLogin, 3000);
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
    <title>Student Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: linear-gradient(to right, white 50%, transparent 50%),
                url('../../Images/intro.jpg') no-repeat left center;
            background-size: 50% auto;
            display: flex;
            flex-direction: column;
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

        .main-container {
            display: flex;
            flex: 1;
            position: relative;
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
            opacity: 0.1;
            z-index: 0;
        }

        .info-content {
            position: relative;
            z-index: 1;
            max-width: 400px;
        }

        .info-side h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: white;
        }

        .info-side p {
            font-size: 1rem;
            color: #e5e7eb;
            line-height: 1.6;
        }

        .form-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .login-form:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #1e293b;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #475569;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .login-form input,
        .login-form select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #f8fafc;
            transition: all 0.3s ease;
        }

        .login-form input:focus,
        .login-form select:focus {
            border-color: #3b82f6;
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn {
        width: 100%;
        margin: 5px;
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

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #64748b;
            color: white;
            margin-top: 0.8rem;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-success {
            background-color: #16a34a;
            color: white;
        }

        .btn-success:hover {
            background-color: #15803d;
        }

        .error-message {
            color: #dc2626;
            background-color: #fee2e2;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border-left: 3px solid #dc2626;
        }

        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #16a34a;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s ease-out;
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

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                background: white;
            }

            .main-container {
                flex-direction: column;
            }

            .info-side {
                padding: 2rem 1rem;
            }

            .school-logo {
                position: static;
                margin: 1rem auto;
                display: block;
            }

            .form-side {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <img src="../../Images/logo.png" alt="School Logo" class="school-logo">

    <div class="main-container">
        <div class="info-side">
            <div class="info-content">
                <h1>Welcome, dear student</h1>
                <p>Please log in to access your dashboard, courses, and academic resources. We're glad to have you here.
                </p>
            </div>
        </div>

        <div class="form-side">
            <form class="login-form" method="post" id="login-form">
                <h2>Login</h2>
                <?php if (!empty($error)): ?>
                        <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary">Login</button>
                <button type="button" onclick="switchToSignup()" class="btn btn-success">Demande d'inscription</button>
            </form>

            <form class="login-form" method="post" id="signup-form" style="display: none;">
                <h2>Demande d'inscription</h2>
                <?php if (!empty($error)): ?>
                        <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>
                </div>

                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required>
                </div>

                <div class="form-group">
                    <label for="dat">Date de naissance</label>
                    <input type="date" id="dat" name="date_naiss" required>
                </div>

                <div class="form-group">
                    <label for="note_">Note Bacalaureat</label>
                    <input type="number" step="0.01" id="note_" name="note" placeholder="Note Bacalaureat" required>
                </div>

                <div class="form-group">
                    <label for="email_signup">Email</label>
                    <input type="email" id="email_signup" name="email" placeholder="Entrez votre email" required>
                </div>

                <div class="form-group">
                    <label for="password_signup">Mot de passe</label>
                    <input type="password" id="password_signup" name="password" placeholder="Choisissez un mot de passe"
                        required>
                </div>

                <div class="form-group">
                    <label for="domain">Choisissez votre filier :</label>
                    <select name="filiere" id="domain" required>
                        <option value="" disabled selected>- Sélectionnez un filier -</option>
                        <?php foreach ($filieres as $filiere): ?>
                                <option value="<?= $filiere['id_filiere'] ?>"><?= $filiere['nom_filiere'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="signup" class="btn btn-success">Envoyer la demande</button>
                <button type="button" onclick="switchToLogin()" class="btn btn-secondary">Retour au login</button>
            </form>
        </div>
    </div>

    <div id="successMessage" class="success-message"></div>

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
        
        infoSide.style.background = "linear-gradient(135deg,rgba(39, 72, 181, 0.78), #3b82f6)";
        document.querySelector(".info-content h1").textContent = "Demande d'inscription";
        document.querySelector(".info-content p").textContent = "Remplissez ce formulaire pour demander un accès en tant qu'un etudiant.";
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
        
        infoSide.style.background = "linear-gradient(135deg,rgba(39, 72, 181, 0.78), #3b82f6)";
        document.querySelector(".info-content h1").textContent = "Welcome, dear student";
        document.querySelector(".info-content p").textContent = "Please log in to access your dashboard, courses, and academic resources. We're glad to have you here.";
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

         // Set max date for birth date (18 years ago)
        document.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('dat');
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