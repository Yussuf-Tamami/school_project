<?php
session_start();
require_once("../OpenDatabase/OpenData.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM teachers_info WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'enseignant';
        header("Location: ../teacher/teacher.php");
        exit;
    } else {
        header("Location: ../signeIN/signeTeacher.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Enseignant</title>
</head>
<body>
    <h2>Connexion Enseignant</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br><br>
        <input type="submit" value="Se connecter">
    </form>
</body>
</html>