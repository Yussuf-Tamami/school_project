
  <?php

require_once("OpenDatabase/OpenData.php");

 if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $nom = $_POST['nom'];
  
    $requet = $pdo -> prepare("INSERT INTO filieres (nom_filiere ) VALUES ( ? )");
    $requet -> execute([$nom]);
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
            <input type="submit">
        </div>
    </form>
    
</body>
</html>