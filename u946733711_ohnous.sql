-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 29 déc. 2025 à 19:25
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
-- Base de données : `u946733711_ohnous`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `nom` text COLLATE utf8mb4_general_ci,
  `id` int NOT NULL AUTO_INCREMENT,
  `unique_id` text COLLATE utf8mb4_general_ci,
  `slug` text COLLATE utf8mb4_general_ci,
  `prix` double DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `reserve` int DEFAULT '1',
  `boutique` int NOT NULL,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `relation_boutique` (`boutique`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`nom`, `id`, `unique_id`, `slug`, `prix`, `description`, `reserve`, `boutique`, `date_ajout`) VALUES
('Sac à main femme vintage', 22, 'article_694824a70c9d3', 'sac-a-main-femme-vintage', 35, 'Sac à  main pour femme couleur terre', 1, 1, '2025-12-21 17:47:35'),
('Sac Guess', 23, 'article_6948393446aff', 'sac-guess', 22, 'Sac Guess rouge Bordeaux', 1, 1, '2025-12-21 19:15:16');

-- --------------------------------------------------------

--
-- Structure de la table `boutiques`
--

DROP TABLE IF EXISTS `boutiques`;
CREATE TABLE IF NOT EXISTS `boutiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text COLLATE utf8mb4_general_ci,
  `adresse_email` int DEFAULT NULL,
  `mdp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `nom_utilisateur` text COLLATE utf8mb4_general_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `boutiques`
--

INSERT INTO `boutiques` (`id`, `nom`, `adresse_email`, `mdp`, `nom_utilisateur`, `description`, `date_ajout`) VALUES
(1, 'Bk sacs', NULL, NULL, NULL, 'Une boutique vintage de sac, une fouineuse amoureuse des sacs en friperie boutique 2025', '2025-12-18 20:28:28');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text COLLATE utf8mb4_general_ci,
  `slug` text COLLATE utf8mb4_general_ci,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`, `slug`, `date_ajout`) VALUES
(1, 'Homme', 'homme', '2025-12-14 06:41:24'),
(2, 'Femme', 'femme', '2025-12-14 06:41:24'),
(3, 'Enfant', 'enfant', '2025-12-14 06:41:24'),
(4, 'Chaussures', 'chaussures', '2025-12-14 06:41:24'),
(5, 'Maquillage', 'maquillage', '2025-12-14 06:41:24'),
(6, 'Soins visage', 'soins-visage', '2025-12-14 06:41:24'),
(7, 'Soins corps', 'soins-corps', '2025-12-14 06:41:24'),
(8, 'Cheveux', 'cheveux', '2025-12-14 06:41:24'),
(9, 'Parfums', 'parfums', '2025-12-14 06:41:24');

-- --------------------------------------------------------

--
-- Structure de la table `categorie_article`
--

DROP TABLE IF EXISTS `categorie_article`;
CREATE TABLE IF NOT EXISTS `categorie_article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article` int NOT NULL,
  `categorie` int NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article` (`article`),
  KEY `categorie_article_ibfk_2` (`categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie_article`
--

INSERT INTO `categorie_article` (`id`, `article`, `categorie`, `date_ajout`) VALUES
(7, 22, 2, '2025-12-21 17:47:35'),
(8, 23, 2, '2025-12-21 19:15:16');

-- --------------------------------------------------------

--
-- Structure de la table `categorie_types`
--

DROP TABLE IF EXISTS `categorie_types`;
CREATE TABLE IF NOT EXISTS `categorie_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categorie` int DEFAULT NULL,
  `types` int NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lien_categorie` (`categorie`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie_types`
--

INSERT INTO `categorie_types` (`id`, `categorie`, `types`, `date_ajout`) VALUES
(1, 1, 1, '2025-12-14 09:11:28'),
(2, 1, 2, '2025-12-14 09:11:28'),
(3, 1, 3, '2025-12-14 09:11:28'),
(4, 1, 4, '2025-12-14 09:11:28'),
(5, 1, 5, '2025-12-14 09:11:28'),
(6, 2, 6, '2025-12-14 09:11:28'),
(7, 2, 7, '2025-12-14 09:11:28'),
(8, 2, 8, '2025-12-14 09:11:28'),
(9, 2, 9, '2025-12-14 09:11:28'),
(10, 2, 10, '2025-12-14 09:11:28'),
(11, 2, 11, '2025-12-14 09:11:28'),
(12, 3, 12, '2025-12-14 09:11:28'),
(13, 3, 13, '2025-12-14 09:11:28'),
(14, 3, 14, '2025-12-14 09:11:28'),
(15, 3, 15, '2025-12-14 09:11:28'),
(16, 3, 16, '2025-12-14 09:11:28'),
(17, 4, 17, '2025-12-14 09:11:28'),
(18, 4, 18, '2025-12-14 09:11:28'),
(19, 4, 19, '2025-12-14 09:11:28'),
(20, 4, 20, '2025-12-14 09:11:28'),
(21, 5, 21, '2025-12-14 09:11:28'),
(22, 5, 22, '2025-12-14 09:11:28'),
(23, 5, 23, '2025-12-14 09:11:28'),
(24, 5, 24, '2025-12-14 09:11:28'),
(25, 6, 25, '2025-12-14 09:11:28'),
(26, 6, 26, '2025-12-14 09:11:28'),
(27, 6, 27, '2025-12-14 09:11:28'),
(28, 6, 28, '2025-12-14 09:11:28'),
(29, 7, 29, '2025-12-14 09:11:28'),
(30, 7, 30, '2025-12-14 09:11:28'),
(31, 7, 31, '2025-12-14 09:11:28'),
(32, 8, 32, '2025-12-14 09:11:28'),
(33, 8, 33, '2025-12-14 09:11:28'),
(34, 8, 34, '2025-12-14 09:11:28'),
(35, 8, 35, '2025-12-14 09:11:28'),
(36, 8, 36, '2025-12-14 09:11:28'),
(37, 8, 37, '2025-12-14 09:11:28'),
(38, 8, 38, '2025-12-14 09:11:28'),
(40, 2, 40, '2025-12-20 13:15:16');

-- --------------------------------------------------------

--
-- Structure de la table `image_articles`
--

DROP TABLE IF EXISTS `image_articles`;
CREATE TABLE IF NOT EXISTS `image_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article` int NOT NULL,
  `img` text COLLATE utf8mb4_general_ci,
  `alt_text` text COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `modif_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `img_article` (`article`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `image_articles`
--

INSERT INTO `image_articles` (`id`, `article`, `img`, `alt_text`, `date_ajout`, `modif_date`) VALUES
(70, 22, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1766335652223_Dw_CtxCPa.webp', 'sac-a-main-femme-vintage', '2025-12-21 17:47:35', '2025-12-21 17:47:35'),
(71, 23, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1766340913536_vzMIi4OCt.webp', 'sac-guess', '2025-12-21 19:15:16', '2025-12-21 19:15:16');

-- --------------------------------------------------------

--
-- Structure de la table `tailles`
--

DROP TABLE IF EXISTS `tailles`;
CREATE TABLE IF NOT EXISTS `tailles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text COLLATE utf8mb4_general_ci,
  `commentaire` text COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_nom` (`nom`(100))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tailles`
--

INSERT INTO `tailles` (`id`, `nom`, `commentaire`, `date_ajout`) VALUES
(1, 'S', NULL, '2025-10-19 15:18:40'),
(2, 'L', NULL, '2025-10-19 15:18:40'),
(3, 'M', NULL, '2025-10-19 15:18:40'),
(4, 'XL', NULL, '2025-10-19 15:18:40'),
(5, 'XXL', NULL, '2025-10-19 15:18:40'),
(6, 'Micro / Mini', '< 5 L', '2025-12-20 13:03:24'),
(7, 'Petit', '5 - 15 L', '2025-12-20 13:03:24'),
(8, 'Moyen', '15 - 30 L', '2025-12-20 13:03:24'),
(9, 'Grand', '30 - 50 L', '2025-12-20 13:03:24'),
(10, 'Extra Grand', '50 L et +', '2025-12-20 13:03:24');

-- --------------------------------------------------------

--
-- Structure de la table `tailles_types`
--

DROP TABLE IF EXISTS `tailles_types`;
CREATE TABLE IF NOT EXISTS `tailles_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tailles` int DEFAULT NULL,
  `types` int DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lien_taille` (`tailles`),
  KEY `lien_types` (`types`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tailles_types`
--

INSERT INTO `tailles_types` (`id`, `tailles`, `types`, `date_ajout`) VALUES
(1, 6, 40, '2025-12-20 14:06:23'),
(2, 7, 40, '2025-12-20 14:06:23'),
(3, 8, 40, '2025-12-20 14:06:23'),
(4, 9, 40, '2025-12-20 14:06:23'),
(5, 10, 40, '2025-12-20 14:06:23');

-- --------------------------------------------------------

--
-- Structure de la table `taille_articles`
--

DROP TABLE IF EXISTS `taille_articles`;
CREATE TABLE IF NOT EXISTS `taille_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `taille` int NOT NULL,
  `article` int NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `taille_taille` (`taille`),
  KEY `taille_article` (`article`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `taille_articles`
--

INSERT INTO `taille_articles` (`id`, `taille`, `article`, `date_ajout`) VALUES
(5, 7, 22, '2025-12-21 17:47:35'),
(6, 7, 23, '2025-12-21 19:15:16');

-- --------------------------------------------------------

--
-- Structure de la table `types`
--

DROP TABLE IF EXISTS `types`;
CREATE TABLE IF NOT EXISTS `types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `types`
--

INSERT INTO `types` (`id`, `nom`, `date_ajout`) VALUES
(1, 'T-shirts & polos', '2025-12-14 07:31:02'),
(2, 'Chemises', '2025-12-14 07:31:02'),
(3, 'Pantalons & jeans', '2025-12-14 07:31:02'),
(4, 'Vestes & manteaux', '2025-12-14 07:31:02'),
(5, 'Accessoires', '2025-12-14 07:31:02'),
(6, 'Robes', '2025-12-14 07:31:02'),
(7, 'Tops & blouses', '2025-12-14 07:31:02'),
(8, 'Jupes', '2025-12-14 07:31:02'),
(9, 'Pantalons & jeans', '2025-12-14 07:31:02'),
(10, 'Vestes & manteaux', '2025-12-14 07:31:02'),
(11, 'Accessoires', '2025-12-14 07:31:02'),
(12, 'Bébé (0–2 ans)', '2025-12-14 07:31:02'),
(13, 'Garçon', '2025-12-14 07:31:02'),
(14, 'Fille', '2025-12-14 07:31:02'),
(15, 'Chaussures enfants', '2025-12-14 07:31:02'),
(16, 'Accessoires scolaires', '2025-12-14 07:31:02'),
(17, 'Homme', '2025-12-14 07:31:02'),
(18, 'Femme', '2025-12-14 07:31:02'),
(19, 'Enfant', '2025-12-14 07:31:02'),
(20, 'Sport', '2025-12-14 07:31:02'),
(21, 'Teint (fond de teint, poudre, correcteur)', '2025-12-14 07:31:02'),
(22, 'Yeux (mascara, eyeliner, fards)', '2025-12-14 07:31:02'),
(23, 'Lèvres (rouges à lèvres, gloss)', '2025-12-14 07:31:02'),
(24, 'Ongles (vernis, soins)', '2025-12-14 07:31:02'),
(25, 'Nettoyants & démaquillants', '2025-12-14 07:31:02'),
(26, 'Crèmes hydratantes', '2025-12-14 07:31:02'),
(27, 'Sérums & masques', '2025-12-14 07:31:02'),
(28, 'Anti-âge', '2025-12-14 07:31:02'),
(29, 'Crèmes & laits hydratants', '2025-12-14 07:31:02'),
(30, 'Gommages', '2025-12-14 07:31:02'),
(31, 'Huiles & beurres', '2025-12-14 07:31:02'),
(32, 'Shampoings', '2025-12-14 07:31:02'),
(33, 'Après-shampoings', '2025-12-14 07:31:02'),
(34, 'Masques & soins', '2025-12-14 07:31:02'),
(35, 'Produits coiffants', '2025-12-14 07:31:02'),
(36, 'Homme', '2025-12-14 07:31:02'),
(37, 'Femme', '2025-12-14 07:31:02'),
(38, 'Unisexes', '2025-12-14 07:31:02'),
(40, 'Sac', '2025-12-20 13:14:38');

-- --------------------------------------------------------

--
-- Structure de la table `types_article`
--

DROP TABLE IF EXISTS `types_article`;
CREATE TABLE IF NOT EXISTS `types_article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article` int NOT NULL,
  `types` int NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article` (`article`),
  KEY `types_article_ibfk_2` (`types`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `types_article`
--

INSERT INTO `types_article` (`id`, `article`, `types`, `date_ajout`) VALUES
(7, 22, 40, '2025-12-21 17:47:35'),
(8, 23, 40, '2025-12-21 19:15:16');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `relation_boutique` FOREIGN KEY (`boutique`) REFERENCES `boutiques` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `categorie_article`
--
ALTER TABLE `categorie_article`
  ADD CONSTRAINT `categorie_article_ibfk_1` FOREIGN KEY (`article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `categorie_article_ibfk_2` FOREIGN KEY (`categorie`) REFERENCES `categorie` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `categorie_types`
--
ALTER TABLE `categorie_types`
  ADD CONSTRAINT `lien_categorie` FOREIGN KEY (`categorie`) REFERENCES `categorie` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `image_articles`
--
ALTER TABLE `image_articles`
  ADD CONSTRAINT `img_article` FOREIGN KEY (`article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `tailles_types`
--
ALTER TABLE `tailles_types`
  ADD CONSTRAINT `lien_taille` FOREIGN KEY (`tailles`) REFERENCES `tailles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `lien_types` FOREIGN KEY (`types`) REFERENCES `types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Contraintes pour la table `taille_articles`
--
ALTER TABLE `taille_articles`
  ADD CONSTRAINT `taille_article` FOREIGN KEY (`article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `taille_taille` FOREIGN KEY (`taille`) REFERENCES `tailles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `types_article`
--
ALTER TABLE `types_article`
  ADD CONSTRAINT `types_article_ibfk_1` FOREIGN KEY (`article`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `types_article_ibfk_2` FOREIGN KEY (`types`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
