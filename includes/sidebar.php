<style>
    :root {
        --active-bg: #f1f5f9;
        --primary-blue: #1a4a8e;
        --text-muted: #64748b;
    }
    .sidebar {
        width: 280px; height: 100vh; background: #ffffff;
        position: fixed; left: 0; top: 0;
        display: flex; flex-direction: column;
        padding: 40px 20px; border-right: 1px solid #e2e8f0;
        box-sizing: border-box; z-index: 1000;
    }
    .logo-section { margin-bottom: 40px; padding-left: 15px; }
    .logo-section h2 { font-family: 'Inter', sans-serif; font-weight: 800; color: var(--primary-blue); font-size: 26px; margin:0; }
    .sidebar-menu ul { list-style: none; padding: 0; }
    .nav-item {
        display: flex; align-items: center; padding: 12px 18px;
        color: var(--text-muted); text-decoration: none;
        border-radius: 12px; margin-bottom: 5px;
        transition: all 0.2s ease; font-weight: 500; font-size: 15px;
    }
    .nav-item i { margin-right: 14px; font-size: 18px; width: 20px; text-align: center; }
    .nav-item:hover, .nav-item.active { background: var(--active-bg); color: var(--primary-blue); font-weight: 600; }
    .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    .logout-item { color: #ef4444; }
</style>

<aside class="sidebar">
    <div class="logo-section"><h2>S_SETICE</h2></div>
    <nav class="sidebar-menu">
        <ul>
    <li><a href="dashboard.php" class="nav-item active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
    <li><a href="promotions.php" class="nav-item"><i class="fas fa-graduation-cap"></i> Créer Promotion</a></li>
    <li><a href="etudiants.php" class="nav-item"><i class="fas fa-user-plus"></i> Créer Étudiant</a></li>
    <li><a href="formateurs.php" class="nav-item"><i class="fas fa-chalkboard-teacher"></i> Créer Formateur</a></li>
    <li><a href="espaces.php" class="nav-item"><i class="fas fa-folder-plus"></i> Créer Espace</a></li>
    <li><a href="consulter_espaces.php" class="nav-item"><i class="fas fa-eye"></i> Consulter Espaces</a></li>
    <li><a href="assignation_globale.php" class="nav-item"><i class="fas fa-link"></i> Assignations</a></li>
</ul>
    </nav>
    <div class="sidebar-footer">
    <a href="../actions/logout.php" class="nav-item logout-item">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
</div>
</aside>