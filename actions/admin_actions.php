// SSS-6 : Création d'un étudiant et affectation à une promotion
if (isset($_POST['create_student'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id_promo = $_POST['id_promotion'];

    try {
        $pdo->beginTransaction();

        // 1. Créer l'utilisateur avec le rôle ETUDIANT
        $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'ETUDIANT')");
        $stmt->execute([$nom, $prenom, $email, $password]);
        
        // Récupérer l'ID de l'étudiant juste créé
        $id_etudiant = $pdo->lastInsertId();

        // 2. Créer le lien dans la table de liaison promotion_etudiant
        $stmt2 = $pdo->prepare("INSERT INTO promotion_etudiant (id_promotion, id_utilisateur) VALUES (?, ?)");
        $stmt2->execute([$id_promo, $id_etudiant]);

        $pdo->commit();
        header('Location: ../admin/gestion_etudiants.php?success=1');
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de la création : " . $e->getMessage());
    }
}