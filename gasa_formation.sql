-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 05 jan. 2026 à 21:53
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gasa_formation`
--

-- --------------------------------------------------------

--
-- Structure de la table `assignations_travaux`
--

DROP TABLE IF EXISTS `assignations_travaux`;
CREATE TABLE IF NOT EXISTS `assignations_travaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_travail` int NOT NULL,
  `id_etudiant` int NOT NULL,
  `statut` enum('EN_ATTENTE','RENDU','EVALUE','EN_RETARD') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'EN_ATTENTE',
  `date_assignation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assignation` (`id_travail`,`id_etudiant`),
  KEY `fk_assignation_etudiant` (`id_etudiant`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `assignations_travaux`
--

INSERT INTO `assignations_travaux` (`id`, `id_travail`, `id_etudiant`, `statut`, `date_assignation`) VALUES
(4, 5, 8, 'EVALUE', '2026-01-05 16:23:46'),
(7, 8, 8, 'EN_ATTENTE', '2026-01-05 22:40:29');

-- --------------------------------------------------------

--
-- Structure de la table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 0, 'SYSTEM', '\"Migration des comptes existants effectu\\u00e9e\"', 'Array', '', '2026-01-01 23:34:01'),
(2, 0, 'SYSTEM', '\"Migration des comptes existants effectu\\u00e9e\"', 'Array', '', '2026-01-01 23:39:46'),
(3, 0, 'SYSTEM', '\"Migration des comptes existants effectu\\u00e9e\"', 'Array', '', '2026-01-01 23:45:06'),
(4, NULL, 'login_failed', '{\"email\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-02 07:29:01'),
(5, 1, 'login_success', '{\"email\":\"gloriaabatti60@gmail.com\",\"role\":\"DIRECTEUR\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-02 07:31:17');

-- --------------------------------------------------------

--
-- Structure de la table `espaces_pedagogiques`
--

DROP TABLE IF EXISTS `espaces_pedagogiques`;
CREATE TABLE IF NOT EXISTS `espaces_pedagogiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_espace` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matiere` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_formateur` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_formateur` (`id_formateur`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `espaces_pedagogiques`
--

INSERT INTO `espaces_pedagogiques` (`id`, `nom_espace`, `matiere`, `id_formateur`) VALUES
(3, 'sdfhj', NULL, 8);

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_promotion` int DEFAULT NULL,
  `id_utilisateur` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_promotion` (`id_promotion`),
  KEY `fk_user_student` (`id_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id`, `nom`, `prenom`, `email`, `id_promotion`, `id_utilisateur`) VALUES
(8, 'ALIKON', 'David', 'lbosadavidayi@gmail.com', 1, 12);

-- --------------------------------------------------------

--
-- Structure de la table `etudiant_espace`
--

DROP TABLE IF EXISTS `etudiant_espace`;
CREATE TABLE IF NOT EXISTS `etudiant_espace` (
  `id_etudiant` int NOT NULL,
  `id_espace` int NOT NULL,
  PRIMARY KEY (`id_etudiant`,`id_espace`),
  UNIQUE KEY `unique_inscription` (`id_etudiant`,`id_espace`),
  KEY `id_espace` (`id_espace`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiant_espace`
--

INSERT INTO `etudiant_espace` (`id_etudiant`, `id_espace`) VALUES
(8, 3);

-- --------------------------------------------------------

--
-- Structure de la table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rendu` int NOT NULL,
  `note` decimal(4,2) NOT NULL,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_evaluation` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_formateur` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evaluation` (`id_rendu`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `evaluations`
--

INSERT INTO `evaluations` (`id`, `id_rendu`, `note`, `commentaire`, `date_evaluation`, `id_formateur`) VALUES
(1, 1, 12.50, 'Excellent travail', '2026-01-05 18:43:30', 8);

--
-- Déclencheurs `evaluations`
--
DROP TRIGGER IF EXISTS `update_statut_evaluation`;
DELIMITER $$
CREATE TRIGGER `update_statut_evaluation` AFTER INSERT ON `evaluations` FOR EACH ROW BEGIN
    UPDATE assignations_travaux at
    JOIN rendus_travaux rt ON at.id = rt.id_assignation
    SET at.statut = 'EVALUE' WHERE rt.id = NEW.id_rendu;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `fichiers_rendus`
--

DROP TABLE IF EXISTS `fichiers_rendus`;
CREATE TABLE IF NOT EXISTS `fichiers_rendus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rendu_id` int DEFAULT NULL,
  `fichier_url` varchar(255) DEFAULT NULL,
  `nom_original` varchar(255) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `taille` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fichiers_travaux`
--

DROP TABLE IF EXISTS `fichiers_travaux`;
CREATE TABLE IF NOT EXISTS `fichiers_travaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_travail` int NOT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin_fichier` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille_fichier` int NOT NULL,
  `type_fichier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_upload` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_travail` (`id_travail`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fichiers_travaux`
--

INSERT INTO `fichiers_travaux` (`id`, `id_travail`, `nom_fichier`, `chemin_fichier`, `taille_fichier`, `type_fichier`, `date_upload`) VALUES
(1, 5, 'evaluation_java_complete.pdf', 'uploads/travaux/5/evaluation_java_complete_1767626626_a4f33f10.pdf', 5384, 'application/pdf', '2026-01-05 16:23:46'),
(4, 8, 'VBA EXCEL.pdf', 'uploads/travaux/8/VBA_EXCEL_1767649229_c19fe2c7.pdf', 1618045, 'application/pdf', '2026-01-05 22:40:29');

-- --------------------------------------------------------

--
-- Structure de la table `formateurs`
--

DROP TABLE IF EXISTS `formateurs`;
CREATE TABLE IF NOT EXISTS `formateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_utilisateur` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formateurs`
--

INSERT INTO `formateurs` (`id`, `nom`, `email`, `id_utilisateur`) VALUES
(8, 'AYIDOWIN David', 'alikondavidayidowin@gmail.com', 10);

-- --------------------------------------------------------

--
-- Structure de la table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annee` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `promotions`
--

INSERT INTO `promotions` (`id`, `nom`, `annee`) VALUES
(1, 'LICENCE 3 SIL', '2024-2025'),
(3, 'SCIENCE-ECO L2', '2024-2025');

-- --------------------------------------------------------

--
-- Structure de la table `promotion_espace`
--

DROP TABLE IF EXISTS `promotion_espace`;
CREATE TABLE IF NOT EXISTS `promotion_espace` (
  `id_promotion` int NOT NULL,
  `id_espace` int NOT NULL,
  PRIMARY KEY (`id_promotion`,`id_espace`),
  KEY `id_espace` (`id_espace`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` int DEFAULT '1',
  `last_attempt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_action` (`ip_address`,`action`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `ip_address`, `action`, `attempts`, `last_attempt`, `created_at`) VALUES
(1, '::1', 'login', 2, '2026-01-02 07:31:16', '2026-01-02 07:28:58');

-- --------------------------------------------------------

--
-- Structure de la table `rendus_travaux`
--

DROP TABLE IF EXISTS `rendus_travaux`;
CREATE TABLE IF NOT EXISTS `rendus_travaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_assignation` int NOT NULL,
  `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichier_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_rendu` datetime DEFAULT CURRENT_TIMESTAMP,
  `est_en_retard` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_rendu_assignation` (`id_assignation`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `rendus_travaux`
--

INSERT INTO `rendus_travaux` (`id`, `id_assignation`, `contenu`, `fichier_url`, `date_rendu`, `est_en_retard`) VALUES
(1, 4, 'Un projet est un ensemble d’actions organisées et planifiées visant à atteindre un objectif précis dans un délai donné, en mobilisant des ressources humaines, matérielles et financières, tout en respectant des contraintes définies afin de produire un résultat concret et mesurable.', NULL, '2026-01-05 18:17:25', 0);

--
-- Déclencheurs `rendus_travaux`
--
DROP TRIGGER IF EXISTS `check_retard_after_rendu`;
DELIMITER $$
CREATE TRIGGER `check_retard_after_rendu` BEFORE INSERT ON `rendus_travaux` FOR EACH ROW BEGIN
    DECLARE date_lim DATETIME;
    SELECT t.date_limite INTO date_lim FROM assignations_travaux at 
    JOIN travaux t ON at.id_travail = t.id WHERE at.id = NEW.id_assignation;
    
    IF NEW.date_rendu > date_lim THEN
        SET NEW.est_en_retard = TRUE;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `update_statut_rendu`;
DELIMITER $$
CREATE TRIGGER `update_statut_rendu` AFTER INSERT ON `rendus_travaux` FOR EACH ROW BEGIN
    UPDATE assignations_travaux SET statut = IF(NEW.est_en_retard, 'EN_RETARD', 'RENDU')
    WHERE id = NEW.id_assignation;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `travaux`
--

DROP TABLE IF EXISTS `travaux`;
CREATE TABLE IF NOT EXISTS `travaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_limite` datetime NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `type_travail` enum('INDIVIDUEL','COLLECTIF') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'COLLECTIF',
  `id_formateur` int DEFAULT NULL,
  `id_espace` int DEFAULT NULL,
  `id_promotion` int DEFAULT NULL,
  `id_etudiant` int DEFAULT NULL,
  `accept_videos` tinyint(1) NOT NULL DEFAULT '0',
  `video_obligatoire` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_travaux_formateur` (`id_formateur`),
  KEY `fk_travaux_espace` (`id_espace`),
  KEY `fk_travaux_promotion` (`id_promotion`),
  KEY `fk_travaux_etudiant` (`id_etudiant`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `travaux`
--

INSERT INTO `travaux` (`id`, `titre`, `description`, `date_limite`, `date_creation`, `type_travail`, `id_formateur`, `id_espace`, `id_promotion`, `id_etudiant`, `accept_videos`, `video_obligatoire`) VALUES
(5, 'Projet SETICE', 'Azeghyui dfghjk ghjk ghjk ghjkl', '2026-01-05 19:00:00', '2026-01-05 16:23:46', 'INDIVIDUEL', 8, 3, NULL, 8, 0, 0),
(8, 'dfghjk', 'sdfghjn,;:', '2026-01-07 22:00:00', '2026-01-05 22:40:29', 'INDIVIDUEL', 8, 3, NULL, 8, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('DIRECTEUR','FORMATEUR','ETUDIANT') NOT NULL,
  `activation_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `email`, `mot_de_passe`, `role`, `activation_token`, `is_active`) VALUES
(1, 'Gloria Abatti', 'gloriaabatti60@gmail.com', '$2y$10$UXLEh49nQpSKZVDejC.mOO69kQR5xtnES7pXBeP/7dosTg20/98Dm', 'DIRECTEUR', NULL, 1),
(10, 'AYIDOWIN David', 'alikondavidayidowin@gmail.com', '$2y$10$/Rf1ogX9b.mjifWsO7iHn.cwfS7qrX6E9lz7oTkoCg2NrlCYSTh46', 'FORMATEUR', NULL, 1),
(12, 'ALIKON David', 'lbosadavidayi@gmail.com', '$2y$10$1zFnF2jIH8Qb6wM5im4JRu.kJpM.5sV5OpOCwk8mzj/WddTcivPcq', 'ETUDIANT', NULL, 1);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_notes_etudiants`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `v_notes_etudiants`;
CREATE TABLE IF NOT EXISTS `v_notes_etudiants` (
`etudiant_id` int
,`nom` varchar(100)
,`prenom` varchar(100)
,`promotion_nom` varchar(100)
,`note` decimal(4,2)
,`travail_titre` varchar(255)
,`nom_espace` varchar(100)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_travaux_stats`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `v_travaux_stats`;
CREATE TABLE IF NOT EXISTS `v_travaux_stats` (
`id` int
,`titre` varchar(255)
,`description` text
,`date_limite` datetime
,`date_creation` datetime
,`type_travail` enum('INDIVIDUEL','COLLECTIF')
,`id_formateur` int
,`id_espace` int
,`id_promotion` int
,`id_etudiant` int
,`formateur_nom` varchar(100)
,`nom_espace` varchar(100)
,`promotion_nom` varchar(100)
,`nb_assignations` bigint
,`nb_rendus` bigint
,`nb_evalues` bigint
,`moyenne_notes` decimal(5,2)
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_notes_etudiants`
--
DROP TABLE IF EXISTS `v_notes_etudiants`;

DROP VIEW IF EXISTS `v_notes_etudiants`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_notes_etudiants`  AS SELECT `et`.`id` AS `etudiant_id`, `et`.`nom` AS `nom`, `et`.`prenom` AS `prenom`, `p`.`nom` AS `promotion_nom`, `ev`.`note` AS `note`, `t`.`titre` AS `travail_titre`, `esp`.`nom_espace` AS `nom_espace` FROM ((((((`etudiants` `et` join `promotions` `p` on((`et`.`id_promotion` = `p`.`id`))) join `assignations_travaux` `at` on((`et`.`id` = `at`.`id_etudiant`))) join `rendus_travaux` `rt` on((`at`.`id` = `rt`.`id_assignation`))) join `evaluations` `ev` on((`rt`.`id` = `ev`.`id_rendu`))) join `travaux` `t` on((`at`.`id_travail` = `t`.`id`))) join `espaces_pedagogiques` `esp` on((`t`.`id_espace` = `esp`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_travaux_stats`
--
DROP TABLE IF EXISTS `v_travaux_stats`;

DROP VIEW IF EXISTS `v_travaux_stats`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_travaux_stats`  AS SELECT `t`.`id` AS `id`, `t`.`titre` AS `titre`, `t`.`description` AS `description`, `t`.`date_limite` AS `date_limite`, `t`.`date_creation` AS `date_creation`, `t`.`type_travail` AS `type_travail`, `t`.`id_formateur` AS `id_formateur`, `t`.`id_espace` AS `id_espace`, `t`.`id_promotion` AS `id_promotion`, `t`.`id_etudiant` AS `id_etudiant`, `f`.`nom` AS `formateur_nom`, `e`.`nom_espace` AS `nom_espace`, `p`.`nom` AS `promotion_nom`, count(distinct `at`.`id`) AS `nb_assignations`, count(distinct `rt`.`id`) AS `nb_rendus`, count(distinct `ev`.`id`) AS `nb_evalues`, round(avg(`ev`.`note`),2) AS `moyenne_notes` FROM ((((((`travaux` `t` left join `formateurs` `f` on((`t`.`id_formateur` = `f`.`id`))) left join `espaces_pedagogiques` `e` on((`t`.`id_espace` = `e`.`id`))) left join `promotions` `p` on((`t`.`id_promotion` = `p`.`id`))) left join `assignations_travaux` `at` on((`t`.`id` = `at`.`id_travail`))) left join `rendus_travaux` `rt` on((`at`.`id` = `rt`.`id_assignation`))) left join `evaluations` `ev` on((`rt`.`id` = `ev`.`id_rendu`))) GROUP BY `t`.`id` ;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `assignations_travaux`
--
ALTER TABLE `assignations_travaux`
  ADD CONSTRAINT `fk_assignation_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignation_travail` FOREIGN KEY (`id_travail`) REFERENCES `travaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `fk_evaluation_rendu` FOREIGN KEY (`id_rendu`) REFERENCES `rendus_travaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fichiers_travaux`
--
ALTER TABLE `fichiers_travaux`
  ADD CONSTRAINT `fichiers_travaux_ibfk_1` FOREIGN KEY (`id_travail`) REFERENCES `travaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rendus_travaux`
--
ALTER TABLE `rendus_travaux`
  ADD CONSTRAINT `fk_rendu_assignation` FOREIGN KEY (`id_assignation`) REFERENCES `assignations_travaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `travaux`
--
ALTER TABLE `travaux`
  ADD CONSTRAINT `fk_travaux_espace` FOREIGN KEY (`id_espace`) REFERENCES `espaces_pedagogiques` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_travaux_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_travaux_formateur` FOREIGN KEY (`id_formateur`) REFERENCES `formateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_travaux_promotion` FOREIGN KEY (`id_promotion`) REFERENCES `promotions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
