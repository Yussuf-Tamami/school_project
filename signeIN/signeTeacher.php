<?php

require_once("../OpenDatabase/OpenData.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $specialite = $_POST['specialite'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $requet = $pdo->prepare("INSERT INTO teacher_demandes (nom , prenom , email ,password , status , specialite ) VALUES (?, ?, ?, ?, 'pending' , ?)");
    $requet->execute([$nom, $prenom, $email, $password,$specialite]);

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
            <label for="email">email</label>
            <input id="email" type="email" name="email" placeholder="entrer votre email"required>
        </div>
        <div>
            <label for="password">password</label>
            <input id="password" type="password" name="password" placeholder="entrer votre pass" required>
        </div>
                  <label for="filiere">Choisissez une filière:</label>
  <select id="filiere" onchange="updateSpecialites()">
    <option value="">-- Sélectionner --</option>
    <option value="GI">GI</option>
    <option value="GC">GC</option>
    <option value="DD">DD</option>
    <option value="GE">GE</option>
    <option value="GM">GM</option>
  </select>

  <br><br>

  <label for="specialite">Choisissez une spécialité:</label>
  <select id="specialite" name="specialite">
    <option value="">-- Sélectionner une filière d'abord --</option>
  </select>

        <div>
            <input type="submit" value="confime">
        </div>
    </form>
    <script>
    const specialitesParFiliere = {
      GI: ["Développement Web", "Base de Données", "Sécurité", "IA", "Réseaux"],
      GC: ["Béton Armé", "Topographie", "Hydraulique", "Structures"],
      DD: ["UX/UI Design", "Back-End", "Front-End", "Marketing Digital"],
      GE: ["Électronique", "Automatisme", "Électricité Industrielle", "Systèmes Embarqués"],
      GM: ["Thermodynamique", "DAO/CAO", "Fabrication", "Maintenance"]
    };

    function updateSpecialites() {
      const filiere = document.getElementById("filiere").value;
      const specialiteSelect = document.getElementById("specialite");

      // vider anciennes options
      specialiteSelect.innerHTML = "";

      if (filiere && specialitesParFiliere[filiere]) {
        specialitesParFiliere[filiere].forEach(s => {
          const option = document.createElement("option");
          option.value = s;
          option.text = s;
          specialiteSelect.appendChild(option);
        });
      } else {
        const defaultOption = document.createElement("option");
        defaultOption.text = "-- Sélectionner une filière d'abord --";
        specialiteSelect.appendChild(defaultOption);
      }
    }
  </script>


    
</body>
</html>