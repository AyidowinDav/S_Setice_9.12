<?php
// On récupère le rôle pour adapter l'affichage
$role = $_SESSION['role'] ?? '';
?>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
    .sidebar {
        width: 280px; min-height: 100vh; background: #ffffff;
        position: fixed; left: 0; top: 0;
        display: flex; flex-direction: column;
        padding: 40px 20px; border-right: 1px solid #e2e8f0;
        box-sizing: border-box; z-index: 1000;
    }
    .logo-section { margin-bottom: 40px; padding-left: 15px; }
    .logo-section h2 { font-family: 'Inter', sans-serif; font-weight: 800; color: #1a4a8e; font-size: 26px; margin:0; }
    .sidebar-menu {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 20px;
    }
    .sidebar-menu ul { list-style: none; padding: 0; }
    .nav-item {
        display: flex; align-items: center; padding: 12px 18px;
        color: #64748b; text-decoration: none;
        border-radius: 12px; margin-bottom: 5px;
        transition: all 0.2s ease; font-weight: 500; font-size: 15px;
    }
    .nav-item i { margin-right: 14px; font-size: 18px; width: 20px; text-align: center; }
    .nav-item:hover, .nav-item.active { background: #f1f5f9; color: #1a4a8e; font-weight: 600; }
    .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    .logout-item { color: #ef4444; }
    .role-badge {
        font-size: 10px; background: #e2e8f0; color: #475569;
        padding: 2px 8px; border-radius: 20px; margin-left: 15px;
        text-transform: uppercase;
    }

    /* Mode Sombre */
    body.dark-mode {
        background-color: #111827;
        color: #f9fafb;
    }
    
    body.dark-mode .sidebar {
        background: #1f2937;
        border-right-color: #374151;
    }
    
    body.dark-mode .nav-item {
        color: #9ca3af;
    }
    
    body.dark-mode .nav-item:hover,
    body.dark-mode .nav-item.active {
        background: #374151;
        color: #60a5fa;
    }
    
    body.dark-mode .role-badge {
        background: #374151;
        color: #9ca3af;
    }
    
    body.dark-mode .toast {
        background: #374151;
        color: #f9fafb;
    }
    
    body.dark-mode .toast-success {
        background: #059669;
    }
    
    body.dark-mode .toast-error {
        background: #dc2626;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeText = document.getElementById('theme-text');
    const themeIcon = themeToggle.querySelector('i');
    const body = document.body;
    
    // Vérifier le thème sauvegardé
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeText.textContent = 'Mode Clair';
        themeIcon.className = 'fas fa-sun';
    }
    
    themeToggle.addEventListener('click', function() {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeText.textContent = 'Mode Clair';
            themeIcon.className = 'fas fa-sun';
        } else {
            localStorage.setItem('theme', 'light');
            themeText.textContent = 'Mode Sombre';
            themeIcon.className = 'fas fa-moon';
        }
    });

    // Système de notifications Toast
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
            ${message}
            <button class="toast-close">&times;</button>
        `;
        
        document.body.appendChild(toast);
        
        // Animation d'entrée
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Auto-disparition
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
        
        // Fermeture manuelle
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    }

    // Convertir les messages existants en toasts
    const errorDiv = document.querySelector('.alert-error');
    const successDiv = document.querySelector('.alert-success');
    
    if (errorDiv) {
        showToast(errorDiv.textContent.trim(), 'error');
        errorDiv.remove();
    }
    
    if (successDiv) {
        showToast(successDiv.textContent.trim(), 'success');
        successDiv.remove();
    }
});
</script>

<style>
/* Styles pour les toasts */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    color: #333;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    transform: translateX(400px);
    transition: transform 0.3s ease;
    max-width: 400px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast.show {
    transform: translateX(0);
}

.toast-success {
    background: #10b981;
    color: white;
}

.toast-error {
    background: #ef4444;
    color: white;
}

.toast-close {
    background: none;
    border: none;
    color: inherit;
    font-size: 20px;
    cursor: pointer;
    margin-left: auto;
    opacity: 0.8;
}

.toast-close:hover {
    opacity: 1;
}
</style>

<aside class="sidebar">
    <div class="logo-section">
        <h2>S_SETICE</h2>
        <span class="role-badge"><?= $role ?></span>
    </div>
    
    <nav class="sidebar-menu">
        <ul>
            <li>
                <a href="<?= ($role === 'ETUDIANT') ? 'etudiant_dashboard.php' : (($role === 'FORMATEUR') ? 'formateur_dashboard.php' : 'dashboard.php') ?>" 
                   class="nav-item active">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>

            <?php if ($role === 'DIRECTEUR' || $role === 'ADMIN'): ?>
                <li><a href="promotions.php" class="nav-item"><i class="fas fa-graduation-cap"></i> Créer Promotion</a></li>
                <li><a href="etudiants.php" class="nav-item"><i class="fas fa-user-plus"></i> Créer Étudiant</a></li>
                <li><a href="formateurs.php" class="nav-item"><i class="fas fa-chalkboard-teacher"></i> Créer Formateur</a></li>
                <li><a href="espaces.php" class="nav-item"><i class="fas fa-folder-plus"></i> Créer Espace</a></li>
                <li><a href="consulter_espaces.php" class="nav-item"><i class="fas fa-eye"></i> Consulter Espaces</a></li>
                <li><a href="assignation_globale.php" class="nav-item"><i class="fas fa-link"></i> Assignations</a></li>
            <?php endif; ?>

            <?php if ($role === 'FORMATEUR'): ?>
                <li><a href="mes_cours.php" class="nav-item"><i class="fas fa-book"></i> Mes Cours</a></li>
                <li><a href="nouveau_travail.php" class="nav-item"><i class="fas fa-file-upload"></i> Définir un Travail</a></li>
                <li><a href="notation.php" class="nav-item"><i class="fas fa-marker"></i> Noter les copies</a></li>
                <li><a href="mon_planning.php" class="nav-item"><i class="fas fa-calendar-alt"></i> Emploi du temps</a></li>
            <?php endif; ?>

            <?php if ($role === 'ETUDIANT'): ?>
                <li><a href="mes_travaux.php" class="nav-item"><i class="fas fa-tasks"></i> Travaux à rendre</a></li>
                <li><a href="mes_notes.php" class="nav-item"><i class="fas fa-graduation-cap"></i> Mes Notes</a></li>
                <li><a href="mon_espace.php" class="nav-item"><i class="fas fa-folder-open"></i> Ressources de cours</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <button id="theme-toggle" class="nav-item" style="width: 100%; justify-content: center; margin-bottom: 10px; background: #f1f5f9;">
            <i class="fas fa-moon"></i> <span id="theme-text">Mode Sombre</span>
        </button>
        <a href="../actions/logout.php" class="nav-item logout-item">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>