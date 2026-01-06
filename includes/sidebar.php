<?php
// CRITIQUE : Ne pas démarrer une nouvelle session si elle existe déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer le rôle et la page actuelle
$role = $_SESSION['role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);

// Définition du lien Dashboard selon le rôle
$dash_link = 'dashboard.php';
if ($role === 'ETUDIANT') {
    $dash_link = 'etudiant_dashboard.php';
} elseif ($role === 'FORMATEUR') {
    $dash_link = 'formateur_dashboard.php';
}
?>

<style>
    html, body { height: 100%; margin: 0; padding: 0; }
    .sidebar {
        width: 280px; min-height: 100vh; background: #ffffff;
        position: fixed; left: 0; top: 0;
        display: flex; flex-direction: column;
        padding: 40px 20px; border-right: 1px solid #e2e8f0;
        box-sizing: border-box; z-index: 1000;
    }
    .logo-section { margin-bottom: 40px; padding-left: 15px; }
    .logo-section h2 { 
        font-family: 'Inter', sans-serif; 
        font-weight: 800; 
        color: #1a4a8e; 
        font-size: 26px; 
        margin:0; 
    }
    .sidebar-menu { flex: 1; overflow-y: auto; margin-bottom: 20px; }
    .sidebar-menu ul { list-style: none; padding: 0; margin: 0; }
    .nav-item {
        display: flex; align-items: center; padding: 12px 18px;
        color: #64748b; text-decoration: none;
        border-radius: 12px; margin-bottom: 5px;
        transition: all 0.2s ease; font-weight: 500; font-size: 15px;
    }
    .nav-item i { 
        margin-right: 14px; 
        font-size: 18px; 
        width: 20px; 
        text-align: center; 
    }
    .nav-item:hover, .nav-item.active { 
        background: #f1f5f9; 
        color: #1a4a8e; 
        font-weight: 600; 
    }
    .sidebar-footer { 
        margin-top: auto; 
        padding-top: 20px; 
        border-top: 1px solid #f1f5f9; 
    }
    .logout-item { color: #ef4444; }
    .role-badge {
        font-size: 10px; background: #e2e8f0; color: #475569;
        padding: 2px 8px; border-radius: 20px; margin-left: 15px;
        text-transform: uppercase; font-weight: 600;
    }
    
    /* Mode Sombre - Application globale complète */
    body.dark-mode {
        background-color: #111827 !important;
        color: #f9fafb !important;
    }

    /* Sidebar */
    body.dark-mode .sidebar {
        background: #1f2937 !important;
        border-right-color: #374151 !important;
    }
    body.dark-mode .nav-item { color: #9ca3af !important; }
    body.dark-mode .nav-item:hover, body.dark-mode .nav-item.active {
        background: #374151 !important;
        color: #60a5fa !important;
    }

    /* Éléments principaux de la page */
    body.dark-mode main {
        background-color: #111827 !important;
        color: #f9fafb !important;
    }

    /* Tous les éléments avec arrière-plan blanc */
    body.dark-mode * {
        background-color: transparent !important;
    }

    /* Cartes et conteneurs spécifiques */
    body.dark-mode div[style*="background: white"],
    body.dark-mode div[style*="background:white"],
    body.dark-mode .card,
    body.dark-mode .container,
    body.dark-mode div[style*="border-radius: 20px"],
    body.dark-mode div[style*="border-radius:10px"] {
        background: #1f2937 !important;
        border-color: #374151 !important;
        color: #f9fafb !important;
    }

    /* Titres */
    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3,
    body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
        color: #f9fafb !important;
    }

    /* Texte général */
    body.dark-mode p, body.dark-mode span, body.dark-mode div,
    body.dark-mode li, body.dark-mode td, body.dark-mode th {
        color: #d1d5db !important;
    }

    /* Liens */
    body.dark-mode a {
        color: #60a5fa !important;
    }
    body.dark-mode a:hover {
        color: #93c5fd !important;
    }

    /* Boutons */
    body.dark-mode button,
    body.dark-mode input[type="submit"],
    body.dark-mode input[type="button"],
    body.dark-mode a[style*="background"] {
        background: #374151 !important;
        color: #f9fafb !important;
        border-color: #4b5563 !important;
    }
    body.dark-mode button:hover,
    body.dark-mode input[type="submit"]:hover,
    body.dark-mode input[type="button"]:hover,
    body.dark-mode a[style*="background"]:hover {
        background: #4b5563 !important;
    }

    /* Champs de formulaire */
    body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
        background: #374151 !important;
        color: #f9fafb !important;
        border-color: #4b5563 !important;
    }
    body.dark-mode input:focus, body.dark-mode select:focus, body.dark-mode textarea:focus {
        border-color: #60a5fa !important;
        box-shadow: 0 0 0 1px #60a5fa !important;
    }

    /* Labels */
    body.dark-mode label {
        color: #d1d5db !important;
    }

    /* Messages d'erreur et succès */
    body.dark-mode div[style*="background: #fee"],
    body.dark-mode div[style*="background:#fee"] {
        background: #7f1d1d !important;
        color: #fca5a5 !important;
    }
    body.dark-mode div[style*="background: #efe"],
    body.dark-mode div[style*="background:#efe"] {
        background: #14532d !important;
        color: #86efac !important;
    }

    /* Tables */
    body.dark-mode table {
        background: #1f2937 !important;
        color: #f9fafb !important;
        border-color: #374151 !important;
    }
    body.dark-mode th, body.dark-mode td {
        background: #1f2937 !important;
        color: #f9fafb !important;
        border-color: #374151 !important;
    }

    /* Modals et overlays */
    body.dark-mode div[style*="background: white"][style*="position: fixed"],
    body.dark-mode div[style*="background:white"][style*="position: fixed"],
    body.dark-mode #modalAssign div[style*="background: white"],
    body.dark-mode #modalAssign div[style*="background:white"],
    body.dark-mode div[style*="background: rgba(0,0,0,0.5)"],
    body.dark-mode div[style*="background:rgba(0,0,0,0.5)"] {
        background: #1f2937 !important;
        color: #f9fafb !important;
    }

    /* Overlay modal spécifique */
    body.dark-mode #modalAssign {
        background: rgba(0,0,0,0.8) !important;
    }

    /* Icônes */
    body.dark-mode .fas, body.dark-mode .far, body.dark-mode .fab,
    body.dark-mode i {
        color: #d1d5db !important;
    }

    /* Éléments spécifiques avec styles inline */
    body.dark-mode div[style*="background: linear-gradient"] {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%) !important;
    }

    /* Boutons avec couleurs spécifiques */
    body.dark-mode a[style*="background: #1a4a8e"] {
        background: #374151 !important;
        color: #60a5fa !important;
    }
    body.dark-mode a[style*="background: #dc2626"] {
        background: #7f1d1d !important;
        color: #fca5a5 !important;
    }
    body.dark-mode a[style*="background: #f1f5f9"] {
        background: #374151 !important;
        color: #60a5fa !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const themeText = document.getElementById('theme-text');
        const body = document.body;
        
        // Gestion Thème
        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            if(themeText) themeText.textContent = 'Mode Clair';
        }
        
        if (themeToggle) {
            themeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                body.classList.toggle('dark-mode');
                const isDark = body.classList.contains('dark-mode');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                if (themeText) {
                    themeText.textContent = isDark ? 'Mode Clair' : 'Mode Sombre';
                }
            });
        }
    });
</script>

<aside class="sidebar">
    <div class="logo-section">
        <h2>S-SETICE</h2>
        <span class="role-badge"><?= htmlspecialchars($role) ?></span>
    </div>
    
    <nav class="sidebar-menu">
        <ul>
            <li>
                <a href="<?= $dash_link ?>" class="nav-item <?= ($current_page == $dash_link) ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>

            <?php if ($role === 'DIRECTEUR' || $role === 'ADMIN'): ?>
                <li><a href="promotions.php" class="nav-item <?= ($current_page == 'promotions.php') ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap"></i> Promotions
                </a></li>
                <li><a href="etudiants.php" class="nav-item <?= ($current_page == 'etudiants.php') ? 'active' : '' ?>">
                    <i class="fas fa-user-graduate"></i> Étudiants
                </a></li>
                <li><a href="formateurs.php" class="nav-item <?= ($current_page == 'formateurs.php') ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i> Formateurs
                </a></li>
                <li><a href="espaces.php" class="nav-item <?= ($current_page == 'espaces.php') ? 'active' : '' ?>">
                    <i class="fas fa-folder-plus"></i> Espaces
                </a></li>
                <li><a href="consulter_espaces.php" class="nav-item <?= ($current_page == 'consulter_espaces.php') ? 'active' : '' ?>">
                    <i class="fas fa-eye"></i> Consulter Espaces
                </a></li>
                <li><a href="assignation_globale.php" class="nav-item <?= ($current_page == 'assignation_globale.php') ? 'active' : '' ?>">
                    <i class="fas fa-link"></i> Assignations
                </a></li>
                <li><a href="classement_general.php" class="nav-item <?= ($current_page == 'classement_general.php') ? 'active' : '' ?>">
                    <i class="fas fa-trophy"></i> Classement
                </a></li>
            <?php endif; ?>

            <?php if ($role === 'FORMATEUR'): ?>
                <li><a href="nouveau_travail.php" class="nav-item <?= ($current_page == 'nouveau_travail.php') ? 'active' : '' ?>">
                    <i class="fas fa-file-upload"></i> Créer Travail
                </a></li>
                <li><a href="mes_travaux_formateur.php" class="nav-item <?= ($current_page == 'mes_travaux_formateur.php') ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> Mes Travaux
                </a></li>
                <li><a href="evaluer_travaux.php" class="nav-item <?= ($current_page == 'evaluer_travaux.php') ? 'active' : '' ?>">
                    <i class="fas fa-marker"></i> Évaluer
                </a></li>
            <?php endif; ?>

            <?php if ($role === 'ETUDIANT'): ?>
                <li><a href="mes_travaux.php" class="nav-item <?= ($current_page == 'mes_travaux.php') ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> Mes Travaux
                </a></li>
                <li><a href="mes_notes.php" class="nav-item <?= ($current_page == 'mes_notes.php') ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> Mes Notes
                </a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <button id="theme-toggle" class="nav-item" style="width: 100%; justify-content: center; margin-bottom: 10px; background: #f1f5f9; border:none; cursor:pointer;">
            <i class="fas fa-moon"></i> <span id="theme-text">Mode Sombre</span>
        </button>
        <a href="../actions/logout.php" class="nav-item logout-item">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>