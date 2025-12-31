-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 31 déc. 2025 à 13:30
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
-- Structure de la table `espaces_pedagogiques`
--

DROP TABLE IF EXISTS `espaces_pedagogiques`;
CREATE TABLE IF NOT EXISTS `espaces_pedagogiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_espace` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matiere` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_formateur` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_formateur` (`id_formateur`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `espaces_pedagogiques`
--

INSERT INTO `espaces_pedagogiques` (`id`, `nom_espace`, `matiere`, `id_formateur`) VALUES
(1, 'Informatique de Gestion', NULL, 2);

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
CREATE TABLE IF NOT EXISTS `etudiants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_promotion` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_promotion` (`id_promotion`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id`, `nom`, `prenom`, `email`, `id_promotion`) VALUES
(1, 'HOUNHAIDE', 'Charles', 'hounhaidejeremie@gmail.com', 1),
(2, 'KILAYOSSI', 'Constantin', 'constantinkilayossi@gmail.com', 1);

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
(1, 1),
(2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `formateurs`
--

DROP TABLE IF EXISTS `formateurs`;

CREATE TABLE `formateurs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Déchargement des données de la table `formateurs`
--


-- --------------------------------------------------------

--
-- Structure de la table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annee` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `promotions`
--

INSERT INTO `promotions` (`id`, `nom`, `annee`) VALUES
(1, 'LICENCE 3 SIL', '2024-2025');

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
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('DIRECTEUR','FORMATEUR','ETUDIANT') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `email`, `mot_de_passe`, `role`) VALUES
(1, 'Gloria Abatti', 'gloriaabatti60@gmail.com', '$2y$10$oiIU9cwAN0Q9Bo//L3Hmt.wUOQvBmmEKx6v9Vyg7aly8BdLWSpyu6', 'DIRECTEUR');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
