<?php

use function PHPSTORM_META\type;

session_start();
require_once '../../connection.php';

// Vérifier l'authentification admin
if (!isset($_SESSION['admin_name'])) {
    header('Location: Login.php');
    exit;
}

// Récupérer l'ID et le type de la demande
$id_demande = $_GET['id_demande'] ?? null;  // Assurez-vous que c'est le même nom que dans le lien
$type = $_GET['type'] ?? null;

if (!$id_demande || !$type) {
    $_SESSION['error'] = "Paramètres manquants";
    header('Location: Main.php');
    exit;
}

try {
    // Récupérer les détails de la demande
    $stmt = $dba->prepare("
        SELECT d.*, f.nom_filiere 
        FROM demandes d
        JOIN filieres f ON d.id_filiere_demandé = f.id_filiere
        WHERE d.id_demande = ?
    ");
    $stmt->execute([$id_demande]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        throw new Exception("Demande introuvable");
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur: " . $e->getMessage();
    header('Location: Main.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Demande</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color:rgb(209, 209, 209);
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        .detail-value {
            flex: 1;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 8px 15px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Détails de la Demande</h1>
        
        <div class="detail-row">
            <div class="detail-label">Type:</div>
            <div class="detail-value"><?= htmlspecialchars(ucfirst($demande['identite'])) ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Nom:</div>
            <div class="detail-value"><?= htmlspecialchars($demande['nom']) ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Prénom:</div>
            <div class="detail-value"><?= htmlspecialchars($demande['prenom']) ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Email:</div>
            <div class="detail-value"><?= htmlspecialchars($demande['email']) ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Filière Demandé:</div>
            <div class="detail-value"><?= htmlspecialchars($demande['nom_filiere']) ?></div>
        </div>
        <?php if($type == "enseignant"){
            echo"
        <div class='detail-row'>
            <div class='detail-label'>Spécialité:</div>
            <div class='detail-value'>" . htmlspecialchars($demande['specialité'] ?? 'N/A') . "</div>
        </div> "; }?>
        
        <?php if($type == "etudiant"){
            echo"
        <div class='detail-row'>
            <div class='detail-label'>Note:</div>
            <div class='detail-value'>" . htmlspecialchars($demande['note'] ?? 'N/A') . "</div>
        </div> "; }?>

        <div class="detail-row">
            <div class="detail-label">Date Demande:</div>
            <div class="detail-value"><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Statut:</div>
            <div class="detail-value"><?= htmlspecialchars($demande['status']) ?></div>
        </div>
        
        <a href="Demandes.php" class="back-btn">Retour au tableau de bord</a>
    </div>
</body>
</html>