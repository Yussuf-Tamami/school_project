<?php
session_start();
require_once '../../../dataBase/connection.php';

if (!isset($_SESSION['admin_name'])) {
    header('Location: ../../logIn/logIn.php');
    exit;
}

$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

// Filter variables
$selected_teacher_filiere = $_GET['teacher_filiere'] ?? '';
$selected_teacher_matiere = $_GET['matiere'] ?? '';
$selected_student_filiere = $_GET['student_filiere'] ?? '';

try {
    // Get all filières
    $stmt = $dba->prepare("SELECT id_filiere, nom_filiere FROM filieres ORDER BY nom_filiere");
    $stmt->execute();
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get matières if filière is selected
    $teacher_matieres = [];
    if ($selected_teacher_filiere) {
        $stmt = $dba->prepare("SELECT id_matiere, nom_matiere FROM matieres WHERE id_filiere = ? ORDER BY nom_matiere");
        $stmt->execute([$selected_teacher_filiere]);
        $teacher_matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Teacher requests query
    $teacherQuery = "
        SELECT d.*, f.nom_filiere, m.nom_matiere 
        FROM demandes d
        JOIN filieres f ON d.id_filiere_demandé = f.id_filiere
        LEFT JOIN matieres m ON d.id_matiere_demandé = m.id_matiere
        WHERE d.identite = 'enseignant' AND d.status = 'waiting'
    ";

    $teacher_params = [];
    if ($selected_teacher_filiere) {
        $teacherQuery .= " AND d.id_filiere_demandé = ?";
        $teacher_params[] = $selected_teacher_filiere;
    }
    if ($selected_teacher_matiere) {
        $teacherQuery .= " AND d.id_matiere_demandé = ?";
        $teacher_params[] = $selected_teacher_matiere;
    }

    $teacherQuery .= " ORDER BY f.nom_filiere, m.nom_matiere";
    $stmt = $dba->prepare($teacherQuery);
    $stmt->execute($teacher_params);
    $teacherRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Student requests query
    $studentQuery = "
        SELECT d.*, f.nom_filiere 
        FROM demandes d
        JOIN filieres f ON d.id_filiere_demandé = f.id_filiere
        WHERE d.identite = 'etudiant' AND d.status = 'waiting'
    ";

    $student_params = [];
    if ($selected_student_filiere) {
        $studentQuery .= " AND d.id_filiere_demandé = ?";
        $student_params[] = $selected_student_filiere;
    }

    $studentQuery .= " ORDER BY f.nom_filiere, note desc";
    $stmt = $dba->prepare($studentQuery);
    $stmt->execute($student_params);
    $studentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Demandes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #3f37c9;
            --secondary: #3a0ca3;
            --dark: #1b263b;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--dark), var(--secondary));
            color: white;
            padding: 20px 0;
            transition: var(--transition);
            position: fixed;
            height: 100%;
            box-shadow: var(--box-shadow);
            z-index: 100;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h3 i {
            font-size: 1.5rem;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid var(--success);
        }

        .sidebar-menu a i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 12px;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--dark);
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-profile span {
            font-weight: 500;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-header h2 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Filter Styles */
        .filter-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .filter-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            background: white;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-waiting {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            gap: 5px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.75rem;
        }

        .btn-view {
            background-color: var(--info);
            color: white;
        }

        .btn-view:hover {
            background-color: #3a7bd5;
        }

        .btn-accept {
            background-color: var(--success);
            color: white;
        }

        .btn-accept:hover {
            background-color: #3aa8d5;
        }

        .btn-reject {
            background-color: var(--danger);
            color: white;
        }

        .btn-reject:hover {
            background-color: #d51a6a;
        }

        .btn-group {
            display: flex;
            gap: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            font-weight: 500;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6c757d;
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: var(--border-radius);
            color: white;
            z-index: 1000;
            animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
            box-shadow: var(--box-shadow);
        }

        .notification.success {
            background-color: var(--success);
        }

        .notification.error {
            background-color: var(--danger);
        }

        @keyframes slideIn {
            from { right: -300px; opacity: 0; }
            to { right: 20px; opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar:hover {
                width: 250px;
            }
            
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar:hover .sidebar-menu a span {
                display: inline;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .sidebar:hover ~ .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-graduation-cap"></i> <span>SchoolAdmin</span></h3>
            </div>
            
            <nav class="sidebar-menu">
                
                <a href="../Main.php">
                    <i class="fas fa-home"></i>
                    <span>Acceuil</span>
                </a>
                <a href="../gerer_Filieres/filieres.php">
                    <i class="fas fa-layer-group"></i>
                    <span>Filières</span>
                </a>
                <a href="../gerer_Enseignants/enseignants.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Enseignants</span>
                </a>
                <a href="Demandes.php" class="active">
                    <i class="fas fa-envelope"></i>
                    <span>Demandes</span>
                </a>
                <a href="../Account.php">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($admin_name); ?></span>
                </a>
            </nav>
            
            <button class="logout-btn" onclick="window.location.href='../../../logOut/logOut.php'">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </button>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if (isset($_SESSION['notification'])): ?>
                <div class="notification <?= $_SESSION['notification']['type'] ?>">
                    <?= $_SESSION['notification']['message'] ?>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>

            <div class="header">
                <h1>Gestion des Demandes</h1>
                <div class="user-profile">
                    <a href="../pageAccount.php">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_name) ?>&background=random" alt="Admin"></a>
                    <span><?php echo htmlspecialchars($admin_name); ?></span>
                </div>
            </div>

            <!-- Teacher Requests Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chalkboard-teacher mr-2"></i> Demandes des Enseignants</h2>
                </div>
                
                <form action="Demandes.php" method="GET">
    <div class="filter-group">
        <label for="teacher_filiere">Filière</label>
        <select name="teacher_filiere" id="teacher_filiere" class="filter-select" onchange="this.form.submit()" required>
            <option value="" disabled selected>Sélectionner une filière</option>
            <?php foreach ($filieres as $filiere): ?>
                <option value="<?= $filiere['id_filiere'] ?>" <?= $selected_teacher_filiere == $filiere['id_filiere'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($filiere['nom_filiere']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($selected_teacher_filiere): ?>
    <div class="filter-group">
        <label for="teacher_matiere">Matière</label>
        <select name="matiere" id="teacher_matiere" class="filter-select" onchange="this.form.submit()">
            <option value="" disabled selected>Sélectionner une matière</option>
            <?php foreach ($teacher_matieres as $matiere): ?>
                <option value="<?= $matiere['id_matiere'] ?>" <?= $selected_teacher_matiere == $matiere['id_matiere'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($matiere['nom_matiere']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</form>

                <?php if (!$selected_teacher_filiere): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune filière sélectionnée</h3>
                        <p>Veuillez sélectionner une filière pour afficher les demandes</p>
                    </div>
                <?php elseif (empty($teacherRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Aucune demande disponible</h3>
                        <p>Il n'y a pas de demandes d'enseignants pour les critères sélectionnés</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Spécialité</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teacherRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['nom']) ?></td>
                                    <td><?= htmlspecialchars($request['prenom']) ?></td>
                                    <td><?= htmlspecialchars($request['email']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($request['nom_filiere']) ?>
                                        <?php if ($request['nom_matiere']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($request['nom_matiere']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= isset($request['date_demande']) ? date('d/m/Y H:i', strtotime($request['date_demande'])) : 'N/A' ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_request.php?id_demande=<?= htmlspecialchars($request['id_demande']) ?>&type=<?= htmlspecialchars($request['identite']) ?>" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <form action="Actions_demande.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
                                                <input type="hidden" name="type" value="enseignant">
                                                <button type="submit" name="action" value="accept" class="btn btn-accept btn-sm">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                            </form>
                                            <form action="Actions_demande.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
                                                <input type="hidden" name="type" value="enseignant">
                                                <button type="submit" name="action" value="reject" class="btn btn-reject btn-sm">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Student Requests Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-graduate mr-2"></i> Demandes des Étudiants</h2>
                </div>
                
                <div class="filter-section">
                  <form action="" method="get">
                    <div class="filter-group">
                        <label for="student_filiere">Filière</label>
                        <select name="student_filiere" id="student_filiere" class="filter-select" onchange="this.form.submit()" required>
                            <option value="" disabled selected>Sélectionner une filière</option>
                            <?php foreach ($filieres as $filiere): ?>
                                <option value="<?= $filiere['id_filiere'] ?>" <?= $selected_student_filiere == $filiere['id_filiere'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($filiere['nom_filiere']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                  </form>
                </div>
                
                <?php if (!$selected_student_filiere): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune filière sélectionnée</h3>
                        <p>Veuillez sélectionner une filière pour afficher les demandes</p>
                    </div>
                <?php elseif (empty($studentRequests)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Aucune demande disponible</h3>
                        <p>Il n'y a pas de demandes d'étudiants pour la filière sélectionnée</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Note</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentRequests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['nom']) ?></td>
                                    <td><?= htmlspecialchars($request['prenom']) ?></td>
                                    <td><?= htmlspecialchars($request['email']) ?></td>
                                    <td><?= htmlspecialchars($request['note']) ?></td>
                                    <td><?= isset($request['date_demande']) ? date('d/m/Y H:i', strtotime($request['date_demande'])) : 'N/A' ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view_request.php?id_demande=<?= htmlspecialchars($request['id_demande']) ?>&type=<?= htmlspecialchars($request['identite']) ?>" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <form action="Actions_demande.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
                                                <input type="hidden" name="type" value="etudiant">
                                                <button type="submit" name="action" value="accept" class="btn btn-accept btn-sm">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                            </form>
                                            <form action="Actions_demande.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id" value="<?= $request['id_demande'] ?>">
                                                <input type="hidden" name="type" value="etudiant">
                                                <button type="submit" name="action" value="reject" class="btn btn-reject btn-sm">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <br>
        </main>
    </div>
</body>
</html>