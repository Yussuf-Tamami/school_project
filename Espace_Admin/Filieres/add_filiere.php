<?php
session_start();
require_once '../../connection.php';

// Vérifier l'authentification admin
if (!isset($_SESSION['admin_name'])) {
    header('Location: Login.php');
    exit;
}

// Récupérer l'id_departement passé par l'URL
$id_departement = $_GET['id_departement'] ?? null;

// Vérifier si l'id_departement est valide
if (!$id_departement) {
    header('Location: filieres.php');
    exit;
}

// Gestion de l'ajout de la filière
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_filiere = trim($_POST['nom_filiere'] ?? '');
    
    if ($nom_filiere !== '') {
        try {
            // Insertion de la filière dans la base de données
            $stmt = $dba->prepare("
                INSERT INTO filieres (nom_filiere, id_departement)
                VALUES (?, ?)
            ");
            $stmt->execute([$nom_filiere, $id_departement]);
            
            // Notification de succès
            $_SESSION['notification'] = [
                'message' => "Filière ajoutée avec succès",
                'type' => 'success'
            ];
            header('Location: filieres.php'); // Redirection vers filieres.php
            exit;
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Le nom de la filière est obligatoire.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Filière</title>
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
        input[type="text"] {
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
    <h2>Ajouter une Nouvelle Filière</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Nom de la Filière :</label>
        <input type="text" name="nom_filiere" required>

        <button type="submit">Ajouter la Filière</button>
    </form>

    <a href="filieres.php" class="back-link">← Retour à la liste</a>
</div>

</body>
</html>
