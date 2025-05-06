<?php
session_start();
require_once '../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../logIn/logIn.php');
    exit;
}

$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier le mot de passe actuel avec PDO
    $stmt = $dba->prepare("SELECT password FROM admin WHERE email = :email");
    $stmt->bindValue(':email', $admin_email, PDO::PARAM_STR);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && hash('sha256', $current_password) === $admin['password']) {
        // Vérifier que le nouveau mot de passe et la confirmation sont identiques
        if ($new_password === $confirm_password) {
            // Mettre à jour le mot de passe
            $new_hashed_password = hash('sha256', $new_password);
            $update_stmt = $dba->prepare("UPDATE admin SET password = :password WHERE email = :email");
            $update_stmt->bindValue(':password', $new_hashed_password, PDO::PARAM_STR);
            $update_stmt->bindValue(':email', $admin_email, PDO::PARAM_STR);

            if ($update_stmt->execute()) {
                $success_message = "Le mot de passe a été changé avec succès.";
            } else {
                $error_message = "Une erreur est survenue lors de la mise à jour du mot de passe.";
            }
        } else {
            $error_message = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $error_message = "Le mot de passe actuel est incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            color: #555;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .button {
            width: 100%;
            padding: 10px;
            background-color: #1877f2;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #165eab;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .success {
            color: #2ecc71;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #1877f2;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Changer le mot de passe</h2>

        <?php if (isset($success_message)) { ?>
            <p class="success"><?= $success_message ?></p>
        <?php } ?>

        <?php if (isset($error_message)) { ?>
            <p class="error"><?= $error_message ?></p>
        <?php } ?>

        <form method="POST">
            <div class="input-group">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="input-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="button">Changer le mot de passe</button>
        </form>

        <a href="Main.php" class="back-link">← Retour au tableau de bord</a>
    </div>

</body>
</html>
