<?php
    if(isset($_POST['login'])){
        if($_POST['login'] == "etudiant"){
            header('Location: ../Espace_Etudiant/logIn/logIn.php');
            exit;
        }

        if($_POST['login'] == "enseignant"){
            header('Location: ../Espace_Enseignant/logIn/logIn.php');
            exit;
        }

        if($_POST['login'] == "administrateur"){
            header('Location: ../Espace_Admin/logIn/logIn.php');
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Scolaire</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            text-align: center;
            width: 300px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-size: 18px;
            margin-left: 10px;
        }

        .option {
            margin: 15px 0;
            display: flex;
            align-items: center;
            justify-content: start;
        }

        input[type="radio"] {
            transform: scale(1.2);
            cursor: pointer;
        }

        @media (max-width: 400px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Vous êtes :</h2>
        <form action="" method="post" id="loginform">
            <div class="option">
                <input type="radio" id="etudiant" name="login" value="etudiant" onchange="document.getElementById('loginform').submit()">
                <label for="etudiant">Étudiant</label>
            </div>
            <div class="option">
                <input type="radio" id="enseignant" name="login" value="enseignant" onchange="document.getElementById('loginform').submit()">
                <label for="enseignant">Enseignant</label>
            </div>
            <div class="option">
                <input type="radio" id="administrateur" name="login" value="administrateur" onchange="document.getElementById('loginform').submit()">
                <label for="administrateur">Administrateur</label>
            </div>
        </form>
    </div>

</body>
</html>
