<?php
require_once("../OpenDatabase/OpenData.php");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filier = $_POST['filier'] ?? '';
    $matierProf = $_POST['matierProf'] ?? '';


    if (isset($_POST['supprimerEtudiant'])) {
        $emailEtudiant = $_POST['supprimerEtudiant'];
        $requet = $pdo->prepare("DELETE FROM student_demandes WHERE email = ?");
        $requet->execute([$emailEtudiant]);
    };

    if(isset($_POST['accepterEtudiant'])){
        $emailEtudiant =$_POST['accepterEtudiant'];
        $requet = $pdo -> prepare(" INSERT INTO students_info (nom , prenom , email , password ,id_filiere)
         SELECT student_demandes.nom , student_demandes.prenom ,student_demandes.email ,student_demandes.password , filieres.id 
         FROM student_demandes 
         JOIN filieres ON  filieres.nom_filiere = student_demandes.filier
         where student_demandes.email = ? ;
         ");
         $requet -> execute([$emailEtudiant]);
         $delet = $pdo -> prepare(" DELETE FROM student_demandes WHERE email = ?");
         $delet -> execute([$emailEtudiant]);
    }

    if(isset($_POST['supprimerTeacher'])){
     $emailTeacher = $_POST['supprimerTeacher'];
     $requet = $pdo -> prepare("DELETE FROM teacher_demandes WHERE email = ?");
     $requet->execute([$emailTeacher]);
    };
    
    if(isset($_POST['accepterTeacher'])){
        $emailTeacher = $_POST['accepterTeacher'];
        $requet = $pdo -> prepare("INSERT INTO teachers_info (nom , prenom , email , password , specialite)
        SELECT nom , prenom , email , password , specialite
        FROM teacher_demandes
        where email = ? ;"
        );
        $requet -> execute([$emailTeacher]);

        $requet->execute([$emailTeacher]);
        $delet = $pdo->prepare(" DELETE FROM teacher_demandes WHERE email = ?");
        $delet->execute([$emailTeacher]);
    }
    // جلب الطلبة
    $requet = $pdo->prepare("SELECT nom, prenom, email , noteSelection FROM student_demandes WHERE filier = ? ORDER BY noteSelection DESC ");
    $requet->execute([$filier]);
    $etudiants = $requet->fetchAll();



    // جلب الأساتذة
    $requet2 = $pdo->prepare("SELECT nom, prenom, email, specialite FROM teacher_demandes where specialite = ?");
    $requet2->execute([$matierProf]);
    $professeurs = $requet2->fetchAll();

   
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Interface Admin - Gestion des Demandes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .section {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
        }

        select,
        button {
            padding: 5px;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>

    <form action="" method="POST">
        <!-- Étudiants -->
        <div class="section">
            <h2>Demandes des Étudiants</h2>
            <label for="filiereEtudiant">Choisir la filière:</label>
            <select name="filier" id="filiereEtudiant">
                <option value="">-- Sélectionner --</option>
                <option value="Génie Informatique (GI)" <?= isset($filier) && $filier == "Génie Informatique (GI)" ? "selected" : "" ?>>GI</option>
                <option value="Génie Civil (GC)" <?= isset($filier) && $filier == "Génie Civil (GC)" ? "selected" : "" ?>>GC</option>
                <option value="Développement Digital(DD)" <?= isset($filier) && $filier == "Développement Digital(DD)" ? "selected" : "" ?>>DD</option>
                <option value="Génie Électrique (GE)" <?= isset($filier) && $filier == "Génie Électrique (GE)" ? "selected" : "" ?>>GE</option>
                <option value="Génie Mécanique (GM)" <?= isset($filier) && $filier == "Génie Mécanique (GM)" ? "selected" : "" ?>>GM</option>
            </select>
            <input type="submit" value="Afficher les étudiants">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th> 
                        <th>Email</th>
                        <th>Note</th>
                        <th>Filière</th>
                        <th>Action</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($etudiants)): ?>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                <td><?= htmlspecialchars($etudiant['email']) ?></td>
                                <td><?= htmlspecialchars($etudiant['noteSelection']) ?></td>
                                <td><?= htmlspecialchars($filier) ?></td>
                                <td><button type="submit" name="accepterEtudiant" value="<?= htmlspecialchars($etudiant['email']) ?>">Accepter</button></td>
                                <td><button type="submit" name="supprimerEtudiant" value="<?= htmlspecialchars($etudiant['email']) ?>">Supprimer</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Professeurs -->
        <div class="section">
            <h2>Demandes des Enseignants</h2>
            <label for="filiereProf">Filière:</label>
            <select name="filierProf" id="filiereProf" onchange="updateMatieres()">
                <option value="">-- Sélectionner --</option>
                <option value="GI" <?= isset($filierProf) && $filierProf == "GI" ? "selected" : "" ?>>GI</option>
                <option value="GC" <?= isset($filierProf) && $filierProf == "GC" ? "selected" : "" ?>>GC</option>
                <option value="DD" <?= isset($filierProf) && $filierProf == "DD" ? "selected" : "" ?>>DD</option>
                <option value="GE" <?= isset($filierProf) && $filierProf == "GE" ? "selected" : "" ?>>GE</option>
                <option value="GM" <?= isset($filierProf) && $filierProf == "GM" ? "selected" : "" ?>>GM</option>
            </select>

            <label for="matiereProf">Matière:</label>
            <select name="matierProf" id="matiereProf">
                <option value="">-- Sélectionner une matière --</option>
            </select>
            <input type="submit" value="Afficher les enseignants">

            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Spécialité</th>
                        <th>Action</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($professeurs)): ?>
                        <?php foreach ($professeurs as $prof): ?>
                            <tr>
                                <td><?= htmlspecialchars($prof['nom']) ?></td>
                                <td><?= htmlspecialchars($prof['prenom']) ?></td>
                                <td><?= htmlspecialchars($prof['email']) ?></td>
                                <td><?= htmlspecialchars($prof['specialite']) ?></td>
                               <td>
                                <input type="hidden" name="specialite" value="<?= htmlspecialchars($prof['specialite']) ?>">

                                <button type="submit" name="accepterTeacher" value="<?= htmlspecialchars($prof['email']) ?>">Accepter</button></td>
                                <td><button type="submit" name="supprimerTeacher" value="<?= htmlspecialchars($prof['email']) ?>">Supprimer</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <script>
        const matieresParFiliere = {
       GI: ["Développement Web", "Base de Données", "Sécurité", "IA", "Réseaux"],
       GC: ["Béton Armé", "Topographie", "Hydraulique", "Structures"],
       DD: ["UX/UI Design", "Back-End", "Front-End", "Marketing Digital"],
        GE: ["Électronique", "Automatisme", "Électricité Industrielle", "Systèmes Embarqués"],
        GM: ["Thermodynamique", "DAO/CAO", "Fabrication", "Maintenance"]
        };

        function updateMatieres() {
            const filiere = document.getElementById("filiereProf").value;
            const matiereSelect = document.getElementById("matiereProf");

            matiereSelect.innerHTML = '<option value="">-- Sélectionner une matière --</option>';
            if (matieresParFiliere[filiere]) {
                matieresParFiliere[filiere].forEach(m => {
                    const option = document.createElement("option");
                    option.value = m;
                    option.textContent = m;
                    matiereSelect.appendChild(option);
                });
            }
        }

        function deleteRow(button) {
            const row = button.closest("tr");
            row.remove();
        }

        // Préremplir matières après le chargement
        window.onload = function () {
            const selectedFiliere = document.getElementById("filiereProf").value;
            const selectedMatiere = <?= json_encode($matierProf ?? '') ?>;

            updateMatieres();

            if (selectedMatiere) {
                const matiereSelect = document.getElementById("matiereProf");
                for (let i = 0; i < matiereSelect.options.length; i++) {
                    if (matiereSelect.options[i].value === selectedMatiere) {
                        matiereSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    </script>

</body>

</html>