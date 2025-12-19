<?php
include 'config/db.php';

try {
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS s_setice");
    $pdo->exec("USE s_setice");

    // Drop tables if they exist (in reverse order to avoid foreign key constraints)
    $pdo->exec("DROP TABLE IF EXISTS espace_etudiant");
    $pdo->exec("DROP TABLE IF EXISTS espace_enseignant");
    $pdo->exec("DROP TABLE IF EXISTS promotion_etudiant");
    $pdo->exec("DROP TABLE IF EXISTS espace_pedagogique");
    $pdo->exec("DROP TABLE IF EXISTS promotions");
    $pdo->exec("DROP TABLE IF EXISTS utilisateur");

    // Create tables
    $sql = "
    -- Table Utilisateur
    CREATE TABLE utilisateur (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('DIRECTEUR', 'FORMATEUR', 'ETUDIANT') NOT NULL
    ) ENGINE=InnoDB;

    -- Table Promotion
    CREATE TABLE promotions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom_promotion VARCHAR(100) NOT NULL,
        annee_academique VARCHAR(20) NOT NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    -- Table Espace Pédagogique
    CREATE TABLE espace_pedagogique (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom_matiere VARCHAR(100) NOT NULL,
        description TEXT
    ) ENGINE=InnoDB;

    -- Table de liaison : Promotion <-> Étudiant
    CREATE TABLE promotion_etudiant (
        id_promotion INT,
        id_utilisateur INT,
        PRIMARY KEY (id_promotion, id_utilisateur),
        FOREIGN KEY (id_promotion) REFERENCES promotions(id) ON DELETE CASCADE,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    -- Table de liaison : Espace <-> Formateur
    CREATE TABLE espace_enseignant (
        id_espace INT,
        id_utilisateur INT,
        PRIMARY KEY (id_espace, id_utilisateur),
        FOREIGN KEY (id_espace) REFERENCES espace_pedagogique(id) ON DELETE CASCADE,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    -- Table de liaison : Espace <-> Étudiant
    CREATE TABLE espace_etudiant (
        id_espace INT,
        id_utilisateur INT,
        PRIMARY KEY (id_espace, id_utilisateur),
        FOREIGN KEY (id_espace) REFERENCES espace_pedagogique(id) ON DELETE CASCADE,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ";

    $pdo->exec($sql);
    echo "Database and tables created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
