<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($id && $type && $action) {
        try {
            
            $dba->beginTransaction();

            // informations du demande
            $stmt = $dba->prepare("SELECT * FROM demandes WHERE id_demande = ?");
            $stmt->execute([$id]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($demande) {
                if ($action === 'accept') {
            
                    if ($type === 'enseignant') {
                        //verification wach lmatiere lmtloba m9yd fiha chi ostad mn 9bl
                        $matiere_stmt = $dba->prepare('SELECT nom_matiere, id_enseignant FROM matieres WHERE id_matiere = ?');
                        $matiere_stmt->execute([$demande['id_matiere_demandé']]);
                        $matiere = $matiere_stmt->fetch();
            
                        if ($matiere && $matiere['id_enseignant'] !== null) {
                            $message = "Un autre enseignant est déjà assigné à la matière " . $matiere['nom_matiere'];
                            $_SESSION['notification'] = [
                                'message' => $message,
                                'type' => 'error'
                            ];
                            header('Location: Demandes.php');
                            exit;
                        }
            
                        // matiere sans enseignant , donc on insere la demande
                        $stmt = $dba->prepare("INSERT INTO enseignants (nom, prenom, email, id_filiere, specialite, password) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $demande['nom'],
                            $demande['prenom'],
                            $demande['email'],
                            $demande['id_filiere_demandé'],
                            $demande['specialité'],
                            $demande['password']
                        ]);
            
                        $id_enseignant = $dba->lastInsertId();
            
                        //noter l'id d'enseignant dans la matiere 
                        $query = $dba->prepare("UPDATE matieres SET id_enseignant = ? WHERE id_matiere = ?");
                        $query->execute([$id_enseignant, $demande['id_matiere_demandé']]);
            
                    } elseif ($type === 'etudiant') {
                        $stmt = $dba->prepare("INSERT INTO etudiants (nom, prenom, date_naissance, email, id_filiere, password) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $demande['nom'],
                            $demande['prenom'],
                            $demande['date_naissance'],
                            $demande['email'],
                            $demande['id_filiere_demandé'],
                            $demande['password']
                        ]);
                    }
            
                    
                    $stmt = $dba->prepare("UPDATE demandes SET status = 'accepted' WHERE id_demande = ?");
                    $stmt->execute([$id]);
            
                    $deleteStmt = $dba->prepare("DELETE FROM demandes WHERE id_demande = ?");
                    $deleteStmt->execute([$demande['id_demande']]);
            
                    $nom_complet = $demande['prenom'] . ' ' . $demande['nom'];
                    $_SESSION['notification'] = [
                        'message' => "Demande acceptée pour " . htmlspecialchars($nom_complet) . " (Type: " . ucfirst($demande['identite']) . ")",
                        'type' => 'success'
                    ];
            
                } elseif ($action === 'reject') {
                    $stmt = $dba->prepare("UPDATE demandes SET status = 'rejected' WHERE id_demande = ?");
                    $stmt->execute([$id]);
            
                    $_SESSION['notification'] = [
                        'message' => "Demande refusée pour " . htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']),
                        'type' => 'error'
                    ];
                }
            
                $dba->commit();
            } else 
                throw new Exception("Demande introuvable");
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
}


header('Location: Demandes.php');
exit;
?>