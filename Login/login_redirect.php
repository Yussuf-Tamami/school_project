<!-- choix_role.php -->
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role'])) {
    $role = $_POST['role'];

    if ($role == 'etudiant') {
        header("Location: login_etudiant.php");
    } elseif ($role == 'enseignant') {
        header("Location: login_enseignant.php");
    } elseif ($role == 'admin') {
        header("Location: login_admin.php");
    } else {
        echo "Rôle invalide.";
    }
    exit;
} else {
    header("Location: choix_role.php");
    exit;
}

