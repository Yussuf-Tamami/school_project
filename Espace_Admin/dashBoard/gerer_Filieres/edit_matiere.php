<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

$id_matiere = $_GET['id_matiere'] ?? null;
if (!$id_matiere) {
    header('Location: filieres.php');
    exit;
}

// Récupérer les données de la matière
try {
    $stmt = $dba->prepare("SELECT * FROM matieres WHERE id_matiere = ?");
    $stmt->execute([$id_matiere]);
    $matiere = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$matiere) {
        header('Location: filieres.php');
        exit;
    }
    
    // Récupérer tous les enseignants
    $stmt = $dba->query("SELECT id_enseignant, nom, prenom FROM enseignants ORDER BY nom");
    $enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_matiere = $_POST['nom_matiere'] ?? '';
    $id_enseignant = $_POST['id_enseignant'] ?? null;
    
    try {
        $stmt = $dba->prepare("
            UPDATE matieres 
            SET nom_matiere = ?, id_enseignant = ?
            WHERE id_matiere = ?
        ");
        $stmt->execute([$nom_matiere, $id_enseignant, $id_matiere]);
        
        $_SESSION['notification'] = [
            'message' => "Matière mise à jour avec succès",
            'type' => 'success'
        ];
        header('Location: filieres.php');
        exit;
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la Matière</title>
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
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #1e8449;
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
    <h2>Modifier la Matière</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Nom de la Matière :</label>
        <input type="text" name="nom_matiere" value="<?= htmlspecialchars($matiere['nom_matiere']) ?>" required>

        <label>Choisir un Enseignant :</label>
        <select name="id_enseignant" required>
            <option value="" selected>-- Sélectionner --</option>
            <?php foreach ($enseignants as $enseignant): ?>
                <option value="<?= htmlspecialchars($enseignant['id_enseignant']) ?>"
                    <?= $enseignant['id_enseignant'] == $matiere['id_enseignant'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($enseignant['prenom']) . ' ' . htmlspecialchars($enseignant['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Mettre à Jour</button>
    </form>

    <a href="filieres.php" class="back-link">← Retour à la liste</a>
</div>

</body>
</html>