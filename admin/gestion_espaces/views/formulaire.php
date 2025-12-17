<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($espace) ? 'Modifier' : 'Créer'; ?> un Espace Pédagogique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('assets/<?php echo isset($espace) ? 'image3.jpeg' : 'image2.jpeg'; ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.9);
        }
        .card-header h2 {
            font-size: 2rem;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 25px;
            font-size: 1.1rem;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            border-radius: 25px;
            font-size: 1.1rem;
        }
        .form-control {
            border-radius: 10px;
            font-size: 1.1rem;
        }
        .form-label {
            font-weight: 600;
            font-size: 1.2rem;
        }
        .alert {
            border-radius: 10px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card" style="max-width: 800px; margin: 0 auto;">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="fas fa-graduation-cap"></i>
                            <?php echo isset($espace) ? 'Modifier' : 'Créer'; ?> un Espace Pédagogique
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="index.php?action=<?php echo isset($espace) ? 'update&id=' . $espace['id'] : 'store'; ?>">
                            <div class="mb-3">
                                <label for="nom_matiere" class="form-label">
                                    <i class="fas fa-book"></i> Nom de la Matière *
                                </label>
                                <input type="text" class="form-control" id="nom_matiere" name="nom_matiere"
                                       value="<?php echo isset($espace) ? htmlspecialchars($espace['nom_matiere']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Description *
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($espace) ? htmlspecialchars($espace['description']) : ''; ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php?action=index" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour à la liste
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo isset($espace) ? 'Modifier' : 'Créer'; ?> l'Espace
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
