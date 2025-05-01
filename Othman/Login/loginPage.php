<?php
session_start();
require_once("../OpenDatabase/OpenData.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "SELECT * FROM users WHERE email = ? AND rol=?";
    $requet = $pdo->prepare($query);
    $requet->execute([$email, $role]);
    $user = $requet->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['rol'];

        if ($role == 'etudiant') {
            header("Location: ../Student/student.php");
        } elseif ($role == 'enseignant') {
            header("Location: ../teacher/teacher.php");
        } elseif ($role == "admin") {
            header("Location: ../admin/admin.php");
        }
        exit;

    } else {
        if ($role == 'etudiant') {
            header("Location: ../signeIN/signeStudent.php");
        } elseif ($role == 'enseignant') {
            header("Location: ../signeIN/signeTeacher.php");
        } elseif ($role == 'admin') {
            echo " Impossible d'inscrit comme un admin";
        } else {
            echo "errererere";
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>

<body>
    <h1>Login</h1>

    <form action="" method="POST">
        <div>
            <label for="email"> email</label>
            <input id="email" type="email" name="email" placeholder="enter your email" required>
        </div>
        <div>
            <label for="password">password</label>
            <input id="password" type="password" name="password" placeholder="enter your password" required>
        </div>
        <div>
            <label for="role"></label>
            <select id="role" name="role" required>
                <option value="etudiant">etudiant</option>
                <option value="enseignant">enseignant</option>
                <option value="admin">admin</option>
            </select>
        </div><br>
        <div>
            <input type="submit" value="confirme">
        </div>
    </form>

</body>

</html>