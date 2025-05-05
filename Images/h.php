<?php
if (isset($_POST['login'])) {
    if ($_POST['login'] == "etudiant") {
        header('Location: ../Espace_Etudiant/logIn/logIn.php');
        exit;
    }
    if ($_POST['login'] == "enseignant") {
        header('Location: ../Espace_Enseignant/logIn/logIn.php');
        exit;
    }
    if ($_POST['login'] == "administrateur") {
        header('Location: ../Espace_Admin/logIn/logIn.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Espace Scolaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f2f2f2, #e6e9f0);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        nav {
            width: 100%;
            background-color: #1c1c1c;
            padding: 10px 0;
            overflow: hidden;
            position: relative;
        }

        .marquee {
            white-space: nowrap;
            overflow: hidden;
            position: absolute;
            animation: scroll 20s linear infinite;
            color: #fff;
            font-size: 16px;
            padding-left: 100%;
        }

        @keyframes scroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-100%);
            }
        }

        .main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
        }

        .login-options {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .option-box {
            width: 280px;
            background-color: #fff;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .option-box:hover {
            transform: scale(1.05);
        }

        .option-box img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
        }

        .option-box p {
            font-size: 18px;
            margin-top: 10px;
            color: #333;
        }

        .rotated-box {
            position: absolute;
            top: 30px;
            right: -60px;
            transform: rotate(-90deg);
            background: #FFA500;
            padding: 10px 20px;
            color: #fff;
            font-weight: bold;
            border-radius: 10px 10px 0 0;
        }

        footer {
            background-color: #222;
            color: #fff;
            text-align: center;
            padding: 15px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <nav>
        <div class="marquee">Bienvenue sur la plateforme scolaire - École Exemplar, Contact: 05 22 33 44 55 | Email:
            contact@ecole.ma</div>
    </nav>

    <div class="main">
        <form method="post" id="loginform" class="login-options">
            <div class="option-box" onclick="selectLogin('etudiant')">
                <img src="images/etudiant.jpg" alt="Espace Élève">
                <p>الفضاء الخاص بالتلاميذ والتلميذات</p>
            </div>
            <div class="option-box" onclick="selectLogin('enseignant')">
                <img src="images/enseignant.jpg" alt="Espace Enseignant">
                <p>الفضاء الخاص بالأساتذة</p>
            </div>
            <div class="option-box" onclick="selectLogin('administrateur')">
                <img src="images/directeur.jpg" alt="Espace Directeur">
                <p>الفضاء الخاص بالمدير</p>
            </div>
            <input type="hidden" name="login" id="loginInput">
        </form>

        <div class="rotated-box">زاوية 90°</div>
    </div>

    <footer>
        École Exemplar - Tél: 05 22 33 44 55 | Email: contact@ecole.ma | Adresse: 123 Rue de l'Éducation, Casablanca
    </footer>

    <script>
        function selectLogin(role) {
            document.getElementById('loginInput').value = role;
            document.getElementById('loginform').submit();
        }
    </script>

</body>

</html>