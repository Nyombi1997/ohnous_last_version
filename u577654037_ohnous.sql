-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 07 jan. 2026 à 23:59
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
-- Base de données : `u577654037_ohnous`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `id` int NOT NULL AUTO_INCREMENT,
  `unique_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `prix` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `reserve` int DEFAULT '1',
  `boutique` int NOT NULL,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `relation_boutique` (`boutique`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('Talon grande dame rouge bordeau', 77, 'article_695c9017275d2', 'talon-grande-dame-rouge-bordeau', 36, 'Magnifique Talon grande dame rouge bordeau', 1, 3, '2026-01-06 05:31:19'),
('Haut corset gris', 78, 'article_695ea789b49a5', 'haut-corset-gris', 35, 'Magnifique haut corset gris', 1, 4, '2026-01-07 19:35:53'),
('Longue robe rouge décolté plongeant', 79, 'article_695ea83219679', 'longue-robe-rouge-d-ecolt-e-plongeant', 120, 'Magnifique longue robe rouge décolté plongeant', 1, 4, '2026-01-07 19:38:42'),
('Robe orange à manche longue', 80, 'article_695ea91cb39cc', 'robe-orange-a-manche-longue', 40, 'Magnifique robe orange à manche longue', 1, 4, '2026-01-07 19:42:36'),
('Mini robe noir de soirée', 81, 'article_695ea981636dc', 'mini-robe-noir-de-soir-ee', 50, 'Magnifique mini robe noir de soirée', 1, 4, '2026-01-07 19:44:17'),
('Robe élegante vest grise', 82, 'article_695eaa22e7c69', 'robe-elegante-vest-grise', 109, 'Parfait pour vos soirée professionnel', 1, 4, '2026-01-07 19:46:58'),
('Robe fleure roge', 83, 'article_695eaa5e1ee48', 'robe-fleure-roge', 79, 'Parfaite pour vos journée en famille', 1, 4, '2026-01-07 19:47:58'),
('Mini robe de soirée coeur rouge', 84, 'article_695eaa919591a', 'mini-robe-de-soir-ee-coeur-rouge', 100, 'Une robe qui vous ressemble', 1, 4, '2026-01-07 19:48:49'),
('Robe noire Hasse de pique', 85, 'article_695eab01acd2a', 'robe-noire-hasse-de-pique', 90, 'Une robe d\'une femme qui s\'aime', 1, 4, '2026-01-07 19:50:41'),
('Robe crème jaune', 86, 'article_695eab29e1faa', 'robe-cr-eme-jaune', 80, '', 1, 4, '2026-01-07 19:51:21'),
('Robe de gala coeur bleu', 87, 'article_695eac033726e', 'robe-de-gala-coeur-bleu', 124, 'Sentez vous bien en soirée', 1, 4, '2026-01-07 19:54:59'),
('Robe noir aux roses blanches', 88, 'article_695eac520c590', 'robe-noir-aux-roses-blanches', 70, 'Magnifique Robe noir aux roses blanches', 1, 4, '2026-01-07 19:56:18'),
('Robe bleu évasée', 89, 'article_695eacf84bcfe', 'robe-bleu-evas-ee', 140, 'Une robe qui aime votre corps', 1, 4, '2026-01-07 19:59:04'),
('Robe fleurie', 90, 'article_695ead5d0967b', 'robe-fleurie', 50, 'Magnifique Robe fleurie\nIdéal pour vos barbecues', 1, 4, '2026-01-07 20:00:45'),
('Robe dame nature', 91, 'article_695eae1577c32', 'robe-dame-nature', 125, 'Communiez avec la nature', 1, 4, '2026-01-07 20:03:49'),
('Robe libellule', 92, 'article_695eae5d47d50', 'robe-libellule', 45, 'Vole comme un papillon', 1, 4, '2026-01-07 20:05:01'),
('Robe rose jaune', 93, 'article_695eae9315c28', 'robe-rose-jaune', 190, 'Vous êtes la fleure que vous pensez que vous êtes', 1, 4, '2026-01-07 20:05:55'),
('Ensemble en jeans', 94, 'article_695eb0bb21d9a', 'ensemble-en-jeans', 150, 'Magnifique complet en jeans', 1, 4, '2026-01-07 20:15:07'),
('Mini robe arc-en-ciel', 95, 'article_695eb0e620c71', 'mini-robe-arc-en-ciel', 60, 'Mini robe arc-en-ciel', 1, 4, '2026-01-07 20:15:50'),
('Duo vert et bleu', 96, 'article_695eb137e3933', 'duo-vert-et-bleu', 250, 'Sortez avec votre meilleure amie', 1, 4, '2026-01-07 20:17:11'),
('Robe rouge femme africaine', 97, 'article_695eb1645fb61', 'robe-rouge-femme-africaine', 130, 'Magnifique Robe rouge femme africaine', 1, 4, '2026-01-07 20:17:56'),
('Robe fleure rose', 98, 'article_695eb195c9cf0', 'robe-fleure-rose', 140, 'Une magnifique rose ce soir', 1, 4, '2026-01-07 20:18:45'),
('Une danse dorée', 99, 'article_695eb1dfa9f72', 'une-danse-dor-ee', 80, 'Une danse dorée', 1, 4, '2026-01-07 20:19:59'),
('Robe de soirée dorée caramel', 100, 'article_695eb2da75e81', 'robe-de-soir-ee-dor-ee-caramel', 180, 'L\'or a été caraméliser', 1, 4, '2026-01-07 20:24:10'),
('Robe feuille de fleure', 101, 'article_695edb66d96fc', 'robe-feuille-de-fleure', 36, '', 1, 5, '2026-01-07 23:17:10'),
('Boubou couleur Metal', 102, 'article_695edc7584045', 'boubou-couleur-metal', 45, '', 1, 5, '2026-01-07 23:21:41'),
('Robe rose fleurie', 103, 'article_695edca01a4a4', 'robe-rose-fleurie', 20, '', 1, 5, '2026-01-07 23:22:24'),
('Robe violette', 104, 'article_695edccc9aaf2', 'robe-violette', 30, '', 1, 5, '2026-01-07 23:23:08'),
('Robe orange neud papillon', 105, 'article_695edcf74cfc2', 'robe-orange-neud-papillon', 20, '', 1, 5, '2026-01-07 23:23:51'),
('Robe nouvelle plante verte', 106, 'article_695edd19eb06c', 'robe-nouvelle-plante-verte', 30, '', 1, 5, '2026-01-07 23:24:25'),
('Robe rouge sang', 107, 'article_695edd4f3e77e', 'robe-rouge-sang', 80, '', 1, 5, '2026-01-07 23:25:19'),
('Robe en pagne dorée', 108, 'article_695edd7f7dc7b', 'robe-en-pagne-dor-ee', 60, '', 1, 5, '2026-01-07 23:26:07'),
('Boubou grande dame vert sombre', 109, 'article_695eddae49148', 'boubou-grande-dame-vert-sombre', 50, '', 1, 5, '2026-01-07 23:26:54'),
('Boubou caméléon', 110, 'article_695edddf954df', 'boubou-cam-el-eon', 60, '', 1, 5, '2026-01-07 23:27:43'),
('Robe violette grande dame', 111, 'article_695ede213e2c4', 'robe-violette-grande-dame', 70, '', 1, 5, '2026-01-07 23:28:49'),
('Boubou beige', 112, 'article_695ede5d81fa9', 'boubou-beige', 40, '', 1, 5, '2026-01-07 23:29:49'),
('Boubou Jaune dorée', 113, 'article_695edeaed4174', 'boubou-jaune-dor-ee', 90, '', 1, 5, '2026-01-07 23:31:10'),
('Robe jupe léopard', 114, 'article_695eded51308d', 'robe-jupe-l-eopard', 65, '', 1, 5, '2026-01-07 23:31:49'),
('Robe beige', 115, 'article_695edf629ba7a', 'robe-beige', 40, '', 1, 5, '2026-01-07 23:34:10'),
('Boucle d\'oreille soleil', 116, 'article_695ee047d29f6', 'boucle-d-oreille-soleil', 12, '', 1, 6, '2026-01-07 23:37:59'),
('Ensemble colliers, boucles d\'oreilles, bagues', 117, 'article_695ee27f68982', 'ensemble-coliers-boucles-d-oreilles-bagues', 50, '', 1, 6, '2026-01-07 23:47:27'),
('Bracelets motife marbre brun', 118, 'article_695ee2c63368d', 'braceles-motife-marbre-brun', 15, '', 1, 6, '2026-01-07 23:48:38'),
('Bracelets dorée', 119, 'article_695ee32de93ad', 'braceles-dor-ee', 12, '', 1, 6, '2026-01-07 23:50:21'),
('Bagues perles argentée', 120, 'article_695ee37b7011a', 'bagues-perles-argent-ee', 20, '', 1, 6, '2026-01-07 23:51:39'),
('Bracelets motif marbre noir', 121, 'article_695ee470c4584', 'bracelets-motif-marbre-noir', 25, '', 1, 6, '2026-01-07 23:55:44'),
('Complet bracelet marbre beige et vert', 122, 'article_695ee5332ca61', 'complet-bracelet-marbre-beige-et-vert', 30, '', 1, 6, '2026-01-07 23:58:59'),
('Boucles d\'oreilles see me', 123, 'article_695ee5617c472', 'boucles-d-oreilles-see-me', 15, '', 1, 6, '2026-01-07 23:59:45'),
('Colliers en perle grande dame', 124, 'article_695ee5b54af3e', 'colliers-en-perle-grande-dame', 20, '', 1, 6, '2026-01-08 00:01:09'),
('Bracelet diadème en perles', 125, 'article_695ee6353fa03', 'bracelet-diad-eme-en-perles', 23, '', 1, 6, '2026-01-08 00:03:17'),
('Trois bracelets dorée', 126, 'article_695ee69ce5624', 'trois-bracelets-dor-ee', 30, '', 1, 6, '2026-01-08 00:05:00'),
('Coiffe de perle', 127, 'article_695ee72878164', 'coiffe-de-perle', 42, '', 1, 6, '2026-01-08 00:07:20'),
('Bracelet motif marbre blanc assorti à ses bagues', 128, 'article_695ee774f07f4', 'bracelet-motif-marbre-blanc-assorti-a-ses-bagues', 60, '', 1, 6, '2026-01-08 00:08:36'),
('Bracelets élégante dame', 129, 'article_695ee79e15305', 'bracelets-el-egante-dame', 40, '', 1, 6, '2026-01-08 00:09:18'),
('coiffe de perle Queen africa', 130, 'article_695ee7d19c471', 'coiffe-de-perle-queen-africa', 25, '', 1, 6, '2026-01-08 00:10:09'),
('Complet bracelets et bagues argentée', 131, 'article_695ee810e4537', 'complet-bracelets-et-bagues-argent-ee', 35, '', 1, 6, '2026-01-08 00:11:12'),
('Parure collier et boucles d’oreilles doré', 132, 'article_695ee852c4480', 'parure-collier-et-boucles-d-oreilles-dor-e', 45, '', 1, 6, '2026-01-08 00:12:18'),
('Bracelets motif marbre blanc', 133, 'article_695ee88aec715', 'bracelets-motif-marbre-blanc', 20, '', 1, 6, '2026-01-08 00:13:14');

-- --------------------------------------------------------

--
-- Structure de la table `boutiques`
--

DROP TABLE IF EXISTS `boutiques`;
CREATE TABLE IF NOT EXISTS `boutiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `adresse_email` int DEFAULT NULL,
  `mdp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `nom_utilisateur` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `boutiques`
--

INSERT INTO `boutiques` (`id`, `nom`, `adresse_email`, `mdp`, `nom_utilisateur`, `description`, `date_ajout`) VALUES
(1, 'Bk sacs', NULL, NULL, NULL, 'Une boutique vintage de sac, une fouineuse amoureuse des sacs en friperie boutique 2025', '2025-12-18 20:28:28'),
(2, 'Luxe lunetti', NULL, NULL, NULL, 'Luxe lunetti est une boutique en ligne de précommande de lunettes de luxe depuis 2023, au menu des marques comme loewe, ferragano, Tom Ford, etc...', '2026-01-04 15:01:25'),
(3, 'Chaussure Store', NULL, NULL, NULL, 'Est une boutique pour chaussure vintage Paris - Kinshasa depuis 2020, si vous aimez des pièces iconiques vous êtes au bons endroits.', '2026-01-05 13:54:27'),
(4, 'Fashion exo kin', NULL, NULL, NULL, 'Nous vendons en ligne les articles fashion nova, shein, maxi dress, ohpolly', '2026-01-07 19:18:39'),
(5, 'Boubou Kin', NULL, NULL, NULL, NULL, '2026-01-07 23:13:02'),
(6, 'Accessoire de Sarah', NULL, NULL, NULL, NULL, '2026-01-07 23:35:58');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
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
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(53, 77, 2, '2026-01-06 05:31:19'),
(54, 78, 2, '2026-01-07 19:35:53'),
(55, 79, 2, '2026-01-07 19:38:42'),
(56, 80, 2, '2026-01-07 19:42:36'),
(57, 81, 2, '2026-01-07 19:44:17'),
(58, 82, 2, '2026-01-07 19:46:58'),
(59, 83, 2, '2026-01-07 19:47:58'),
(60, 84, 2, '2026-01-07 19:48:49'),
(61, 85, 2, '2026-01-07 19:50:41'),
(62, 86, 2, '2026-01-07 19:51:21'),
(63, 87, 2, '2026-01-07 19:54:59'),
(64, 88, 2, '2026-01-07 19:56:18'),
(65, 89, 2, '2026-01-07 19:59:04'),
(66, 90, 2, '2026-01-07 20:00:45'),
(67, 91, 2, '2026-01-07 20:03:49'),
(68, 92, 2, '2026-01-07 20:05:01'),
(69, 93, 2, '2026-01-07 20:05:55'),
(70, 94, 2, '2026-01-07 20:15:07'),
(71, 95, 2, '2026-01-07 20:15:50'),
(72, 96, 2, '2026-01-07 20:17:11'),
(73, 97, 2, '2026-01-07 20:17:56'),
(74, 98, 2, '2026-01-07 20:18:45'),
(75, 99, 2, '2026-01-07 20:19:59'),
(76, 100, 2, '2026-01-07 20:24:10'),
(77, 101, 2, '2026-01-07 23:17:10'),
(78, 102, 2, '2026-01-07 23:21:41'),
(79, 103, 2, '2026-01-07 23:22:24'),
(80, 104, 2, '2026-01-07 23:23:08'),
(81, 105, 2, '2026-01-07 23:23:51'),
(82, 106, 2, '2026-01-07 23:24:25'),
(83, 107, 2, '2026-01-07 23:25:19'),
(84, 108, 2, '2026-01-07 23:26:07'),
(85, 109, 2, '2026-01-07 23:26:54'),
(86, 110, 2, '2026-01-07 23:27:43'),
(87, 111, 2, '2026-01-07 23:28:49'),
(88, 112, 2, '2026-01-07 23:29:49'),
(89, 113, 2, '2026-01-07 23:31:10'),
(90, 114, 2, '2026-01-07 23:31:49'),
(91, 115, 2, '2026-01-07 23:34:10'),
(92, 116, 2, '2026-01-07 23:37:59'),
(93, 117, 2, '2026-01-07 23:47:27'),
(94, 118, 2, '2026-01-07 23:48:38'),
(95, 119, 2, '2026-01-07 23:50:21'),
(96, 120, 2, '2026-01-07 23:51:39'),
(97, 121, 2, '2026-01-07 23:55:44'),
(98, 122, 2, '2026-01-07 23:58:59'),
(99, 123, 2, '2026-01-07 23:59:45'),
(100, 124, 2, '2026-01-08 00:01:09'),
(101, 125, 2, '2026-01-08 00:03:17'),
(102, 126, 2, '2026-01-08 00:05:00'),
(103, 127, 2, '2026-01-08 00:07:20'),
(104, 128, 2, '2026-01-08 00:08:36'),
(105, 129, 2, '2026-01-08 00:09:18'),
(106, 130, 2, '2026-01-08 00:10:09'),
(107, 131, 2, '2026-01-08 00:11:12'),
(108, 132, 2, '2026-01-08 00:12:18'),
(109, 133, 2, '2026-01-08 00:13:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(43, 2, 43, '2026-01-05 14:27:53'),
(47, 2, 47, '2026-01-07 02:08:47'),
(48, 2, 48, '2026-01-07 20:09:31'),
(49, 2, 49, '2026-01-07 23:21:14');

-- --------------------------------------------------------

--
-- Structure de la table `image_articles`
--

DROP TABLE IF EXISTS `image_articles`;
CREATE TABLE IF NOT EXISTS `image_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article` int NOT NULL,
  `img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `alt_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `styles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `modif_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `img_article` (`article`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(116, 77, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673877105_ONGk6X8QE.webp', 'talon-grande-dame-rouge-bordeau', 'rgb(129, 113, 109)', 'width: 100%; height: auto;', '2026-01-06 05:31:19', '2026-01-06 05:31:19'),
(117, 78, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767810950855_tOlsYU-GL.webp', 'haut-corset-gris', 'rgb(146, 139, 138)', 'width: auto; height: 100%;', '2026-01-07 19:35:53', '2026-01-07 19:35:53'),
(118, 79, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811118630_WeaD_ebze.webp', 'longue-robe-rouge-d-ecolt-e-plongeant', 'rgb(119, 104, 109)', 'width: auto; height: 100%;', '2026-01-07 19:38:42', '2026-01-07 19:38:42'),
(119, 80, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811353588_WbZwro7n6.webp', 'robe-orange-a-manche-longue', 'rgb(114, 93, 82)', 'width: auto; height: 100%;', '2026-01-07 19:42:36', '2026-01-07 19:42:36'),
(120, 81, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811455148_LBo68tpi2w.webp', 'mini-robe-noir-de-soir-ee', 'rgb(198, 187, 183)', 'width: auto; height: 100%;', '2026-01-07 19:44:17', '2026-01-07 19:44:17'),
(121, 82, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811616445_DmKFd73qp.webp', 'robe-elegante-vest-grise', 'rgb(142, 144, 153)', 'width: auto; height: 100%;', '2026-01-07 19:46:58', '2026-01-07 19:46:58'),
(122, 83, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811675412__2_UmR7eV.webp', 'robe-fleure-roge', 'rgb(128, 110, 103)', 'width: auto; height: 100%;', '2026-01-07 19:47:58', '2026-01-07 19:47:58'),
(123, 84, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811727116_FlqRk9vOi.webp', 'mini-robe-de-soir-ee-coeur-rouge', 'rgb(120, 85, 72)', 'width: auto; height: 100%;', '2026-01-07 19:48:49', '2026-01-07 19:48:49'),
(124, 85, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811839463_FxZxiOaus.webp', 'robe-noire-hasse-de-pique', 'rgb(104, 92, 85)', 'width: auto; height: 100%;', '2026-01-07 19:50:41', '2026-01-07 19:50:41'),
(125, 86, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811879053_YGckweprh.webp', 'robe-cr-eme-jaune', 'rgb(143, 116, 88)', 'width: auto; height: 100%;', '2026-01-07 19:51:21', '2026-01-07 19:51:21'),
(126, 87, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812097132_J-IgzirKM.webp', 'robe-de-gala-coeur-bleu', 'rgb(160, 176, 192)', 'width: auto; height: 100%;', '2026-01-07 19:54:59', '2026-01-07 19:54:59'),
(127, 88, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812175893_XeDmhm3Sz.webp', 'robe-noir-aux-roses-blanches', 'rgb(173, 150, 116)', 'width: auto; height: 100%;', '2026-01-07 19:56:18', '2026-01-07 19:56:18'),
(128, 89, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812342133_hFI2f9POS.webp', 'robe-bleu-evas-ee', 'rgb(120, 101, 97)', 'width: auto; height: 100%;', '2026-01-07 19:59:04', '2026-01-07 19:59:04'),
(129, 90, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812442952_ykFMwpAJR.webp', 'robe-fleurie', 'rgb(221, 209, 201)', 'width: auto; height: 100%;', '2026-01-07 20:00:45', '2026-01-07 20:00:45'),
(130, 91, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812627157_LNw83zDzu.webp', 'robe-dame-nature', 'rgb(196, 197, 180)', 'width: auto; height: 100%;', '2026-01-07 20:03:49', '2026-01-07 20:03:49'),
(131, 92, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812699148_0t9-X1F8c.webp', 'robe-libellule', 'rgb(192, 184, 195)', 'width: auto; height: 100%;', '2026-01-07 20:05:01', '2026-01-07 20:05:01'),
(132, 93, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812752907_KncJC5AU9s.webp', 'robe-rose-jaune', 'rgb(190, 187, 166)', 'width: auto; height: 100%;', '2026-01-07 20:05:55', '2026-01-07 20:05:55'),
(133, 94, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813304998_BP0cpar5F.webp', 'ensemble-en-jeans', 'rgb(127, 119, 113)', 'width: auto; height: 100%;', '2026-01-07 20:15:07', '2026-01-07 20:15:07'),
(134, 95, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813347527_zJrq52VDa.webp', 'mini-robe-arc-en-ciel', 'rgb(160, 90, 81)', 'width: auto; height: 100%;', '2026-01-07 20:15:50', '2026-01-07 20:15:50'),
(135, 96, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813429835_uJ05LntOe.webp', 'duo-vert-et-bleu', 'rgb(89, 94, 79)', 'width: auto; height: 100%;', '2026-01-07 20:17:11', '2026-01-07 20:17:11'),
(136, 97, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813474167_aX-ictxP7.webp', 'robe-rouge-femme-africaine', 'rgb(26, 80, 118)', 'width: auto; height: 100%;', '2026-01-07 20:17:56', '2026-01-07 20:17:56'),
(137, 98, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813523678_-k0RBGi9-.webp', 'robe-fleure-rose', 'rgb(171, 140, 157)', 'width: auto; height: 100%;', '2026-01-07 20:18:45', '2026-01-07 20:18:45'),
(138, 99, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813597194_-pn3Fi7KS.webp', 'une-danse-dor-ee', 'rgb(98, 87, 81)', 'width: auto; height: 100%;', '2026-01-07 20:19:59', '2026-01-07 20:19:59'),
(139, 100, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813848102_rI_IlvZXM.webp', 'robe-de-soir-ee-dor-ee-caramel', 'rgb(61, 48, 37)', 'width: auto; height: 100%;', '2026-01-07 20:24:10', '2026-01-07 20:24:10'),
(140, 101, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824228549_nYwNsXV28.webp', 'robe-feuille-de-fleure', 'rgb(142, 110, 80)', 'width: auto; height: 100%;', '2026-01-07 23:17:10', '2026-01-07 23:17:10'),
(141, 102, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824499277_6XyBCV8qK.webp', 'boubou-couleur-metal', 'rgb(159, 149, 161)', 'width: auto; height: 100%;', '2026-01-07 23:21:41', '2026-01-07 23:21:41'),
(142, 103, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824541438_l21CDyjUtc.webp', 'robe-rose-fleurie', 'rgb(164, 119, 126)', 'width: auto; height: 100%;', '2026-01-07 23:22:24', '2026-01-07 23:22:24'),
(143, 104, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824586526_HMxGz5WZA.webp', 'robe-violette', 'rgb(175, 123, 139)', 'width: auto; height: 100%;', '2026-01-07 23:23:08', '2026-01-07 23:23:08'),
(144, 105, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824628832_MevvlITvm.webp', 'robe-orange-neud-papillon', 'rgb(180, 103, 93)', 'width: auto; height: 100%;', '2026-01-07 23:23:51', '2026-01-07 23:23:51'),
(145, 106, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824663834_heX-h9qXf.webp', 'robe-nouvelle-plante-verte', 'rgb(132, 118, 94)', 'width: auto; height: 100%;', '2026-01-07 23:24:25', '2026-01-07 23:24:25'),
(146, 107, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824717119_0CPU3R_FK.webp', 'robe-rouge-sang', 'rgb(118, 90, 86)', 'width: auto; height: 100%;', '2026-01-07 23:25:19', '2026-01-07 23:25:19'),
(147, 108, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824765107_EC_46h2lH.webp', 'robe-en-pagne-dor-ee', 'rgb(161, 147, 137)', 'width: auto; height: 100%;', '2026-01-07 23:26:07', '2026-01-07 23:26:07'),
(148, 109, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824812192_mKWOPNjzx.webp', 'boubou-grande-dame-vert-sombre', 'rgb(134, 133, 129)', 'width: auto; height: 100%;', '2026-01-07 23:26:54', '2026-01-07 23:26:54'),
(149, 110, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824861158_MTg2IpHR1.webp', 'boubou-cam-el-eon', 'rgb(120, 138, 151)', 'width: auto; height: 100%;', '2026-01-07 23:27:43', '2026-01-07 23:27:43'),
(150, 111, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824927109_1CtC24xHv.webp', 'robe-violette-grande-dame', 'rgb(104, 79, 127)', 'width: auto; height: 100%;', '2026-01-07 23:28:49', '2026-01-07 23:28:49'),
(151, 112, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824987474_sRxICNyS3.webp', 'boubou-beige', 'rgb(190, 167, 141)', 'width: auto; height: 100%;', '2026-01-07 23:29:49', '2026-01-07 23:29:49'),
(152, 113, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825068817_tQc0UNkeb.webp', 'boubou-jaune-dor-ee', 'rgb(239, 207, 167)', 'width: auto; height: 100%;', '2026-01-07 23:31:10', '2026-01-07 23:31:10'),
(153, 114, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825106622_K3I6mtCBA.webp', 'robe-jupe-l-eopard', 'rgb(145, 126, 122)', 'width: auto; height: 100%;', '2026-01-07 23:31:49', '2026-01-07 23:31:49'),
(154, 115, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825248217_QANYQlrxN.webp', 'robe-beige', 'rgb(162, 150, 134)', 'width: auto; height: 100%;', '2026-01-07 23:34:10', '2026-01-07 23:34:10'),
(155, 116, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825477764_hzsUgul0O.webp', 'boucle-d-oreille-soleil', 'rgb(144, 131, 126)', 'width: auto; height: 100%;', '2026-01-07 23:37:59', '2026-01-07 23:37:59'),
(156, 117, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826045320_j8HCzFlld.webp', 'ensemble-coliers-boucles-d-oreilles-bagues', 'rgb(146, 104, 79)', 'width: auto; height: 100%;', '2026-01-07 23:47:27', '2026-01-07 23:47:27'),
(157, 118, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826115791_WxGibu7S7.webp', 'braceles-motife-marbre-brun', 'rgb(187, 159, 139)', 'width: auto; height: 100%;', '2026-01-07 23:48:38', '2026-01-07 23:48:38'),
(158, 119, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826219553_ZvcruG0we.webp', 'braceles-dor-ee', 'rgb(139, 113, 94)', 'width: 100%; height: auto;', '2026-01-07 23:50:21', '2026-01-07 23:50:21'),
(159, 120, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826297355_MZNXSiIDO.webp', 'bagues-perles-argent-ee', 'rgb(170, 155, 143)', 'width: auto; height: 100%;', '2026-01-07 23:51:39', '2026-01-07 23:51:39'),
(160, 121, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826542741_i-Bzwt6ot.webp', 'bracelets-motif-marbre-noir', 'rgb(172, 149, 130)', 'width: auto; height: 100%;', '2026-01-07 23:55:44', '2026-01-07 23:55:44'),
(161, 122, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826737044_qG0EmuDlX.webp', 'complet-bracelet-marbre-beige-et-vert', 'rgb(160, 150, 126)', 'width: auto; height: 100%;', '2026-01-07 23:58:59', '2026-01-07 23:58:59'),
(162, 123, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826783460_mc2eiA2N4.webp', 'boucles-d-oreilles-see-me', 'rgb(146, 105, 74)', 'width: auto; height: 100%;', '2026-01-07 23:59:45', '2026-01-07 23:59:45'),
(163, 124, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826867158_nIkL5PulW.webp', 'colliers-en-perle-grande-dame', 'rgb(137, 115, 104)', 'width: auto; height: 100%;', '2026-01-08 00:01:09', '2026-01-08 00:01:09'),
(164, 125, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826995148_ghboIl9IZ.webp', 'bracelet-diad-eme-en-perles', 'rgb(119, 72, 51)', 'width: auto; height: 100%;', '2026-01-08 00:03:17', '2026-01-08 00:03:17'),
(165, 126, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827098826_aIf_kLY0T.webp', 'trois-bracelets-dor-ee', 'rgb(115, 104, 89)', 'width: 100%; height: auto;', '2026-01-08 00:05:00', '2026-01-08 00:05:00'),
(166, 127, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827238226_xmzUOog-x.webp', 'coiffe-de-perle', 'rgb(123, 117, 111)', 'width: auto; height: 100%;', '2026-01-08 00:07:20', '2026-01-08 00:07:20'),
(167, 128, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827315266_gf0D3yfcW.webp', 'bracelet-motif-marbre-blanc-assorti-a-ses-bagues', 'rgb(119, 100, 88)', 'width: auto; height: 100%;', '2026-01-08 00:08:36', '2026-01-08 00:08:36'),
(168, 129, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827356069_RtZX2K6T-.webp', 'bracelets-el-egante-dame', 'rgb(166, 140, 129)', 'width: auto; height: 100%;', '2026-01-08 00:09:18', '2026-01-08 00:09:18'),
(169, 130, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827407583_KPUH3D6bK.webp', 'coiffe-de-perle-queen-africa', 'rgb(62, 61, 54)', 'width: auto; height: 100%;', '2026-01-08 00:10:09', '2026-01-08 00:10:09'),
(170, 131, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827470295_4oqn4sucL.webp', 'complet-bracelets-et-bagues-argent-ee', 'rgb(153, 130, 116)', 'width: auto; height: 100%;', '2026-01-08 00:11:12', '2026-01-08 00:11:12'),
(171, 132, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827536679_BvUrlgFsj.webp', 'parure-collier-et-boucles-d-oreilles-dor-e', 'rgb(127, 82, 63)', 'width: auto; height: 100%;', '2026-01-08 00:12:18', '2026-01-08 00:12:18'),
(172, 133, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827592887_hzEDEyBvK.webp', 'bracelets-motif-marbre-blanc', 'rgb(184, 181, 174)', 'width: auto; height: 100%;', '2026-01-08 00:13:14', '2026-01-08 00:13:14');

-- --------------------------------------------------------

--
-- Structure de la table `tailles`
--

DROP TABLE IF EXISTS `tailles`;
CREATE TABLE IF NOT EXISTS `tailles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(220, '5.5', 'Pointure US', '2026-01-05 14:24:50'),
(221, '6', 'Pointure US', '2026-01-05 14:24:50'),
(222, '6.5', 'Pointure US', '2026-01-05 14:24:50'),
(223, '7.5', 'Pointure US', '2026-01-05 14:24:50'),
(224, '8.5', 'Pointure US', '2026-01-05 14:24:50'),
(225, '9.5', 'Pointure US', '2026-01-05 14:24:50'),
(226, '10', 'Pointure US', '2026-01-05 14:24:50'),
(227, '11', 'Pointure US', '2026-01-05 14:24:50'),
(228, '12', 'Pointure US', '2026-01-05 14:24:50'),
(229, '13', 'Pointure US', '2026-01-05 14:24:50'),
(230, '14', 'Pointure US', '2026-01-05 14:24:50'),
(231, '3.5', 'Pointure UK', '2026-01-05 14:24:50'),
(232, '4', 'Pointure UK', '2026-01-05 14:24:50'),
(233, '5', 'Pointure UK', '2026-01-05 14:24:50'),
(234, '6', 'Pointure UK', '2026-01-05 14:24:50'),
(235, '6.5', 'Pointure UK', '2026-01-05 14:24:50'),
(236, '7.5', 'Pointure UK', '2026-01-05 14:24:50'),
(237, '8', 'Pointure UK', '2026-01-05 14:24:50'),
(238, '9', 'Pointure UK', '2026-01-05 14:24:50'),
(239, '10', 'Pointure UK', '2026-01-05 14:24:50'),
(240, '11', 'Pointure UK', '2026-01-05 14:24:50'),
(241, '12', 'Pointure UK', '2026-01-05 14:24:50'),
(242, 'XS', 'Taille', '2026-01-07 01:25:36'),
(243, 'S', 'Taille', '2026-01-07 01:25:36'),
(244, 'S/M', 'Taille', '2026-01-07 01:25:36'),
(245, 'M', 'Taille', '2026-01-07 01:25:36'),
(246, 'L', 'Taille', '2026-01-07 01:25:36'),
(247, 'L/XL', 'Taille', '2026-01-07 01:25:36'),
(248, 'XL', 'Taille', '2026-01-07 01:25:36'),
(249, 'XXL', 'Taille', '2026-01-07 01:25:36'),
(250, '2', 'Taille US', '2026-01-07 01:25:36'),
(251, '4', 'Taille US', '2026-01-07 01:25:36'),
(252, '6', 'Taille US', '2026-01-07 01:25:36'),
(253, '8', 'Taille US', '2026-01-07 01:25:36'),
(254, '10', 'Taille US', '2026-01-07 01:25:36'),
(255, '12', 'Taille US', '2026-01-07 01:25:36'),
(256, '14', 'Taille US', '2026-01-07 01:25:36'),
(257, '16', 'Taille US', '2026-01-07 01:25:36'),
(258, '18', 'Taille US', '2026-01-07 01:25:36'),
(259, '20', 'Taille US', '2026-01-07 01:25:36'),
(260, '34', 'Taille EU', '2026-01-07 01:25:36'),
(261, '36', 'Taille EU', '2026-01-07 01:25:36'),
(262, '38', 'Taille EU', '2026-01-07 01:25:36'),
(263, '40', 'Taille EU', '2026-01-07 01:25:36'),
(264, '42', 'Taille EU', '2026-01-07 01:25:36'),
(265, '44', 'Taille EU', '2026-01-07 01:25:36'),
(266, '46', 'Taille EU', '2026-01-07 01:25:36'),
(267, '48', 'Taille EU', '2026-01-07 01:25:36'),
(268, 'XXS', 'Taille', '2026-01-07 01:25:36'),
(269, '0', 'Taille US', '2026-01-07 01:25:36'),
(270, '32', 'Taille EU', '2026-01-07 01:25:36'),
(271, '47', 'Pointure EU', '2026-01-05 14:24:50'),
(272, '48', 'Pointure EU', '2026-01-05 14:24:50'),
(273, '7', 'Pointure US', '2026-01-05 14:24:50'),
(274, '8', 'Pointure US', '2026-01-05 14:24:50'),
(275, '10.5', 'Pointure US', '2026-01-05 14:24:50'),
(276, '11.5', 'Pointure US', '2026-01-05 14:24:50'),
(277, '12.5', 'Pointure US', '2026-01-05 14:24:50'),
(278, '13.5', 'Pointure US', '2026-01-05 14:24:50'),
(279, '14.5', 'Pointure US', '2026-01-05 14:24:50'),
(280, '5.5', 'Pointure UK', '2026-01-05 14:24:50'),
(281, '13', 'Pointure UK', '2026-01-05 14:24:50'),
(282, '14', 'Pointure UK', '2026-01-05 14:24:50');

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
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(38, 219, 43, '2026-01-05 14:36:29'),
(39, 242, 6, '2026-01-07 01:34:30'),
(40, 243, 6, '2026-01-07 01:34:30'),
(41, 244, 6, '2026-01-07 01:34:30'),
(42, 246, 6, '2026-01-07 01:34:30'),
(43, 247, 6, '2026-01-07 01:34:30'),
(44, 248, 6, '2026-01-07 01:34:30'),
(45, 249, 6, '2026-01-07 01:34:30'),
(46, 252, 6, '2026-01-07 01:34:30'),
(47, 250, 6, '2026-01-07 01:34:30'),
(48, 251, 6, '2026-01-07 01:34:30'),
(49, 253, 6, '2026-01-07 01:34:30'),
(50, 254, 6, '2026-01-07 01:34:30'),
(51, 255, 6, '2026-01-07 01:34:30'),
(52, 256, 6, '2026-01-07 01:34:30'),
(53, 257, 6, '2026-01-07 01:34:30'),
(54, 260, 6, '2026-01-07 01:34:30'),
(55, 262, 6, '2026-01-07 01:34:30'),
(56, 263, 6, '2026-01-07 01:34:30'),
(57, 264, 6, '2026-01-07 01:34:30'),
(58, 265, 6, '2026-01-07 01:34:30'),
(59, 266, 6, '2026-01-07 01:34:30'),
(60, 267, 6, '2026-01-07 01:34:30'),
(61, 245, 6, '2026-01-07 01:35:58'),
(62, 261, 6, '2026-01-07 01:35:58'),
(87, 242, 47, '2026-01-07 01:34:30'),
(88, 243, 47, '2026-01-07 01:34:30'),
(89, 244, 47, '2026-01-07 01:34:30'),
(90, 246, 47, '2026-01-07 01:34:30'),
(91, 247, 47, '2026-01-07 01:34:30'),
(92, 248, 47, '2026-01-07 01:34:30'),
(93, 249, 47, '2026-01-07 01:34:30'),
(94, 252, 47, '2026-01-07 01:34:30'),
(95, 250, 47, '2026-01-07 01:34:30'),
(96, 251, 47, '2026-01-07 01:34:30'),
(97, 253, 47, '2026-01-07 01:34:30'),
(98, 254, 47, '2026-01-07 01:34:30'),
(99, 255, 47, '2026-01-07 01:34:30'),
(100, 256, 47, '2026-01-07 01:34:30'),
(101, 257, 47, '2026-01-07 01:34:30'),
(102, 260, 47, '2026-01-07 01:34:30'),
(103, 262, 47, '2026-01-07 01:34:30'),
(104, 263, 47, '2026-01-07 01:34:30'),
(105, 264, 47, '2026-01-07 01:34:30'),
(106, 265, 47, '2026-01-07 01:34:30'),
(107, 266, 47, '2026-01-07 01:34:30'),
(108, 267, 47, '2026-01-07 01:34:30'),
(109, 245, 47, '2026-01-07 01:35:58'),
(110, 261, 47, '2026-01-07 01:35:58'),
(111, 268, 47, '2026-01-07 19:23:59'),
(112, 269, 47, '2026-01-07 19:23:59'),
(113, 270, 47, '2026-01-07 19:23:59'),
(114, 242, 48, '2026-01-07 01:34:30'),
(115, 243, 48, '2026-01-07 01:34:30'),
(116, 244, 48, '2026-01-07 01:34:30'),
(117, 246, 48, '2026-01-07 01:34:30'),
(118, 247, 48, '2026-01-07 01:34:30'),
(119, 248, 48, '2026-01-07 01:34:30'),
(120, 249, 48, '2026-01-07 01:34:30'),
(121, 252, 48, '2026-01-07 01:34:30'),
(122, 250, 48, '2026-01-07 01:34:30'),
(123, 251, 48, '2026-01-07 01:34:30'),
(124, 253, 48, '2026-01-07 01:34:30'),
(125, 254, 48, '2026-01-07 01:34:30'),
(126, 255, 48, '2026-01-07 01:34:30'),
(127, 256, 48, '2026-01-07 01:34:30'),
(128, 257, 48, '2026-01-07 01:34:30'),
(129, 260, 48, '2026-01-07 01:34:30'),
(130, 262, 48, '2026-01-07 01:34:30'),
(131, 263, 48, '2026-01-07 01:34:30'),
(132, 264, 48, '2026-01-07 01:34:30'),
(133, 265, 48, '2026-01-07 01:34:30'),
(134, 266, 48, '2026-01-07 01:34:30'),
(135, 267, 48, '2026-01-07 01:34:30'),
(136, 245, 48, '2026-01-07 01:35:58'),
(137, 261, 48, '2026-01-07 01:35:58'),
(138, 242, 49, '2026-01-07 01:34:30'),
(139, 243, 49, '2026-01-07 01:34:30'),
(140, 244, 49, '2026-01-07 01:34:30'),
(141, 246, 49, '2026-01-07 01:34:30'),
(142, 247, 49, '2026-01-07 01:34:30'),
(143, 248, 49, '2026-01-07 01:34:30'),
(144, 249, 49, '2026-01-07 01:34:30'),
(145, 252, 49, '2026-01-07 01:34:30'),
(146, 250, 49, '2026-01-07 01:34:30'),
(147, 251, 49, '2026-01-07 01:34:30'),
(148, 253, 49, '2026-01-07 01:34:30'),
(149, 254, 49, '2026-01-07 01:34:30'),
(150, 255, 49, '2026-01-07 01:34:30'),
(151, 256, 49, '2026-01-07 01:34:30'),
(152, 257, 49, '2026-01-07 01:34:30'),
(153, 260, 49, '2026-01-07 01:34:30'),
(154, 262, 49, '2026-01-07 01:34:30'),
(155, 263, 49, '2026-01-07 01:34:30'),
(156, 264, 49, '2026-01-07 01:34:30'),
(157, 265, 49, '2026-01-07 01:34:30'),
(158, 266, 49, '2026-01-07 01:34:30'),
(159, 267, 49, '2026-01-07 01:34:30'),
(160, 245, 49, '2026-01-07 01:35:58'),
(161, 261, 49, '2026-01-07 01:35:58');

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
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(48, 215, 77, '2026-01-06 05:31:19'),
(49, 242, 78, '2026-01-07 19:35:53'),
(50, 262, 79, '2026-01-07 19:38:42'),
(51, 263, 80, '2026-01-07 19:42:36'),
(52, 262, 81, '2026-01-07 19:44:17'),
(53, 263, 82, '2026-01-07 19:46:58'),
(54, 263, 83, '2026-01-07 19:47:58'),
(55, 263, 84, '2026-01-07 19:48:49'),
(56, 261, 85, '2026-01-07 19:50:41'),
(57, 262, 86, '2026-01-07 19:51:21'),
(58, 265, 87, '2026-01-07 19:54:59'),
(59, 263, 88, '2026-01-07 19:56:18'),
(60, 263, 89, '2026-01-07 19:59:04'),
(61, 262, 90, '2026-01-07 20:00:45'),
(62, 263, 91, '2026-01-07 20:03:49'),
(63, 263, 92, '2026-01-07 20:05:01'),
(64, 263, 93, '2026-01-07 20:05:55'),
(65, 264, 94, '2026-01-07 20:15:07'),
(66, 262, 95, '2026-01-07 20:15:50'),
(67, 262, 96, '2026-01-07 20:17:11'),
(68, 263, 97, '2026-01-07 20:17:56'),
(69, 263, 98, '2026-01-07 20:18:45'),
(70, 264, 99, '2026-01-07 20:19:59'),
(71, 263, 100, '2026-01-07 20:24:10'),
(72, 247, 101, '2026-01-07 23:17:10'),
(73, 247, 102, '2026-01-07 23:21:41'),
(74, 263, 103, '2026-01-07 23:22:24'),
(75, 262, 104, '2026-01-07 23:23:08'),
(76, 262, 105, '2026-01-07 23:23:51'),
(77, 263, 106, '2026-01-07 23:24:25'),
(78, 264, 107, '2026-01-07 23:25:19'),
(79, 262, 108, '2026-01-07 23:26:07'),
(80, 263, 109, '2026-01-07 23:26:54'),
(81, 247, 110, '2026-01-07 23:27:43'),
(82, 263, 111, '2026-01-07 23:28:49'),
(83, 243, 112, '2026-01-07 23:29:49'),
(84, 247, 113, '2026-01-07 23:31:10'),
(85, 264, 114, '2026-01-07 23:31:49'),
(86, 262, 115, '2026-01-07 23:34:10');

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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `types`
--

INSERT INTO `types` (`id`, `nom`, `date_ajout`) VALUES
(1, 'T-shirts & polos', '2025-12-14 07:31:02'),
(2, 'Chemises', '2025-12-14 07:31:02'),
(3, 'Pantalons & jeans', '2025-12-14 07:31:02'),
(4, 'Vestes & manteaux', '2025-12-14 07:31:02'),
(5, 'Accessoires', '2025-12-14 07:31:02'),
(6, 'Robe', '2025-12-14 07:31:02'),
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
(43, 'Talon', '2026-01-05 14:26:40'),
(47, 'Haut', '2026-01-07 02:08:26'),
(48, 'Ensemble', '2026-01-07 20:08:58'),
(49, 'Boubou', '2026-01-07 23:17:43');

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
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(53, 77, 43, '2026-01-06 05:31:19'),
(54, 78, 47, '2026-01-07 19:35:53'),
(55, 79, 6, '2026-01-07 19:38:42'),
(56, 80, 6, '2026-01-07 19:42:36'),
(57, 81, 6, '2026-01-07 19:44:17'),
(58, 82, 6, '2026-01-07 19:46:58'),
(59, 83, 6, '2026-01-07 19:47:58'),
(60, 84, 6, '2026-01-07 19:48:49'),
(61, 85, 6, '2026-01-07 19:50:41'),
(62, 86, 6, '2026-01-07 19:51:21'),
(63, 87, 6, '2026-01-07 19:54:59'),
(64, 88, 6, '2026-01-07 19:56:18'),
(65, 89, 6, '2026-01-07 19:59:04'),
(66, 90, 6, '2026-01-07 20:00:45'),
(67, 91, 6, '2026-01-07 20:03:49'),
(68, 92, 6, '2026-01-07 20:05:01'),
(69, 93, 6, '2026-01-07 20:05:55'),
(70, 94, 48, '2026-01-07 20:15:07'),
(71, 95, 6, '2026-01-07 20:15:50'),
(72, 96, 6, '2026-01-07 20:17:11'),
(73, 97, 6, '2026-01-07 20:17:56'),
(74, 98, 6, '2026-01-07 20:18:45'),
(75, 99, 6, '2026-01-07 20:19:59'),
(76, 100, 6, '2026-01-07 20:24:10'),
(77, 101, 6, '2026-01-07 23:17:10'),
(78, 102, 49, '2026-01-07 23:21:41'),
(79, 103, 6, '2026-01-07 23:22:24'),
(80, 104, 6, '2026-01-07 23:23:08'),
(81, 105, 6, '2026-01-07 23:23:51'),
(82, 106, 6, '2026-01-07 23:24:25'),
(83, 107, 6, '2026-01-07 23:25:19'),
(84, 108, 6, '2026-01-07 23:26:07'),
(85, 109, 6, '2026-01-07 23:26:54'),
(86, 110, 49, '2026-01-07 23:27:43'),
(87, 111, 6, '2026-01-07 23:28:49'),
(88, 112, 49, '2026-01-07 23:29:49'),
(89, 113, 49, '2026-01-07 23:31:10'),
(90, 114, 6, '2026-01-07 23:31:49'),
(91, 115, 6, '2026-01-07 23:34:10'),
(92, 116, 11, '2026-01-07 23:37:59'),
(93, 117, 11, '2026-01-07 23:47:27'),
(94, 118, 11, '2026-01-07 23:48:38'),
(95, 119, 11, '2026-01-07 23:50:21'),
(96, 120, 11, '2026-01-07 23:51:39'),
(97, 121, 11, '2026-01-07 23:55:44'),
(98, 122, 11, '2026-01-07 23:58:59'),
(99, 123, 11, '2026-01-07 23:59:45'),
(100, 124, 11, '2026-01-08 00:01:09'),
(101, 125, 11, '2026-01-08 00:03:17'),
(102, 126, 11, '2026-01-08 00:05:00'),
(103, 127, 11, '2026-01-08 00:07:20'),
(104, 128, 11, '2026-01-08 00:08:36'),
(105, 129, 11, '2026-01-08 00:09:18'),
(106, 130, 11, '2026-01-08 00:10:09'),
(107, 131, 11, '2026-01-08 00:11:12'),
(108, 132, 11, '2026-01-08 00:12:18'),
(109, 133, 11, '2026-01-08 00:13:14');

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
  ADD CONSTRAINT `categorie_types` FOREIGN KEY (`types`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
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
  ADD CONSTRAINT `lien_taille` FOREIGN KEY (`tailles`) REFERENCES `tailles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lien_types` FOREIGN KEY (`types`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
