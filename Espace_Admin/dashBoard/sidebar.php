<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
/* Sidebar Styles */
.sidebar {
    width: 250px;
    background: linear-gradient(180deg, #1b263b, #3a0ca3);
    color: white;
    padding: 20px 0;
    transition: all 0.3s ease;
    position: fixed;
    height: 100%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-menu a:hover, 
.sidebar-menu a.active {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-left: 3px solid #4cc9f0;
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
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-graduation-cap"></i> <span>SchoolAdmin</span></h3>
    </div>
    
    <nav class="sidebar-menu">
        <a href="/Espace_Admin/dashBoard/Main.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Main.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Acceuil</span>
        </a>
        <a href="/Espace_Admin/dashBoard/gerer_Filieres/filieres.php" class="<?= basename($_SERVER['PHP_SELF']) == 'filieres.php' ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i>
            <span>Filières</span>
        </a>
        <a href="gerer_Enseignants/enseignants.php" class="<?= basename($_SERVER['PHP_SELF']) == 'enseignants.php' ? 'active' : '' ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Enseignants</span>
        </a>
        <a href="/gerer_Demandes/Demandes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Demandes.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i>
            <span>Demandes</span>
        </a>
        <a href="/Espace_Admin/dashBoard/pageAccount.php" class="<?= basename($_SERVER['PHP_SELF']) == 'Account.php' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        </a>
    </nav>
    
    <button class="logout-btn" onclick="window.location.href='../../../logOut/logOut.php'">
        <i class="fas fa-sign-out-alt"></i>
        <span>Déconnexion</span>
    </button>
</aside>