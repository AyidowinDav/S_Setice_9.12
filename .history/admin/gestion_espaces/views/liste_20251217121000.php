<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Espaces Pédagogiques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 25px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-warning {
            border-radius: 25px;
        }
        .btn-danger {
            border-radius: 25px;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-graduation-cap"></i> Gestion des Espaces Pédagogiques
                        </h2>
                        <a href="index.php?action=create" class="btn btn-light">
                            <i class="fas fa-plus"></i> Nouveau Espace
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php
                                switch ($_GET['success']) {
                                    case 1: echo "Espace créé avec succès."; break;
                                    case 2: echo "Espace modifié avec succès."; break;
                                    case 3: echo "Espace supprimé avec succès."; break;
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php
                                switch ($_GET['error']) {
                                    case 1: echo "Espace non trouvé."; break;
                                    case 2: echo "Erreur lors de la suppression."; break;
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($espaces)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Aucun espace pédagogique trouvé</h4>
                                <p class="text-muted">Commencez par créer votre premier espace pédagogique.</p>
                                <a href="index.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Créer le premier espace
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom de la Matière</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($espaces as $espace): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($espace['id']); ?></td>
                                                <td><?php echo htmlspecialchars($espace['nom_matiere']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($espace['description'], 0, 100)) . (strlen($espace['description']) > 100 ? '...' : ''); ?></td>
                                                <td>
                                                    <a href="index.php?action=edit&id=<?php echo $espace['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a>
                                                    <a href="index.php?action=delete&id=<?php echo $espace['id']; ?>" class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet espace ?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
