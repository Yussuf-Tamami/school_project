<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

$id_filiere = $_GET['id_filiere'] ?? null;
if (!$id_filiere) {
    header('Location: filieres.php');
    exit;
}

// Récupérer la liste des enseignants
try {
    $stmt = $dba->query("SELECT id_enseignant, nom, prenom FROM enseignants ORDER BY nom");
    $enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_matiere = trim($_POST['nom_matiere'] ?? '');
    $id_enseignant = $_POST['id_enseignant'] ?? null;
    
    if ($nom_matiere !== '') {
        try {
            $stmt = $dba->prepare("
                INSERT INTO matieres (nom_matiere, id_filiere, id_enseignant)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$nom_matiere, $id_filiere, $id_enseignant]);
            
            $_SESSION['notification'] = [
                'message' => "Matière ajoutée avec succès",
                'type' => 'success'
            ];
            header('Location: filieres.php');
            exit;
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Le nom de la matière est obligatoire.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Matière</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input[type="text"], select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 10px;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #3498db;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Ajouter une Nouvelle Matière</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Nom de la Matière :</label>
        <input type="text" name="nom_matiere" required>

        <label>Choisir un Enseignant :</label>
        <select name="id_enseignant" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($enseignants as $enseignant): ?>
                <option value="<?= htmlspecialchars($enseignant['id_enseignant']) ?>">
                    <?= htmlspecialchars($enseignant['prenom']) . ' ' . htmlspecialchars($enseignant['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Ajouter la Matière</button>
    </form>

    <a href="filieres.php" class="back-link">← Retour à la liste</a>
</div>

</body>
</html>
