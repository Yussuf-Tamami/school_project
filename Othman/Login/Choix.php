

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choix du rôle</title>
</head>
<body>
    <h2>Qui êtes-vous ?</h2>
    <form action="login_redirect.php" method="POST">
        <select name="role" required>
            <option value="">-- Sélectionner votre rôle --</option>
            <option value="etudiant">Étudiant</option>
            <option value="enseignant">Enseignant</option>
            <option value="admin">Administrateur</option>
        </select><br><br>
        <input type="submit" value="Continuer">
    </form>
</body>
</html>