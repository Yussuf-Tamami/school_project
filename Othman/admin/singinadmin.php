 <?php

 require_once("../OpenDatabase/OpenData.php");

 if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'] , PASSWORD_DEFAULT);

    $requet = $pdo -> prepare("INSERT INTO admins (nom , prenom , email , password ) VALUES ( ? , ? , ? , ? )");
    $requet -> execute([$nom , $prenom , $email , $password]);
 }
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="POST">
        <div>
            <input name="nom" type="text">
            <input name="prenom" type="text">
            <input name="email" type="email">
            <input name="password" type="password">
            <input type="submit">
        </div>
    </form>
    
</body>
</html>