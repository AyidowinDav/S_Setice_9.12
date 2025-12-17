-- Création de la base de données
CREATE DATABASE IF NOT EXISTS s_setice;
USE s_setice;

-- Table Utilisateur
CREATE TABLE IF NOT EXISTS utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('DIRECTEUR', 'FORMATEUR', 'ETUDIANT') NOT NULL
) ENGINE=InnoDB;

-- Table Promotion
CREATE TABLE IF NOT EXISTS promotion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_promotion VARCHAR(100) NOT NULL,
    annee INT NOT NULL,
    code_access VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- Table Espace Pédagogique
CREATE TABLE IF NOT EXISTS espace_pedagogique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_matiere VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

-- Table de liaison : Promotion <-> Étudiant
CREATE TABLE IF NOT EXISTS promotion_etudiant (
    id_promotion INT,
    id_utilisateur INT,
    PRIMARY KEY (id_promotion, id_utilisateur),
    FOREIGN KEY (id_promotion) REFERENCES promotion(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table de liaison : Espace <-> Formateur
CREATE TABLE IF NOT EXISTS espace_enseignant (
    id_espace INT,
    id_utilisateur INT,
    PRIMARY KEY (id_espace, id_utilisateur),
    FOREIGN KEY (id_espace) REFERENCES espace_pedagogique(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table de liaison : Espace <-> Étudiant
CREATE TABLE IF NOT EXISTS espace_etudiant (
    id_espace INT,
    id_utilisateur INT,
    PRIMARY KEY (id_espace, id_utilisateur),
    FOREIGN KEY (id_espace) REFERENCES espace_pedagogique(id) ON DELETE CASCADE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
) ENGINE=InnoDB;