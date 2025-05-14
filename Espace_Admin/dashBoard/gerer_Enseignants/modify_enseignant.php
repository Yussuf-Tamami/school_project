<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: enseignants.php');
    exit;
}

// Récupérer les filières pour le select
try {
    $stmt = $dba->query("SELECT * FROM filieres");
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $dba->prepare("SELECT * FROM enseignants WHERE id_enseignant = ?");
    $stmt->execute([$id]);
    $enseignant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $id_filiere = $_POST['id_filiere'] ?? '';
    $specialite = $_POST['specialite'] ?? '';
    
    try {
        $stmt = $dba->prepare("
            UPDATE enseignants 
            SET nom = ?, prenom = ?, email = ?, id_filiere = ?, specialite = ?
            WHERE id_enseignant = ?
        ");
        $stmt->execute([$nom, $prenom, $email, $id_filiere, $specialite, $id]);
        
        $_SESSION['notification'] = [
            'message' => "Enseignant mis à jour avec succès",
            'type' => 'success'
        ];
        header('Location: enseignants.php');
        exit;
    } catch (PDOException $e) {
        $error = "Erreur de mise à jour : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    
</head>
<body>
    <div class="container">
        <h1>Modifier l'Enseignant</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($enseignant): ?>
            <form method="POST" class="edit-form">
                <div class="form-group">
                    <label>Nom:</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($enseignant['nom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Prénom:</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($enseignant['prenom']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($enseignant['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Filière:</label>
                    <select name="id_filiere" required>
                        <?php foreach ($filieres as $filiere): ?>
                            <option value="<?= $filiere['id_filiere'] ?>" 
                                <?= $filiere['id_filiere'] == $enseignant['id_filiere'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($filiere['nom_filiere']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Spécialité:</label>
                    <input type="text" name="specialite" value="<?= htmlspecialchars($enseignant['specialite'] ?? '') ?>">
                </div>
                
                <button type="submit" class="save-btn">Enregistrer</button>
                <a href="enseignants.php" class="cancel-btn">Annuler</a>
            </form>
        <?php else: ?>
            <p>Enseignant non trouvé</p>
        <?php endif; ?>
    </div>
</body>
</html>