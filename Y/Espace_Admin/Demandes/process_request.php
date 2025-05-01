<?php
session_start();
require_once '../../connection.php';

// Vérifier l'authentification admin
if (!isset($_SESSION['admin_name'])) {
    header('Location: Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($id && $type && $action) {
        try {
            // Commencer une transaction
            $dba->beginTransaction();

            // 1. Récupérer les infos de la demande avant traitement
            $stmt = $dba->prepare("SELECT * FROM demandes WHERE id_demande = ?");
            $stmt->execute([$id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($demande) {
                // 2. Mettre à jour le statut de la demande
                $newStatus = ($action === 'accept') ? 'accepted' : 'rejected';
                $stmt = $dba->prepare("UPDATE demandes SET status = ? WHERE id_demande = ?");
                $stmt->execute([$newStatus, $id]);

                // 3. Si c'est une acceptation, créer le compte correspondant
                if ($action === 'accept') {
                    if ($type === 'enseignant') {
                        // Créer un compte enseignant
                        $stmt = $dba->prepare("
                            INSERT INTO enseignants 
                            (nom, prenom, email, id_filiere, specialite, password) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $demande['nom'],
                            $demande['prenom'],
                            $demande['email'],
                            $demande['id_filiere_demandé'],
                            $demande['specialité'],
                            $demande['password']
                        ]);

                        $id_enseignant = $dba->lastInsertId();
                        
                        $query = $dba->prepare('
                        update matieres 
                        set id_enseignant = ?
                        where id_matiere = ?');

                        $query->execute([$id_enseignant, $demande['id_matiere_demandé']]);

                        $deleteStmt = $dba->prepare("DELETE FROM demandes WHERE id_demande = ?");
                        $deleteStmt->execute([$demande['id_demande']]);

                    } elseif ($type === 'etudiant') {
                        // Créer un compte étudiant
                        $stmt = $dba->prepare("
                            INSERT INTO etudiants 
                            (nom, prenom, date_naissance, email, id_filiere, password) 
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                       
                        $stmt->execute([
                            $demande['nom'],
                            $demande['prenom'],
                            $demande['date_naissance'],
                            $demande['email'],
                            $demande['id_filiere_demandé'],
                            $demande['password']
                        ]);

                        $deleteStmt = $dba->prepare("DELETE FROM demandes WHERE id_demande = ?");
                        $deleteStmt->execute([$demande['id_demande']]);

                    }
                }

                // Valider la transaction
                $dba->commit();

                // Préparer le message de notification
                $nom_complet = $demande['prenom'] . ' ' . $demande['nom'];
                $message = "Demande " . ($action === 'accept' ? 'acceptée' : 'refusée') . 
                           " pour " . htmlspecialchars($nom_complet) . 
                           " (Type: " . ucfirst($demande['identite']) . ")";
                
                $_SESSION['notification'] = [
                    'message' => $message,
                    'type' => $action === 'accept' ? 'success' : 'error'
                ];
            } else {
                throw new Exception("Demande introuvable");
            }
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $dba->rollBack();
            
            $_SESSION['notification'] = [
                'message' => "Erreur: " . $e->getMessage(),
                'type' => 'error'
            ];
        }
    } else {
        $_SESSION['notification'] = [
            'message' => "Paramètres manquants",
            'type' => 'error'
        ];
    }
} else {
    $_SESSION['notification'] = [
        'message' => "Méthode non autorisée",
        'type' => 'error'
    ];
}

// Redirection vers la page des demandes
header('Location: Demandes.php');
exit;
?>