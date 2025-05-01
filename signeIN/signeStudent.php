<?php

require_once("../OpenDatabase/OpenData.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $filier = $_POST['filier'];
    $note = $_POST['note'];
    $password = password_hash($_POST['password'] , PASSWORD_DEFAULT);

    $requet = $pdo -> prepare("INSERT INTO student_demandes (nom , prenom , noteSelection , email , password , status , filier) VALUES (?, ?, ?, ?, ? , 'pending',?)");
    $requet ->execute([$nom,$prenom,$note,$email,$password,$filier]);

    echo "la demande d'inscription a ete envoyee , attendez l'approbation de la direction";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signe in as student</title>
</head>
<body>
    <he> Signe in</he>
    <h6> if faut de inscrit premierment !</h6>
    <br><br>
    <form action="" method="POST">
        <div>
            <label for="nom">Nom</label>
            <input id="nom" type="text" name="nom" placeholder="entrer votre nom" required>
        </div>
        <div>
            <label for="prenom">prenom</label>
            <input id="prenom" type="text" name="prenom" placeholder="entrer votre nom" required>
        </div>
        <div>
            <label for="note">Note</label>
            <input type="number" name="note" min="0" max="20" step="0.01" required>
        </div>
        <div>
            <label for="email">email</label>
            <input id="email" type="email" name="email" placeholder="entrer votre email"required>
        </div>
        <div>
            <label for="password">password</label>
            <input id="password" type="password" name="password" placeholder="entrer votre pass" required>
        </div>
        <div>
            <label for="filier">filier</label>
            <select id="filier" name="filier" required>
                 <option value="Génie Informatique (GI)" selected >Génie Informatique (GI)</option>
                 <option value="Génie Civil (GC)">Génie Civil (GC)</option>
                  <option value="Développement Digital(DD)">Développement Digital(DD)</option>
                   <option value="Génie Électrique (GE)">Génie Électrique (GE)</option>
                    <option value="Génie Mécanique (GM)">Génie Mécanique (GM)</option>
            </select>
        </div>
        <div>
            <input type="submit" value="confime">
        </div>
    </form>
    
</body>
</html>