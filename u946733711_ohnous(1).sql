-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 06 jan. 2026 à 04:51
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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`nom`, `id`, `unique_id`, `slug`, `prix`, `description`, `reserve`, `boutique`, `date_ajout`) VALUES
('Sac à main femme vintage', 22, 'article_694824a70c9d3', 'sac-a-main-femme-vintage', 35, 'Sac à  main pour femme couleur terre', 1, 1, '2025-12-21 17:47:35'),
('Sac Guess', 23, 'article_6948393446aff', 'sac-guess', 22, 'Sac Guess rouge Bordeaux', 1, 1, '2025-12-21 19:15:16'),
('Prada sac à main', 35, 'article_695a6c342dbd4', 'prada-sac-a-main', 27, 'Magnifique sac prada', 1, 1, '2026-01-04 14:33:40'),
('Sac four tout', 36, 'article_695a6cb3834d4', 'sac-four-tout', 20, 'Sac neous four tout', 1, 1, '2026-01-04 14:35:47'),
('Sac étudiante', 37, 'article_695a6d32e78f5', 'sac-etudiante', 15, 'Magnifique sac pour étudiante', 1, 1, '2026-01-04 14:37:54'),
('Mini sac de soirée', 38, 'article_695a6d9b77335', 'mini-sac-de-soir-ee', 23, 'Magnifique sac de soirée', 1, 1, '2026-01-04 14:39:39'),
('Sac rebelle', 39, 'article_695a6df43d214', 'sac-rebelle', 22, 'Sac rebelle idéal pour les sortis en boîte', 1, 1, '2026-01-04 14:41:08'),
('Mini sac crocos', 40, 'article_695a6e4c18520', 'mini-sac-crocos', 30, 'Mini sac crocos', 1, 1, '2026-01-04 14:42:36'),
('Sac coraille', 41, 'article_695a6ee92bef8', 'sac-coraille', 26, 'Magnifique sac des meres', 1, 1, '2026-01-04 14:45:13'),
('Sac corde rouge', 42, 'article_695a6f2fe022a', 'sac-corde-rouge', 36, 'Magnifique sac cordes', 1, 1, '2026-01-04 14:46:23'),
('Sac jaguar', 43, 'article_695a6facaee73', 'sac-jaguar', 50, 'Sac jaguar pour vos soirées de luxe', 1, 1, '2026-01-04 14:48:28'),
('Lunettes Tom Ford', 56, 'article_695aa93a62fbf', 'lunettes-tom-ford', 12, 'ok', 1, 2, '2026-01-04 18:54:02'),
('Lunette de soleil  LOEWE', 57, 'article_695ac805e85f2', 'lunette-de-soleil-loewe', 11, 'Lunettes de soleil LOEWE', 1, 2, '2026-01-04 21:05:25'),
('Lunette ALL BLACK LOEWE', 58, 'article_695ac85904657', 'lunette-all-black-loewe', 15, 'Lunette ALL BLACK LOEWE', 1, 2, '2026-01-04 21:06:49'),
('Lunettes rose view', 59, 'article_695ac90a72d38', 'lunettes-rose-view', 16, 'Belle lunettes noirs LOEWE rose view', 1, 2, '2026-01-04 21:09:46'),
('Lunette de plage blanche', 60, 'article_695ac967a29d7', 'lunette-de-plage-blanche', 20, '', 1, 2, '2026-01-04 21:11:19'),
('Duo Montarde et Dalmacien', 61, 'article_695ac9b147231', 'duo-montarde-et-dalmacien', 40, 'Ces lunettes ne se vendent qu\'à deux', 1, 2, '2026-01-04 21:12:33'),
('Lunette metal dorée', 62, 'article_695ac9fad5142', 'lunette-metal-dor-ee', 25, 'La vue de l\'océan', 1, 2, '2026-01-04 21:13:46'),
('Lunette hibou', 63, 'article_695aca3665563', 'lunette-hibou', 10, 'Magnifique', 1, 2, '2026-01-04 21:14:46'),
('Talons noir à pointe rouge', 64, 'article_695bce688f9b6', 'talons-noir-a-pointe-rouge', 35, 'Magnifique chaussure à la pointe de sang pour vos soirées', 1, 3, '2026-01-05 15:44:56'),
('Talon brun à pointe argenté', 65, 'article_695c8b58300f3', 'talon-brun-a-pointe-argent-e', 20, 'Magnifique Talon brun à pointe argenté', 1, 3, '2026-01-06 05:11:04'),
('Talon bleu Alala', 66, 'article_695c8b9ae994f', 'talon-bleu-alala', 28, 'Magnifique Talon bleu Alala', 1, 3, '2026-01-06 05:12:10'),
('Talon pleine rouge bordeau', 67, 'article_695c8bec73d3d', 'talon-pleine-rouge-bordeau', 29, 'Magnifique Talon pleine rouge bordeau', 1, 3, '2026-01-06 05:13:32'),
('Talon jumelle de Jeffrey Campbell', 68, 'article_695c8c322c8fd', 'talon-jumelle-de-jeffrey-campbell', 26, 'Magnifique Talon jumelle de Jeffrey Campbell', 1, 3, '2026-01-06 05:14:42'),
('Talon vert croco', 69, 'article_695c8cab50045', 'talon-vert-croco', 25, 'Magnifique Talon vert croco', 1, 3, '2026-01-06 05:16:43'),
('Talon de Gala dorée', 70, 'article_695c8cfbf0f32', 'talon-de-gala-dor-ee', 27, 'Magnifique Talon de Gala dorée', 1, 3, '2026-01-06 05:18:03'),
('Talon à semelle compensée', 71, 'article_695c8e770f569', 'talon-a-semelle-compens-ee', 24, 'Magnifique Talon à semelle compensée', 1, 3, '2026-01-06 05:24:23'),
('Talon fleurie jaune doré', 72, 'article_695c8ec90736b', 'talon-fleurie-jaune-dor-e', 20, 'Magnifuque Talon fleurie jaune doré', 1, 3, '2026-01-06 05:25:45'),
('Talon Dara noir doré', 73, 'article_695c8effd4c4e', 'talon-dara-noir-dor-e', 23, 'Magnifique Talon Dara noir doré', 1, 3, '2026-01-06 05:26:39'),
('Talon vert sombre Saint Laurent', 74, 'article_695c8f3bf1964', 'talon-vert-sombre-saint-laurent', 39, 'Magnifique Talon vert sombre Saint Laurent', 1, 3, '2026-01-06 05:27:39'),
('Talon boisé', 75, 'article_695c8f874afda', 'talon-bois-e', 50, 'Magnifique Talon boisé', 1, 3, '2026-01-06 05:28:55'),
('Talon coeur d\'afrique', 76, 'article_695c8fb4c8b05', 'talon-coeur-d-afrique', 26, 'Magnifique Talon coeur d\'afrique', 1, 3, '2026-01-06 05:29:40'),
('Talon grande dame rouge bordeau', 77, 'article_695c9017275d2', 'talon-grande-dame-rouge-bordeau', 36, 'Magnifique Talon grande dame rouge bordeau', 1, 3, '2026-01-06 05:31:19');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `boutiques`
--

INSERT INTO `boutiques` (`id`, `nom`, `adresse_email`, `mdp`, `nom_utilisateur`, `description`, `date_ajout`) VALUES
(1, 'Bk sacs', NULL, NULL, NULL, 'Une boutique vintage de sac, une fouineuse amoureuse des sacs en friperie boutique 2025', '2025-12-18 20:28:28'),
(2, 'Luxe lunetti', NULL, NULL, NULL, 'Luxe lunetti est une boutique en ligne de précommande de lunettes de luxe depuis 2023, au menu des marques comme loewe, ferragano, Tom Ford, etc...', '2026-01-04 15:01:25'),
(3, 'Chaussure Store', NULL, NULL, NULL, 'Est une boutique pour chaussure vintage Paris - Kinshasa depuis 2020, si vous aimez des pièces iconiques vous êtes au bons endroits.', '2026-01-05 13:54:27');

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie_article`
--

INSERT INTO `categorie_article` (`id`, `article`, `categorie`, `date_ajout`) VALUES
(7, 22, 2, '2025-12-21 17:47:35'),
(8, 23, 2, '2025-12-21 19:15:16'),
(19, 35, 2, '2026-01-04 14:33:40'),
(20, 36, 2, '2026-01-04 14:35:47'),
(21, 37, 2, '2026-01-04 14:37:54'),
(22, 38, 2, '2026-01-04 14:39:39'),
(23, 39, 2, '2026-01-04 14:41:08'),
(24, 40, 2, '2026-01-04 14:42:36'),
(25, 41, 2, '2026-01-04 14:45:13'),
(26, 42, 2, '2026-01-04 14:46:23'),
(27, 43, 2, '2026-01-04 14:48:28'),
(32, 56, 2, '2026-01-04 18:54:02'),
(33, 57, 2, '2026-01-04 21:05:25'),
(34, 58, 2, '2026-01-04 21:06:49'),
(35, 59, 2, '2026-01-04 21:09:46'),
(36, 60, 2, '2026-01-04 21:11:19'),
(37, 61, 2, '2026-01-04 21:12:33'),
(38, 62, 2, '2026-01-04 21:13:46'),
(39, 63, 2, '2026-01-04 21:14:46'),
(40, 64, 2, '2026-01-05 15:44:56'),
(41, 65, 2, '2026-01-06 05:11:04'),
(42, 66, 2, '2026-01-06 05:12:10'),
(43, 67, 2, '2026-01-06 05:13:32'),
(44, 68, 2, '2026-01-06 05:14:42'),
(45, 69, 2, '2026-01-06 05:16:43'),
(46, 70, 2, '2026-01-06 05:18:04'),
(47, 71, 2, '2026-01-06 05:24:23'),
(48, 72, 2, '2026-01-06 05:25:45'),
(49, 73, 2, '2026-01-06 05:26:39'),
(50, 74, 2, '2026-01-06 05:27:40'),
(51, 75, 2, '2026-01-06 05:28:55'),
(52, 76, 2, '2026-01-06 05:29:40'),
(53, 77, 2, '2026-01-06 05:31:19');

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
  KEY `lien_categorie` (`categorie`),
  KEY `categorie_types` (`types`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(40, 2, 40, '2025-12-20 13:15:16'),
(41, 2, 41, '2026-01-04 15:14:36'),
(42, 2, 42, '2026-01-05 13:56:22'),
(43, 2, 43, '2026-01-05 14:27:53');

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
  `background` text COLLATE utf8mb4_general_ci,
  `styles` text COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `modif_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `img_article` (`article`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `image_articles`
--

INSERT INTO `image_articles` (`id`, `article`, `img`, `alt_text`, `background`, `styles`, `date_ajout`, `modif_date`) VALUES
(70, 22, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1766335652223_Dw_CtxCPa.webp', 'sac-a-main-femme-vintage', 'rgb(128, 116, 100)', 'width: auto; height: 100%;', '2025-12-21 17:47:35', '2025-12-21 17:47:35'),
(71, 23, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1766340913536_vzMIi4OCt.webp', 'sac-guess', 'rgb(173, 165, 163)', 'width: 100%; height: auto;', '2025-12-21 19:15:16', '2025-12-21 19:15:16'),
(82, 35, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533615157_bPPTwzrej.webp', 'prada-sac-a-main', 'rgb(190, 199, 190)', 'width: auto; height: 100%;', '2026-01-04 14:33:40', '2026-01-04 14:33:40'),
(83, 36, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533745439_ZoYXVqrYg.webp', 'sac-four-tout', 'rgb(136, 114, 103)', 'width: auto; height: 100%;', '2026-01-04 14:35:47', '2026-01-04 14:35:47'),
(84, 37, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533871747_W65o-tNbg.webp', 'sac-etudiante', 'rgb(151, 110, 102)', 'width: 100%; height: auto;', '2026-01-04 14:37:54', '2026-01-04 14:37:54'),
(85, 38, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533976830_Bxs4KVnqH.webp', 'mini-sac-de-soir-ee', 'rgb(139, 113, 114)', 'width: 100%; height: auto;', '2026-01-04 14:39:39', '2026-01-04 14:39:39'),
(86, 39, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534065661_vw-z8gzRE.webp', 'sac-rebelle', 'rgb(38, 22, 20)', 'width: auto; height: 100%;', '2026-01-04 14:41:08', '2026-01-04 14:41:08'),
(87, 40, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534153579_fyxatQAoj.webp', 'mini-sac-crocos', 'rgb(100, 99, 87)', 'width: auto; height: 100%;', '2026-01-04 14:42:36', '2026-01-04 14:42:36'),
(88, 41, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534311007_7c7yXiHQw.webp', 'sac-coraille', 'rgb(156, 139, 127)', 'width: 100%; height: auto;', '2026-01-04 14:45:13', '2026-01-04 14:45:13'),
(89, 42, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534381852_HvMY_8zgf.webp', 'sac-corde-rouge', 'rgb(107, 91, 86)', 'width: auto; height: 100%;', '2026-01-04 14:46:23', '2026-01-04 14:46:23'),
(90, 43, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534505726_y04zJSgG9.webp', 'sac-jaguar', 'rgb(130, 101, 94)', 'width: auto; height: 100%;', '2026-01-04 14:48:28', '2026-01-04 14:48:28'),
(95, 56, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767549238994_a4NVcL4yZ.webp', 'lunettes-tom-ford', 'rgb(157, 147, 134)', 'width: auto; height: 100%;', '2026-01-04 18:54:02', '2026-01-04 18:54:02'),
(96, 57, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557120632_jwyeDtZJBO.webp', 'lunette-de-soleil-loewe', 'rgb(184, 185, 184)', 'width: 100%; height: auto;', '2026-01-04 21:05:25', '2026-01-04 21:05:25'),
(97, 58, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557206646_dVjrbrk1x.webp', 'lunette-all-black-loewe', 'rgb(176, 174, 173)', 'width: auto; height: 100%;', '2026-01-04 21:06:49', '2026-01-04 21:06:49'),
(98, 59, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557384436__6uYzatorl.webp', 'lunettes-rose-view', 'rgb(220, 218, 215)', 'width: auto; height: 100%;', '2026-01-04 21:09:46', '2026-01-04 21:09:46'),
(99, 60, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557476850_S2K2ilIz8.webp', 'lunette-de-plage-blanche', 'rgb(169, 161, 147)', 'width: auto; height: 100%;', '2026-01-04 21:11:19', '2026-01-04 21:11:19'),
(100, 61, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557550670_eK4-RZRcZ.webp', 'duo-montarde-et-dalmacien', 'rgb(154, 149, 133)', 'width: auto; height: 100%;', '2026-01-04 21:12:33', '2026-01-04 21:12:33'),
(101, 62, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557624415_nWDQQW1e8.webp', 'lunette-metal-dor-ee', 'rgb(156, 152, 144)', 'width: auto; height: 100%;', '2026-01-04 21:13:46', '2026-01-04 21:13:46'),
(102, 63, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557683935_Eosj1tvSl.webp', 'lunette-hibou', 'rgb(167, 147, 130)', 'width: auto; height: 100%;', '2026-01-04 21:14:46', '2026-01-04 21:14:46'),
(103, 64, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767624293757_HQL6fbb144.webp', 'talons-noir-a-pointe-rouge', 'rgb(139, 134, 131)', 'width: auto; height: 100%;', '2026-01-05 15:44:56', '2026-01-05 15:44:56'),
(104, 65, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672661453_Ogc2qm6Wm.webp', 'talon-brun-a-pointe-argent-e', 'rgb(26, 16, 17)', 'width: auto; height: 100%;', '2026-01-06 05:11:04', '2026-01-06 05:11:04'),
(105, 66, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672729163_3eC29U4_D.webp', 'talon-bleu-alala', 'rgb(220, 222, 227)', 'width: auto; height: 100%;', '2026-01-06 05:12:10', '2026-01-06 05:12:10'),
(106, 67, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672810266_oJV2jBUhJ.webp', 'talon-pleine-rouge-bordeau', 'rgb(145, 106, 81)', 'width: auto; height: 100%;', '2026-01-06 05:13:32', '2026-01-06 05:13:32'),
(107, 68, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672879606_NmCSIsavQ.webp', 'talon-jumelle-de-jeffrey-campbell', 'rgb(138, 119, 91)', 'width: auto; height: 100%;', '2026-01-06 05:14:42', '2026-01-06 05:14:42'),
(108, 69, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673001239_wDIcI8VkO.webp', 'talon-vert-croco', 'rgb(47, 50, 40)', 'width: auto; height: 100%;', '2026-01-06 05:16:43', '2026-01-06 05:16:43'),
(109, 70, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673081834_QdLFF6qf1.webp', 'talon-de-gala-dor-ee', 'rgb(170, 168, 163)', 'width: auto; height: 100%;', '2026-01-06 05:18:03', '2026-01-06 05:18:03'),
(110, 71, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673460963_VRNURcGFj.webp', 'talon-a-semelle-compens-ee', 'rgb(151, 138, 133)', 'width: 100%; height: auto;', '2026-01-06 05:24:23', '2026-01-06 05:24:23'),
(111, 72, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673542624_JWV7dXLBl.webp', 'talon-fleurie-jaune-dor-e', 'rgb(143, 137, 128)', 'width: auto; height: 100%;', '2026-01-06 05:25:45', '2026-01-06 05:25:45'),
(112, 73, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673597692_3r-KrKOBE.webp', 'talon-dara-noir-dor-e', 'rgb(153, 149, 149)', 'width: auto; height: 100%;', '2026-01-06 05:26:39', '2026-01-06 05:26:39'),
(113, 74, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673657830_qze1MaN4f.webp', 'talon-vert-sombre-saint-laurent', 'rgb(159, 160, 142)', 'width: auto; height: 100%;', '2026-01-06 05:27:39', '2026-01-06 05:27:39'),
(114, 75, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673733166_RicCxGONH.webp', 'talon-bois-e', 'rgb(116, 105, 97)', 'width: auto; height: 100%;', '2026-01-06 05:28:55', '2026-01-06 05:28:55'),
(115, 76, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673778734_wpcMp6ESnG.webp', 'talon-coeur-d-afrique', 'rgb(113, 98, 65)', 'width: auto; height: 100%;', '2026-01-06 05:29:40', '2026-01-06 05:29:40'),
(116, 77, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673877105_ONGk6X8QE.webp', 'talon-grande-dame-rouge-bordeau', 'rgb(129, 113, 109)', 'width: 100%; height: auto;', '2026-01-06 05:31:19', '2026-01-06 05:31:19');

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(7, 'Petit(e)', '5 - 15 L', '2025-12-20 13:03:24'),
(8, 'Moyen(ne)', '15 - 30 L', '2025-12-20 13:03:24'),
(9, 'Grand(e)', '30 - 50 L', '2025-12-20 13:03:24'),
(10, 'Extra Grand(e)', '50 L et +', '2025-12-20 13:03:24'),
(209, '36', 'Pointure EU', '2026-01-05 14:24:50'),
(210, '37', 'Pointure EU', '2026-01-05 14:24:50'),
(211, '38', 'Pointure EU', '2026-01-05 14:24:50'),
(212, '39', 'Pointure EU', '2026-01-05 14:24:50'),
(213, '40', 'Pointure EU', '2026-01-05 14:24:50'),
(214, '41', 'Pointure EU', '2026-01-05 14:24:50'),
(215, '42', 'Pointure EU', '2026-01-05 14:24:50'),
(216, '43', 'Pointure EU', '2026-01-05 14:24:50'),
(217, '44', 'Pointure EU', '2026-01-05 14:24:50'),
(218, '45', 'Pointure EU', '2026-01-05 14:24:50'),
(219, '46', 'Pointure EU', '2026-01-05 14:24:50'),
(220, '5.5', 'US Femme', '2026-01-05 14:24:50'),
(221, '6', 'US Femme', '2026-01-05 14:24:50'),
(222, '6.5', 'US Femme', '2026-01-05 14:24:50'),
(223, '7.5', 'US Femme', '2026-01-05 14:24:50'),
(224, '8.5', 'US Femme', '2026-01-05 14:24:50'),
(225, '9.5', 'US Femme', '2026-01-05 14:24:50'),
(226, '10', 'US Femme', '2026-01-05 14:24:50'),
(227, '11', 'US Femme', '2026-01-05 14:24:50'),
(228, '12', 'US Femme', '2026-01-05 14:24:50'),
(229, '13', 'US Femme', '2026-01-05 14:24:50'),
(230, '14', 'US Femme', '2026-01-05 14:24:50'),
(231, '3.5', 'UK', '2026-01-05 14:24:50'),
(232, '4', 'UK', '2026-01-05 14:24:50'),
(233, '5', 'UK', '2026-01-05 14:24:50'),
(234, '6', 'UK', '2026-01-05 14:24:50'),
(235, '6.5', 'UK', '2026-01-05 14:24:50'),
(236, '7.5', 'UK', '2026-01-05 14:24:50'),
(237, '8', 'UK', '2026-01-05 14:24:50'),
(238, '9', 'UK', '2026-01-05 14:24:50'),
(239, '10', 'UK', '2026-01-05 14:24:50'),
(240, '11', 'UK', '2026-01-05 14:24:50'),
(241, '12', 'UK', '2026-01-05 14:24:50');

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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tailles_types`
--

INSERT INTO `tailles_types` (`id`, `tailles`, `types`, `date_ajout`) VALUES
(1, 6, 40, '2025-12-20 14:06:23'),
(2, 7, 40, '2025-12-20 14:06:23'),
(3, 8, 40, '2025-12-20 14:06:23'),
(4, 9, 40, '2025-12-20 14:06:23'),
(5, 10, 40, '2025-12-20 14:06:23'),
(6, 231, 43, '2026-01-05 14:36:29'),
(7, 232, 43, '2026-01-05 14:36:29'),
(8, 233, 43, '2026-01-05 14:36:29'),
(9, 220, 43, '2026-01-05 14:36:29'),
(10, 234, 43, '2026-01-05 14:36:29'),
(11, 221, 43, '2026-01-05 14:36:29'),
(12, 222, 43, '2026-01-05 14:36:29'),
(13, 235, 43, '2026-01-05 14:36:29'),
(14, 223, 43, '2026-01-05 14:36:29'),
(15, 236, 43, '2026-01-05 14:36:29'),
(16, 237, 43, '2026-01-05 14:36:29'),
(17, 224, 43, '2026-01-05 14:36:29'),
(18, 238, 43, '2026-01-05 14:36:29'),
(19, 225, 43, '2026-01-05 14:36:29'),
(20, 239, 43, '2026-01-05 14:36:29'),
(21, 226, 43, '2026-01-05 14:36:29'),
(22, 227, 43, '2026-01-05 14:36:29'),
(23, 240, 43, '2026-01-05 14:36:29'),
(24, 241, 43, '2026-01-05 14:36:29'),
(25, 228, 43, '2026-01-05 14:36:29'),
(26, 229, 43, '2026-01-05 14:36:29'),
(27, 230, 43, '2026-01-05 14:36:29'),
(28, 209, 43, '2026-01-05 14:36:29'),
(29, 210, 43, '2026-01-05 14:36:29'),
(30, 211, 43, '2026-01-05 14:36:29'),
(31, 212, 43, '2026-01-05 14:36:29'),
(32, 213, 43, '2026-01-05 14:36:29'),
(33, 214, 43, '2026-01-05 14:36:29'),
(34, 215, 43, '2026-01-05 14:36:29'),
(35, 216, 43, '2026-01-05 14:36:29'),
(36, 217, 43, '2026-01-05 14:36:29'),
(37, 218, 43, '2026-01-05 14:36:29'),
(38, 219, 43, '2026-01-05 14:36:29');

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `taille_articles`
--

INSERT INTO `taille_articles` (`id`, `taille`, `article`, `date_ajout`) VALUES
(5, 7, 22, '2025-12-21 17:47:35'),
(6, 7, 23, '2025-12-21 19:15:16'),
(17, 7, 35, '2026-01-04 14:33:40'),
(18, 8, 36, '2026-01-04 14:35:47'),
(19, 9, 37, '2026-01-04 14:37:54'),
(20, 6, 38, '2026-01-04 14:39:39'),
(21, 7, 39, '2026-01-04 14:41:08'),
(22, 6, 40, '2026-01-04 14:42:36'),
(23, 6, 41, '2026-01-04 14:45:13'),
(24, 7, 42, '2026-01-04 14:46:23'),
(25, 7, 43, '2026-01-04 14:48:28'),
(35, 213, 64, '2026-01-05 15:44:56'),
(36, 211, 65, '2026-01-06 05:11:04'),
(37, 212, 66, '2026-01-06 05:12:10'),
(38, 214, 67, '2026-01-06 05:13:32'),
(39, 215, 68, '2026-01-06 05:14:42'),
(40, 213, 69, '2026-01-06 05:16:43'),
(41, 213, 70, '2026-01-06 05:18:03'),
(42, 213, 71, '2026-01-06 05:24:23'),
(43, 212, 72, '2026-01-06 05:25:45'),
(44, 213, 73, '2026-01-06 05:26:39'),
(45, 214, 74, '2026-01-06 05:27:39'),
(46, 213, 75, '2026-01-06 05:28:55'),
(47, 213, 76, '2026-01-06 05:29:40'),
(48, 215, 77, '2026-01-06 05:31:19');

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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(40, 'Sac', '2025-12-20 13:14:38'),
(41, 'Lunettes', '2026-01-04 15:03:25'),
(42, 'Chaussure', '2026-01-05 13:55:47'),
(43, 'Talon', '2026-01-05 14:26:40');

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
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `types_article`
--

INSERT INTO `types_article` (`id`, `article`, `types`, `date_ajout`) VALUES
(7, 22, 40, '2025-12-21 17:47:35'),
(8, 23, 40, '2025-12-21 19:15:16'),
(19, 35, 40, '2026-01-04 14:33:40'),
(20, 36, 40, '2026-01-04 14:35:47'),
(21, 37, 40, '2026-01-04 14:37:54'),
(22, 38, 40, '2026-01-04 14:39:39'),
(23, 39, 40, '2026-01-04 14:41:08'),
(24, 40, 40, '2026-01-04 14:42:36'),
(25, 41, 40, '2026-01-04 14:45:13'),
(26, 42, 40, '2026-01-04 14:46:23'),
(27, 43, 40, '2026-01-04 14:48:28'),
(32, 56, 41, '2026-01-04 18:54:02'),
(33, 57, 41, '2026-01-04 21:05:25'),
(34, 58, 41, '2026-01-04 21:06:49'),
(35, 59, 41, '2026-01-04 21:09:46'),
(36, 60, 41, '2026-01-04 21:11:19'),
(37, 61, 41, '2026-01-04 21:12:33'),
(38, 62, 41, '2026-01-04 21:13:46'),
(39, 63, 41, '2026-01-04 21:14:46'),
(40, 64, 43, '2026-01-05 15:44:56'),
(41, 65, 43, '2026-01-06 05:11:04'),
(42, 66, 43, '2026-01-06 05:12:10'),
(43, 67, 43, '2026-01-06 05:13:32'),
(44, 68, 43, '2026-01-06 05:14:42'),
(45, 69, 43, '2026-01-06 05:16:43'),
(46, 70, 43, '2026-01-06 05:18:03'),
(47, 71, 43, '2026-01-06 05:24:23'),
(48, 72, 43, '2026-01-06 05:25:45'),
(49, 73, 43, '2026-01-06 05:26:39'),
(50, 74, 43, '2026-01-06 05:27:40'),
(51, 75, 43, '2026-01-06 05:28:55'),
(52, 76, 43, '2026-01-06 05:29:40'),
(53, 77, 43, '2026-01-06 05:31:19');

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
  ADD CONSTRAINT `categorie_types` FOREIGN KEY (`types`) REFERENCES `types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `lien_categorie` FOREIGN KEY (`categorie`) REFERENCES `categorie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
