<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $login = $_POST['login'];
    $redirect_url = '';

    switch ($login) {
        case 'etudiant':
            $redirect_url = '../Espace_Etudiant/logIn/logIn.php';
            break;
        case 'enseignant':
            $redirect_url = '../Espace_Enseignant/logIn/logIn.php';
            break;
        case 'administrateur':
            $redirect_url = '../Espace_Admin/logIn/logIn.php';
            break;
    }

    if (!empty($redirect_url)) {
        header("Location: " . $redirect_url);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Espace Scolaire - Platforme educative</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
        }

        nav {
            height: 70px;
            width: 100%;
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .school-name {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .school-name i {
            margin-left: 10px;
            font-size: 1.3rem;
        }

        .marquee-container {
            flex-grow: 1;
            overflow: hidden;
            margin: 0 20px;
        }

        .marquee {
            white-space: nowrap;
            overflow: hidden;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            animation: scroll 25s linear infinite;
        }

        @keyframes scroll {
            from { transform: translateX(100%); }
            to { transform: translateX(-100%); }
        }

        .logo-container {
            position: absolute;
            left: 30px;
            top: 12px;
        }

        .circular-logo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 3px 6px rgba(0,0,0,0.16);
            transition: transform 0.3s ease;
        }

        .circular-logo:hover {
            transform: scale(1.1);
        }

        .circular-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            background: url('../Images/intro.jpg') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
        }

        .main::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }

        .login-options {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
        }

        .option-box {
            width: 300px;
            background-color: white;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            padding: 0;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .option-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }

        .option-box .image-container {
            width: 100%;
            height: 180px;
            overflow: hidden;
            position: relative;
        }

        .option-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .option-box:hover img {
            transform: scale(1.05);
        }

        .option-box .content {
            padding: 20px;
        }

        .option-box h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .option-box p {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
        }

        .option-box .icon {
            font-size: 2.5rem;
            color: #4a6491;
            margin-bottom: 15px;
        }

        footer {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: white;
            text-align: center;
            padding: 15px 20px;
            font-size: 0.9rem;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section {
            margin: 10px 20px;
        }

        .footer-section h3 {
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .footer-section p {
            margin: 5px 0;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #f1c40f;
        }

        .copyright {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                height: auto;
                padding: 15px;
            }
            
            .school-name {
                margin-bottom: 10px;
            }
            
            .marquee-container {
                margin: 10px 0;
                width: 100%;
            }
            
            .logo-container {
                position: static;
                margin-top: 10px;
            }
            
            .option-box {
                width: 100%;
                max-width: 350px;
            }
            
            .footer-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo-container">
            <div class="circular-logo">
                <img src="../Images/logo.png" alt="Logo de l'école">
            </div>
        </div>
        
        <div class="school-name">
            <i class="fas fa-graduation-cap"></i>
            Ecole Superieure des Sciences et de l'Innovation
        </div>
        
        <div class="marquee-container">
            <div class="marquee">
                Bienvenue sur la plateforme scolaire - Ecole Superieure des Sciences et de l'Innovation, Contact: +212 718 356 368 | Email:essiContact@um5.ac.ma | 
                Ecole Superieure des Sciences et de l'Innovation| 
            </div>
        </div>
    </nav>
    
    <div class="main">
        <form method="post" id="loginform" class="login-options">
            <!-- Etudiant -->
            <input type="radio" name="login" value="etudiant" id="etudiant" hidden onchange="document.getElementById('loginform').submit();">
            <label class="option-box" for="etudiant">
                <div class="image-container">
                    <img src="../Images/students.webp" alt="Espace Élève">
                </div>
                <div class="content">
                    <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    <h3>Espace etudiant</h3>
                    <p>Accédez à la plateforme pédagogique étudiante pour suivre les cours, les exercices et les résultats.</p>
                </div>
            </label>

            <!-- Enseignant -->
            <input type="radio" name="login" value="enseignant" id="enseignant" hidden onchange="document.getElementById('loginform').submit();">
            <label class="option-box" for="enseignant">
                <div class="image-container">
                    <img src="../Images/teaches.jpg" alt="Espace Enseignant">
                </div>
                <div class="content">
                    <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Espace enseignants</h3>
                    <p>Accédez à la plateforme pédagogique permettant aux enseignants de gérer les cours et les évaluations.</p>
                </div>
            </label>

            <!-- Administrateur -->
            <input type="radio" name="login" value="administrateur" id="administrateur" hidden onchange="document.getElementById('loginform').submit();">
            <label class="option-box" for="administrateur">
                <div class="image-container">
                    <img src="../Images/admin.jpg" alt="Espace Directeur">
                </div>
                <div class="content">
                    <div class="icon"><i class="fas fa-user-tie"></i></div>
                    <h3>Espace administratif</h3>
                    <p>Connectez-vous au panneau de contrôle administratif pour la gestion de l'école et des ressources humaines</p>
                </div>
            </label>
        </form>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>contactez-nous</h3>
                <p><i class="fas fa-phone"></i> +212 7 18 35 63 68</p>
                <p><i class="fas fa-envelope"></i>essiContact@um5.ac.ma</p>
            </div>
            
            <div class="footer-section">
                <h3>Titre</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Rue de l'Éducation</p>
                <p>Salé, Maroc</p>
            </div>
            
            <div class="footer-section">
                <h3>heures de travail</h3>
                <p>de lundi au vendredi </p>
                <p>Matin 8:30 - soir 6:30</p>
            </div>
        </div>
        
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
        
        <div class="copyright">
            &copy; 2025 Ecole Superieure des Sciences et de l'Innovation. Tous droits réservés.
        </div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.option-box');
            options.forEach((option, index) => {
                setTimeout(() => {
                    option.style.opacity = '1';
                    option.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>