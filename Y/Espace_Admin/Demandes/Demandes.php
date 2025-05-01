<?php
session_start();
require_once '../../connection.php';

// Verify admin authentication
if (!isset($_SESSION['admin_name'])) {
    header('Location: ../Login.php');
    exit;
}
$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];
// Fetch all teacher requests grouped by filière
$teacherRequests = [];
$studentRequests = [];

try {
    // Get teacher requests
    $stmt = $dba->prepare("
        SELECT d.*, f.nom_filiere 
        FROM demandes d
        JOIN filieres f ON d.id_filiere_demandé = f.id_filiere
        WHERE d.identite = 'enseignant' AND d.status = 'waiting'
        ORDER BY f.nom_filiere
    ");
    $stmt->execute();
    $teacherRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get student requests (corrected query)
    $stmt = $dba->prepare("
        SELECT d.*, f.nom_filiere 
        FROM demandes d
        JOIN filieres f ON d.id_filiere_demandé = f.id_filiere
        WHERE d.identite = 'etudiant' AND d.status = 'waiting'
        ORDER BY f.nom_filiere, note desc
    ");
    $stmt->execute();
    $studentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

if(isset($_POST['logout'])){
    session_unset();
    session_destroy();
    header('Location: ../Login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color:rgba(255, 245, 180, 0.64);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        h1, h2 {
            color: #333;
        }
        h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .filiere-group {
            margin-bottom: 25px;
        }
        .filiere-title {
            background-color: #f0f7ff;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .action-btns {
            display: flex;
            gap: 5px;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }
        .accept-btn {
            background-color: #4CAF50;
        }
        .reject-btn {
            background-color: #f44336;
        }
        .view-btn {
            background-color: #2196F3;
        }
        .no-requests {
            color: #666;
            font-style: italic;
            padding: 20px;
            text-align: center;
        }

        .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 60px;
      background-color: #2c3e50;
      color: white;
      transition: width 0.3s;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .sidebar:hover {
      width: 200px;
    }
    .sidebar nav {
      display: flex;
      flex-direction: column;
      padding: 10px 0;
    }
    .sidebar nav a {
      color: white;
      text-decoration: none;
      padding: 12px 10px;
      margin-bottom: 5px;
      border-radius: 5px;
      display: flex;
      align-items: center;
      transition: background 0.3s;
      white-space: nowrap;
    }
    .sidebar nav a:hover {
      background-color: #34495e;
    }
    .sidebar nav i {
      margin: 0 10px;
      font-size: 18px;
      min-width: 20px;
      text-align: center;
    }
    .sidebar nav span {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }
    .sidebar:hover nav span {
      opacity: 1;
    }

    .logout {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 60px;
  width: 100%;
  margin-bottom: 20px;
  position: absolute;
  left: 27px;
  bottom: 5px;
}

.logout form {
  width: 100%;
}

.logout-btn {
  width: 100%;
  background: none;
  border: none;
  color: #ecf0f1;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 10px 0;
  transition: all 0.3s ease;
}


.logout-btn:hover{
  background-color: #fff;
  border-radius: 6px;
  color:rgba(181, 0, 0, 0.84);
}

.logout-btn i {
  font-size: 18px;
}

.logout-btn span {
  margin-left: 10px;
  opacity: 0;
  white-space: nowrap;
  transition: opacity 0.3s ease;
}

.sidebar:hover .logout-btn span {
  opacity: 1;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 5px;
    color: white;
    z-index: 1000;
    animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.notification.success {
    background-color: #4CAF50;
}

.notification.error {
    background-color: #f44336;
}

@keyframes slideIn {
    from { right: -300px; opacity: 0; }
    to { right: 20px; opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

    </style>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php if (isset($_SESSION['notification'])): ?>
    <div class="notification <?= $_SESSION['notification']['type'] ?>">
        <?= $_SESSION['notification']['message'] ?>
    </div>
    <?php unset($_SESSION['notification']); ?>
<?php endif; ?>

<div class="sidebar">
    <nav>
      <a href="../Account.php"><i class="fas fa-user-circle"></i> <span><?php echo htmlspecialchars($admin_name); ?></span></a>
      <br>
      <a href="#"><i class="fas fa-layer-group"></i> <span>Filières</span></a>
      <a href="#"><i class="fas fa-chalkboard-teacher"></i> <span>Enseignants</span></a>
      <a href="#"><i class="fas fa-user-graduate"></i> <span>Étudiants</span></a>
      <a href="Demandes.php"><i class="fas fa-envelope"></i> <span>Demandes</span></a>

    </nav>
    <div class="logout">
      <form action="" method="post" >
        <button type="submit" name="logout" class="logout-btn" ><i class="fas fa-sign-out-alt" ></i><span>Log out</span></button>
      </form>
    </div>
  </div>

    <div class="container">
        <h1>Tableau de Bord Administrateur</h1>
        
        <!-- Teacher Requests Section -->
        <div class="section">
            <h2>Demandes d'Enseignants</h2>
            
            <?php if (empty($teacherRequests)): ?>
                <p class="no-requests">Aucune demande d'enseignant en attente.</p>
            <?php else: ?>
                <!-- Group by Filière -->
                <?php 
                $groupedTeachers = [];
                foreach ($teacherRequests as $request) {
                    $groupedTeachers[$request['nom_filiere']][] = $request;
                }
                ?>
                
                <?php foreach ($groupedTeachers as $filiere => $requests): ?>
                    <div class="filiere-group">
                        <div class="filiere-title">Filière: <?= htmlspecialchars($filiere) ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Specialite</th>
                                    <th>Date Demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['nom']) ?></td>
                                    <td><?= htmlspecialchars($request['prenom']) ?></td>
                                    <td><?= htmlspecialchars($request['email']) ?></td>
                                    <td><?= htmlspecialchars($request['nom_filiere']) ?></td>
                                    <td><?= isset($request['date_demande']) ? date('d/m/Y H:i', strtotime($request['date_demande'])) : 'N/A' ?></td>
                                    <td>
    <div class="action-btns">
        <a href="view_request.php?id_demande=<?= htmlspecialchars($request['id_demande']) ?>&type=<?= htmlspecialchars($request['identite']) ?>" class="action-btn view-btn">Voir</a>
        <form action="process_request.php" method="post" style="display: inline;">
            <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
            <input type="hidden" name="type" value="enseignant">
            <button type="submit" name="action" value="accept" class="action-btn accept-btn">Accepter</button>
        </form>
        <form action="process_request.php" method="post" style="display: inline;">
            <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
            <input type="hidden" name="type" value="enseignant">
            <button type="submit" name="action" value="reject" class="action-btn reject-btn">Refuser</button>
        </form>
    </div>
</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Student Requests Section -->
        <div class="section">
            <h2>Demandes d'Étudiants</h2>
            
            <?php if (empty($studentRequests)): ?>
                <p class="no-requests">Aucune demande d'étudiant en attente.</p>
            <?php else: ?>
                <!-- Group by Filière -->
                <?php 
                $groupedStudents = [];
                foreach ($studentRequests as $request) {
                    $groupedStudents[$request['nom_filiere']][] = $request;
                }
                ?>
                
                <?php foreach ($groupedStudents as $filiere => $requests): ?>
                    <div class="filiere-group">
                        <div class="filiere-title">Filière: <?= htmlspecialchars($filiere) ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Note</th>
                                    <th>Date Demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['nom']) ?></td>
                                    <td><?= htmlspecialchars($request['prenom']) ?></td>
                                    <td><?= htmlspecialchars($request['email']) ?></td>
                                    <td><?= htmlspecialchars($request['note']) ?></td>
                                    <td><?= isset($request['date_demande']) ? date('d/m/Y H:i', strtotime($request['date_demande'])) : 'N/A' ?></td>
                                    <td>
    <div class="action-btns">
        <a href="view_request.php?id_demande=<?= htmlspecialchars($request['id_demande']) ?>&type=<?= htmlspecialchars($request['identite']) ?>" class="action-btn view-btn">Voir</a>
        <form action="process_request.php" method="post" style="display: inline;">
            <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
            <input type="hidden" name="type" value="etudiant">
            <button type="submit" name="action" value="accept" class="action-btn accept-btn">Accepter</button>
        </form>
        <form action="process_request.php" method="post" style="display: inline;">
            <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
            <input type="hidden" name="type" value="etudiant">
            <button type="submit" name="action" value="reject" class="action-btn reject-btn">Refuser</button>
        </form>
    </div>
</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>