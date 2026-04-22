-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 22 avr. 2026 à 06:35
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
-- Structure de la table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` text,
  `nom` varchar(190) DEFAULT NULL,
  `profile` text,
  `created_by` int NOT NULL DEFAULT '0',
  `mdp` text,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `email`, `nom`, `profile`, `created_by`, `mdp`, `date_ajout`) VALUES
(1, 'admin@admin.com', NULL, '/asset/images/icons/favicon-1.png', 0, '$2y$12$8AE57tHjq0hnQS7AQ5doy.QB3g3i5kmpC45OImRrO8jlfiXsPNVhC', '2026-04-12 13:34:54');

-- --------------------------------------------------------

--
-- Structure de la table `admin_access_tokens`
--

DROP TABLE IF EXISTS `admin_access_tokens`;
CREATE TABLE IF NOT EXISTS `admin_access_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `token` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `redirect_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expire_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admin_access_token` (`token`),
  KEY `idx_admin_access_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admin_boutique_messages`
--

DROP TABLE IF EXISTS `admin_boutique_messages`;
CREATE TABLE IF NOT EXISTS `admin_boutique_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `boutique_id` int NOT NULL,
  `from_type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admin_password_resets`
--

DROP TABLE IF EXISTS `admin_password_resets`;
CREATE TABLE IF NOT EXISTS `admin_password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `token` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `expire_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `promo_actif` int NOT NULL DEFAULT '0',
  `promo_prix` double DEFAULT NULL,
  `boutique` int NOT NULL,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `relation_boutique` (`boutique`)
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`nom`, `id`, `unique_id`, `slug`, `prix`, `description`, `reserve`, `promo_actif`, `promo_prix`, `boutique`, `date_ajout`) VALUES
('Sac à main femme vintage', 22, 'article_694824a70c9d3', 'sac-a-main-femme-vintage', 35, 'Sac à  main pour femme couleur terre', 1, 0, NULL, 1, '2025-12-21 17:47:35'),
('Sac Guess', 23, 'article_6948393446aff', 'sac-guess', 22, 'Sac Guess rouge Bordeaux', 1, 0, NULL, 1, '2025-12-21 19:15:16'),
('Prada sac à main', 35, 'article_695a6c342dbd4', 'prada-sac-a-main', 27, 'Magnifique sac prada', 1, 0, NULL, 1, '2026-01-04 14:33:40'),
('Sac four tout', 36, 'article_695a6cb3834d4', 'sac-four-tout', 20, 'Sac neous four tout', 1, 0, NULL, 1, '2026-01-04 14:35:47'),
('Sac étudiante', 37, 'article_695a6d32e78f5', 'sac-etudiante', 15, 'Magnifique sac pour étudiante', 1, 0, NULL, 1, '2026-01-04 14:37:54'),
('Mini sac de soirée', 38, 'article_695a6d9b77335', 'mini-sac-de-soir-ee', 23, 'Magnifique sac de soirée', 1, 0, NULL, 1, '2026-01-04 14:39:39'),
('Sac rebelle', 39, 'article_695a6df43d214', 'sac-rebelle', 22, 'Sac rebelle idéal pour les sortis en boîte', 1, 0, NULL, 1, '2026-01-04 14:41:08'),
('Mini sac crocos', 40, 'article_695a6e4c18520', 'mini-sac-crocos', 30, 'Mini sac crocos', 1, 0, NULL, 1, '2026-01-04 14:42:36'),
('Sac coraille', 41, 'article_695a6ee92bef8', 'sac-coraille', 26, 'Magnifique sac des meres', 1, 0, NULL, 1, '2026-01-04 14:45:13'),
('Sac corde rouge', 42, 'article_695a6f2fe022a', 'sac-corde-rouge', 36, 'Magnifique sac cordes', 1, 0, NULL, 1, '2026-01-04 14:46:23'),
('Sac jaguar', 43, 'article_695a6facaee73', 'sac-jaguar', 50, 'Sac jaguar pour vos soirées de luxe', 1, 0, NULL, 1, '2026-01-04 14:48:28'),
('Lunettes Tom Ford', 56, 'article_695aa93a62fbf', 'lunettes-tom-ford', 12, 'ok', 1, 0, NULL, 2, '2026-01-04 18:54:02'),
('Lunette de soleil  LOEWE', 57, 'article_695ac805e85f2', 'lunette-de-soleil-loewe', 11, 'Lunettes de soleil LOEWE', 1, 0, NULL, 2, '2026-01-04 21:05:25'),
('Lunette ALL BLACK LOEWE', 58, 'article_695ac85904657', 'lunette-all-black-loewe', 15, 'Lunette ALL BLACK LOEWE', 1, 0, NULL, 2, '2026-01-04 21:06:49'),
('Lunettes rose view', 59, 'article_695ac90a72d38', 'lunettes-rose-view', 16, 'Belle lunettes noirs LOEWE rose view', 1, 0, NULL, 2, '2026-01-04 21:09:46'),
('Lunette de plage blanche', 60, 'article_695ac967a29d7', 'lunette-de-plage-blanche', 20, '', 1, 0, NULL, 2, '2026-01-04 21:11:19'),
('Duo Montarde et Dalmacien', 61, 'article_695ac9b147231', 'duo-montarde-et-dalmacien', 40, 'Ces lunettes ne se vendent qu\'à deux', 1, 0, NULL, 2, '2026-01-04 21:12:33'),
('Lunette metal dorée', 62, 'article_695ac9fad5142', 'lunette-metal-dor-ee', 25, 'La vue de l\'océan', 1, 0, NULL, 2, '2026-01-04 21:13:46'),
('Lunette hibou', 63, 'article_695aca3665563', 'lunette-hibou', 10, 'Magnifique', 1, 0, NULL, 2, '2026-01-04 21:14:46'),
('Talons noir à pointe rouge', 64, 'article_695bce688f9b6', 'talons-noir-a-pointe-rouge', 35, 'Magnifique chaussure à la pointe de sang pour vos soirées', 1, 0, NULL, 3, '2026-01-05 15:44:56'),
('Talon brun à pointe argenté', 65, 'article_695c8b58300f3', 'talon-brun-a-pointe-argent-e', 20, 'Magnifique Talon brun à pointe argenté', 1, 0, NULL, 3, '2026-01-06 05:11:04'),
('Talon bleu Alala', 66, 'article_695c8b9ae994f', 'talon-bleu-alala', 28, 'Magnifique Talon bleu Alala', 1, 0, NULL, 3, '2026-01-06 05:12:10'),
('Talon pleine rouge bordeau', 67, 'article_695c8bec73d3d', 'talon-pleine-rouge-bordeau', 29, 'Magnifique Talon pleine rouge bordeau', 1, 0, NULL, 3, '2026-01-06 05:13:32'),
('Talon jumelle de Jeffrey Campbell', 68, 'article_695c8c322c8fd', 'talon-jumelle-de-jeffrey-campbell', 26, 'Magnifique Talon jumelle de Jeffrey Campbell', 1, 0, NULL, 3, '2026-01-06 05:14:42'),
('Talon vert croco', 69, 'article_695c8cab50045', 'talon-vert-croco', 25, 'Magnifique Talon vert croco', 1, 0, NULL, 3, '2026-01-06 05:16:43'),
('Talon de Gala dorée', 70, 'article_695c8cfbf0f32', 'talon-de-gala-dor-ee', 27, 'Magnifique Talon de Gala dorée', 1, 0, NULL, 3, '2026-01-06 05:18:03'),
('Talon à semelle compensée', 71, 'article_695c8e770f569', 'talon-a-semelle-compens-ee', 24, 'Magnifique Talon à semelle compensée', 1, 0, NULL, 3, '2026-01-06 05:24:23'),
('Talon fleurie jaune doré', 72, 'article_695c8ec90736b', 'talon-fleurie-jaune-dor-e', 20, 'Magnifuque Talon fleurie jaune doré', 1, 0, NULL, 3, '2026-01-06 05:25:45'),
('Talon Dara noir doré', 73, 'article_695c8effd4c4e', 'talon-dara-noir-dor-e', 23, 'Magnifique Talon Dara noir doré', 1, 0, NULL, 3, '2026-01-06 05:26:39'),
('Talon vert sombre Saint Laurent', 74, 'article_695c8f3bf1964', 'talon-vert-sombre-saint-laurent', 39, 'Magnifique Talon vert sombre Saint Laurent', 1, 0, NULL, 3, '2026-01-06 05:27:39'),
('Talon boisé', 75, 'article_695c8f874afda', 'talon-bois-e', 50, 'Magnifique Talon boisé', 1, 0, NULL, 3, '2026-01-06 05:28:55'),
('Talon coeur d\'afrique', 76, 'article_695c8fb4c8b05', 'talon-coeur-d-afrique', 26, 'Magnifique Talon coeur d\'afrique', 1, 0, NULL, 3, '2026-01-06 05:29:40'),
('Talon grande dame rouge bordeau', 77, 'article_695c9017275d2', 'talon-grande-dame-rouge-bordeau', 36, 'Magnifique Talon grande dame rouge bordeau', 1, 0, NULL, 3, '2026-01-06 05:31:19'),
('Haut corset gris', 78, 'article_695ea789b49a5', 'haut-corset-gris', 35, 'Magnifique haut corset gris', 1, 0, NULL, 4, '2026-01-07 19:35:53'),
('Longue robe rouge décolté plongeant', 79, 'article_695ea83219679', 'longue-robe-rouge-d-ecolt-e-plongeant', 120, 'Magnifique longue robe rouge décolté plongeant', 1, 0, NULL, 4, '2026-01-07 19:38:42'),
('Robe orange à manche longue', 80, 'article_695ea91cb39cc', 'robe-orange-a-manche-longue', 40, 'Magnifique robe orange à manche longue', 1, 0, NULL, 4, '2026-01-07 19:42:36'),
('Mini robe noir de soirée', 81, 'article_695ea981636dc', 'mini-robe-noir-de-soir-ee', 50, 'Magnifique mini robe noir de soirée', 1, 0, NULL, 4, '2026-01-07 19:44:17'),
('Robe élegante vest grise', 82, 'article_695eaa22e7c69', 'robe-elegante-vest-grise', 109, 'Parfait pour vos soirée professionnel', 1, 0, NULL, 4, '2026-01-07 19:46:58'),
('Robe fleure roge', 83, 'article_695eaa5e1ee48', 'robe-fleure-roge', 79, 'Parfaite pour vos journée en famille', 1, 0, NULL, 4, '2026-01-07 19:47:58'),
('Mini robe de soirée coeur rouge', 84, 'article_695eaa919591a', 'mini-robe-de-soir-ee-coeur-rouge', 100, 'Une robe qui vous ressemble', 1, 0, NULL, 4, '2026-01-07 19:48:49'),
('Robe noire Hasse de pique', 85, 'article_695eab01acd2a', 'robe-noire-hasse-de-pique', 90, 'Une robe d\'une femme qui s\'aime', 1, 0, NULL, 4, '2026-01-07 19:50:41'),
('Robe crème jaune', 86, 'article_695eab29e1faa', 'robe-cr-eme-jaune', 80, '', 1, 0, NULL, 4, '2026-01-07 19:51:21'),
('Robe de gala coeur bleu', 87, 'article_695eac033726e', 'robe-de-gala-coeur-bleu', 124, 'Sentez vous bien en soirée', 1, 0, NULL, 4, '2026-01-07 19:54:59'),
('Robe noir aux roses blanches', 88, 'article_695eac520c590', 'robe-noir-aux-roses-blanches', 70, 'Magnifique Robe noir aux roses blanches', 1, 0, NULL, 4, '2026-01-07 19:56:18'),
('Robe bleu évasée', 89, 'article_695eacf84bcfe', 'robe-bleu-evas-ee', 140, 'Une robe qui aime votre corps', 1, 0, NULL, 4, '2026-01-07 19:59:04'),
('Robe fleurie', 90, 'article_695ead5d0967b', 'robe-fleurie', 50, 'Magnifique Robe fleurie\nIdéal pour vos barbecues', 1, 0, NULL, 4, '2026-01-07 20:00:45'),
('Robe dame nature', 91, 'article_695eae1577c32', 'robe-dame-nature', 125, 'Communiez avec la nature', 1, 0, NULL, 4, '2026-01-07 20:03:49'),
('Robe libellule', 92, 'article_695eae5d47d50', 'robe-libellule', 45, 'Vole comme un papillon', 1, 0, NULL, 4, '2026-01-07 20:05:01'),
('Robe rose jaune', 93, 'article_695eae9315c28', 'robe-rose-jaune', 190, 'Vous êtes la fleure que vous pensez que vous êtes', 1, 0, NULL, 4, '2026-01-07 20:05:55'),
('Ensemble en jeans', 94, 'article_695eb0bb21d9a', 'ensemble-en-jeans', 150, 'Magnifique complet en jeans', 1, 0, NULL, 4, '2026-01-07 20:15:07'),
('Mini robe arc-en-ciel', 95, 'article_695eb0e620c71', 'mini-robe-arc-en-ciel', 60, 'Mini robe arc-en-ciel', 1, 0, NULL, 4, '2026-01-07 20:15:50'),
('Duo vert et bleu', 96, 'article_695eb137e3933', 'duo-vert-et-bleu', 250, 'Sortez avec votre meilleure amie', 1, 0, NULL, 4, '2026-01-07 20:17:11'),
('Robe rouge femme africaine', 97, 'article_695eb1645fb61', 'robe-rouge-femme-africaine', 130, 'Magnifique Robe rouge femme africaine', 1, 0, NULL, 4, '2026-01-07 20:17:56'),
('Robe fleure rose', 98, 'article_695eb195c9cf0', 'robe-fleure-rose', 140, 'Une magnifique rose ce soir', 1, 0, NULL, 4, '2026-01-07 20:18:45'),
('Une danse dorée', 99, 'article_695eb1dfa9f72', 'une-danse-dor-ee', 80, 'Une danse dorée', 1, 0, NULL, 4, '2026-01-07 20:19:59'),
('Robe de soirée dorée caramel', 100, 'article_695eb2da75e81', 'robe-de-soir-ee-dor-ee-caramel', 180, 'L\'or a été caraméliser', 1, 0, NULL, 4, '2026-01-07 20:24:10'),
('Robe feuille de fleure', 101, 'article_695edb66d96fc', 'robe-feuille-de-fleure', 36, '', 1, 0, NULL, 5, '2026-01-07 23:17:10'),
('Boubou couleur Metal', 102, 'article_695edc7584045', 'boubou-couleur-metal', 45, '', 1, 0, NULL, 5, '2026-01-07 23:21:41'),
('Robe rose fleurie', 103, 'article_695edca01a4a4', 'robe-rose-fleurie', 20, '', 1, 0, NULL, 5, '2026-01-07 23:22:24'),
('Robe violette', 104, 'article_695edccc9aaf2', 'robe-violette', 30, '', 1, 0, NULL, 5, '2026-01-07 23:23:08'),
('Robe orange neud papillon', 105, 'article_695edcf74cfc2', 'robe-orange-neud-papillon', 20, '', 1, 0, NULL, 5, '2026-01-07 23:23:51'),
('Robe nouvelle plante verte', 106, 'article_695edd19eb06c', 'robe-nouvelle-plante-verte', 30, '', 1, 0, NULL, 5, '2026-01-07 23:24:25'),
('Robe rouge sang', 107, 'article_695edd4f3e77e', 'robe-rouge-sang', 80, '', 1, 0, NULL, 5, '2026-01-07 23:25:19'),
('Robe en pagne dorée', 108, 'article_695edd7f7dc7b', 'robe-en-pagne-dor-ee', 60, '', 1, 0, NULL, 5, '2026-01-07 23:26:07'),
('Boubou grande dame vert sombre', 109, 'article_695eddae49148', 'boubou-grande-dame-vert-sombre', 50, '', 1, 0, NULL, 5, '2026-01-07 23:26:54'),
('Boubou caméléon', 110, 'article_695edddf954df', 'boubou-cam-el-eon', 60, '', 1, 0, NULL, 5, '2026-01-07 23:27:43'),
('Robe violette grande dame', 111, 'article_695ede213e2c4', 'robe-violette-grande-dame', 70, '', 1, 0, NULL, 5, '2026-01-07 23:28:49'),
('Boubou beige', 112, 'article_695ede5d81fa9', 'boubou-beige', 40, '', 1, 0, NULL, 5, '2026-01-07 23:29:49'),
('Boubou Jaune dorée', 113, 'article_695edeaed4174', 'boubou-jaune-dor-ee', 90, '', 1, 0, NULL, 5, '2026-01-07 23:31:10'),
('Robe jupe léopard', 114, 'article_695eded51308d', 'robe-jupe-l-eopard', 65, '', 1, 0, NULL, 5, '2026-01-07 23:31:49'),
('Robe beige', 115, 'article_695edf629ba7a', 'robe-beige', 40, '', 1, 0, NULL, 5, '2026-01-07 23:34:10'),
('Boucle d\'oreille soleil', 116, 'article_695ee047d29f6', 'boucle-d-oreille-soleil', 12, '', 1, 0, NULL, 6, '2026-01-07 23:37:59'),
('Ensemble colliers, boucles d\'oreilles, bagues', 117, 'article_695ee27f68982', 'ensemble-coliers-boucles-d-oreilles-bagues', 50, '', 1, 0, NULL, 6, '2026-01-07 23:47:27'),
('Bracelets motife marbre brun', 118, 'article_695ee2c63368d', 'braceles-motife-marbre-brun', 15, '', 1, 0, NULL, 6, '2026-01-07 23:48:38'),
('Bracelets dorée', 119, 'article_695ee32de93ad', 'braceles-dor-ee', 12, '', 1, 0, NULL, 6, '2026-01-07 23:50:21'),
('Bagues perles argentée', 120, 'article_695ee37b7011a', 'bagues-perles-argent-ee', 20, '', 1, 0, NULL, 6, '2026-01-07 23:51:39'),
('Bracelets motif marbre noir', 121, 'article_695ee470c4584', 'bracelets-motif-marbre-noir', 25, '', 1, 0, NULL, 6, '2026-01-07 23:55:44'),
('Complet bracelet marbre beige et vert', 122, 'article_695ee5332ca61', 'complet-bracelet-marbre-beige-et-vert', 30, '', 1, 0, NULL, 6, '2026-01-07 23:58:59'),
('Boucles d\'oreilles see me', 123, 'article_695ee5617c472', 'boucles-d-oreilles-see-me', 15, '', 1, 0, NULL, 6, '2026-01-07 23:59:45'),
('Colliers en perle grande dame', 124, 'article_695ee5b54af3e', 'colliers-en-perle-grande-dame', 20, '', 1, 0, NULL, 6, '2026-01-08 00:01:09'),
('Bracelet diadème en perles', 125, 'article_695ee6353fa03', 'bracelet-diad-eme-en-perles', 23, '', 1, 0, NULL, 6, '2026-01-08 00:03:17'),
('Trois bracelets dorée', 126, 'article_695ee69ce5624', 'trois-bracelets-dor-ee', 30, '', 1, 0, NULL, 6, '2026-01-08 00:05:00'),
('Coiffe de perle', 127, 'article_695ee72878164', 'coiffe-de-perle', 42, '', 1, 0, NULL, 6, '2026-01-08 00:07:20'),
('Bracelet motif marbre blanc assorti à ses bagues', 128, 'article_695ee774f07f4', 'bracelet-motif-marbre-blanc-assorti-a-ses-bagues', 60, '', 1, 0, NULL, 6, '2026-01-08 00:08:36'),
('Bracelets élégante dame', 129, 'article_695ee79e15305', 'bracelets-el-egante-dame', 40, '', 1, 0, NULL, 6, '2026-01-08 00:09:18'),
('coiffe de perle Queen africa', 130, 'article_695ee7d19c471', 'coiffe-de-perle-queen-africa', 25, '', 1, 0, NULL, 6, '2026-01-08 00:10:09'),
('Complet bracelets et bagues argentée', 131, 'article_695ee810e4537', 'complet-bracelets-et-bagues-argent-ee', 35, '', 1, 0, NULL, 6, '2026-01-08 00:11:12'),
('Parure collier et boucles d’oreilles doré', 132, 'article_695ee852c4480', 'parure-collier-et-boucles-d-oreilles-dor-e', 45, '', 1, 0, NULL, 6, '2026-01-08 00:12:18'),
('Bracelets motif marbre blanc', 133, 'article_695ee88aec715', 'bracelets-motif-marbre-blanc', 20, '', 1, 0, NULL, 6, '2026-01-08 00:13:14'),
('Casquettes Boston (6 couleurs disponible)', 134, 'article_696063ca02419', 'casquettes-boston-6-couleurs-disponible', 12, '', 1, 0, NULL, 7, '2026-01-09 03:11:22'),
('Bandana zèbre', 135, 'article_6960645284b4f', 'bandana-z-ebre', 5, '', 1, 0, NULL, 7, '2026-01-09 03:13:38'),
('Patek Philipe dorée', 136, 'article_696064cb9355a', 'patek-philipe-dor-ee', 20, '', 1, 0, NULL, 7, '2026-01-09 03:15:39'),
('Montres Casio 3 couleurs disponibles', 137, 'article_6960654a94d5f', 'montres-casio-3-couleurs-disponibles', 15, '', 1, 0, NULL, 7, '2026-01-09 03:17:46'),
('Chaussures noir \"sir\"', 138, 'article_696065dd86369', 'chaussures-noir-sir', 40, '', 1, 0, NULL, 7, '2026-01-09 03:20:13'),
('Chaussure Miu Miu Brun', 139, 'article_6960661638f25', 'chaussure-miu-miu-brun', 35, '', 1, 0, NULL, 7, '2026-01-09 03:21:10'),
('Chaussure Grenson couleur gazon', 140, 'article_696066523e775', 'chaussure-grenson-couleur-gazon', 45, '', 1, 0, NULL, 7, '2026-01-09 03:22:10'),
('Chausson noir cuire', 141, 'article_69606a39de5c4', 'chausson-noir-cuire', 20, '', 1, 0, NULL, 7, '2026-01-09 03:38:49'),
('Montre Casio Full argenté', 142, 'article_69606a6d74007', 'montre-casio-full-argent-e', 12, '', 1, 0, NULL, 7, '2026-01-09 03:39:41'),
('Chausure prada', 143, 'article_69606a9053e26', 'chausure-prada', 30, '', 1, 0, NULL, 7, '2026-01-09 03:40:16'),
('Montre Casio argenté coeur du gazon', 144, 'article_69606ac2b2525', 'montre-casio-argent-e-coeur-du-gazon', 12, '', 1, 0, NULL, 7, '2026-01-09 03:41:06'),
('Chaussure homme brun foncé', 145, 'article_69606afdce0a5', 'chaussure-homme-brun-fonc-e', 50, '', 1, 0, NULL, 7, '2026-01-09 03:42:05'),
('Montre Casio coeur noir', 146, 'article_69606b21bef32', 'montre-casio-coeur-noir', 12, '', 1, 0, NULL, 7, '2026-01-09 03:42:41'),
('Chaussure Saint Laurent imprimé marbre', 147, 'article_69606b5887698', 'chaussure-saint-laurent-imprim-e-marbre', 50, '', 1, 0, NULL, 7, '2026-01-09 03:43:36'),
('Chaussures Jacques Solonière', 148, 'article_69606b94615fe', 'chaussures-jacques-soloni-ere', 40, '', 1, 0, NULL, 7, '2026-01-09 03:44:36'),
('Chaussure CHARLES & KEITH brun', 149, 'article_69606beb19de9', 'chaussure-charles-keith-brun', 45, '', 1, 0, NULL, 7, '2026-01-09 03:46:03'),
('Zara Chaussure Brun', 150, 'article_69606c2317650', 'zara-chaussure-brun', 45, '', 1, 0, NULL, 7, '2026-01-09 03:46:59'),
('BOB mongolia', 151, 'article_69606c45ab2f2', 'bob-mongolia', 15, '', 1, 0, NULL, 7, '2026-01-09 03:47:33'),
('Sac pour homme (2 couleurs disponibles)', 152, 'article_69606dd5d6a04', 'sac-pour-homme-2-couleurs-disponibles', 20, '', 1, 0, NULL, 7, '2026-01-09 03:54:13'),
('Montre Casio coeur rouge', 153, 'article_69606ee6bf2dc', 'montre-casio-coeur-rouge', 12, '', 1, 0, NULL, 7, '2026-01-09 03:58:46'),
('Casquette foulard', 154, 'article_69606f59300de', 'casquette-foulard', 10, '', 1, 0, NULL, 7, '2026-01-09 04:00:41'),
('Perruque châtain', 155, 'article_6960761d84969', 'perruque-ch-atain', 120, '', 1, 0, NULL, 8, '2026-01-09 04:29:33'),
('Longue perruque châtain', 156, 'article_696076460132e', 'longue-perruque-ch-atain', 130, '', 1, 0, NULL, 8, '2026-01-09 04:30:14'),
('Mallia perruque noir à frange', 157, 'article_696076a24be8b', 'mallia-perruque-noir-a-frange', 140, '', 1, 0, NULL, 8, '2026-01-09 04:31:46'),
('Perruque noir à frange gauche', 158, 'article_696076dfc8c49', 'perruque-noir-a-frange-gauche', 110, '', 1, 0, NULL, 8, '2026-01-09 04:32:47'),
('Modèle Rihanna longue perruque noir', 159, 'article_6960786ebfe18', 'mod-ele-rihanna-longue-perruque-noir', 125, '', 1, 0, NULL, 8, '2026-01-09 04:39:26'),
('Perruque courte blonde', 160, 'article_696078a07d421', 'perruque-courte-blonde', 56, '', 1, 0, NULL, 8, '2026-01-09 04:40:16'),
('Perruque blonde à frange droite', 161, 'article_696078ce8b824', 'perruque-blonde-a-frange-droite', 120, '', 1, 0, NULL, 8, '2026-01-09 04:41:02'),
('Perruque argentée', 162, 'article_696078f4953bd', 'perruque-argent-ee', 110, '', 1, 0, NULL, 8, '2026-01-09 04:41:40'),
('Perruques noires', 163, 'article_69607b46defc1', 'perruques-noires', 80, '', 1, 0, NULL, 8, '2026-01-09 04:51:34'),
('Perruques badies', 164, 'article_69607b6d78f85', 'perruques-badies', 70, '', 1, 0, NULL, 8, '2026-01-09 04:52:13'),
('Robe noir haut argenté', 165, 'article_69607d08ce1fe', 'robe-noir-haut-argent-e', 15, '', 1, 0, NULL, 9, '2026-01-09 04:59:04'),
('Robe rose grande dame', 166, 'article_69607d3590f5b', 'robe-rose-grande-dame', 15, '', 1, 0, NULL, 9, '2026-01-09 04:59:49'),
('Robe noire duchesse', 167, 'article_69607d887d63a', 'robe-noire-duchesse', 20, '', 1, 0, NULL, 9, '2026-01-09 05:01:12'),
('Robe de galla Verte mure', 168, 'article_69607e322c875', 'robe-de-galla-verte-mure', 20, '', 1, 0, NULL, 9, '2026-01-09 05:04:02'),
('Robe verte argentée', 169, 'article_69607e71924fb', 'robe-verte-argent-ee', 15, '', 1, 0, NULL, 9, '2026-01-09 05:05:05'),
('Robe décolleté argentée', 170, 'article_69607ed0d96e9', 'robe-d-ecollet-e-argent-ee', 25, 'Tu ne passes jamais inaperçu en soirée', 1, 0, NULL, 9, '2026-01-09 05:06:40'),
('Robe moulante rouge', 171, 'article_69607f1714543', 'robe-moulante-rouge', 20, '', 1, 0, NULL, 9, '2026-01-09 05:07:51'),
('Robe de soirée violette', 172, 'article_6960817d01b9a', 'robe-de-soir-ee-violette', 40, '', 1, 0, NULL, 10, '2026-01-09 05:18:05'),
('Complet cowgril', 173, 'article_696081c1a7aea', 'complet-cowgril', 50, '', 1, 0, NULL, 10, '2026-01-09 05:19:13'),
('Corset love your body (deux couleurs disponibles)', 174, 'article_696081fbe74b6', 'corset-love-your-body-deux-couleurs-disponibles', 35, '', 1, 0, NULL, 10, '2026-01-09 05:20:11'),
('Robe jaune moutarde', 175, 'article_69608262e17d3', 'robe-jaune-moutarde', 60, '', 1, 0, NULL, 10, '2026-01-09 05:21:54'),
('Robe Moulante violette', 176, 'article_6960831b7638f', 'robe-moulante-violette', 55, '', 1, 0, NULL, 10, '2026-01-09 05:24:59'),
('Robe orange décolleté', 177, 'article_6960834e4f673', 'robe-orange-d-ecollet-e', 80, '', 1, 0, NULL, 10, '2026-01-09 05:25:50'),
('Robe verte à fente', 178, 'article_696083b785e2b', 'robe-verte-a-fente', 40, '', 1, 0, NULL, 10, '2026-01-09 05:27:35'),
('Skincare by LAROCHE-POSAY', 179, 'article_69614b27a3b70', 'skincare-by-laroche-posay', 150, '', 1, 0, NULL, 11, '2026-01-09 19:38:31'),
('Soins du visage by Vichy laboratoire', 180, 'article_69614b699f515', 'soins-du-visage-by-vichy-laboratoire', 25, '', 1, 0, NULL, 11, '2026-01-09 19:39:37'),
('Brosses pour le corps', 181, 'article_69614c1677861', 'brosses-pour-le-corps', 51, '', 1, 0, NULL, 11, '2026-01-09 19:42:30'),
('Brosses pour le H & S', 182, 'article_69614c502c7c2', 'brosses-pour-le-h-s', 30, '', 1, 0, NULL, 11, '2026-01-09 19:43:28'),
('Serum pour le visage LA ROCHE-POSAY', 183, 'article_69614cb7e0a72', 'serum-pour-le-visage-la-roche-posay', 50, '', 1, 0, NULL, 11, '2026-01-09 19:45:11'),
('Demaquillant Medicube', 184, 'article_69614ce94c511', 'demaquillant-medicube', 20, '', 1, 0, NULL, 11, '2026-01-09 19:46:01'),
('Visage care', 185, 'article_69614d455e908', 'visage-care', 35, '', 1, 0, NULL, 11, '2026-01-09 19:47:33'),
('Brosse à dents & dentifrice blanchissant', 186, 'article_69614ebaa0029', 'brosse-a-dents-dentifrice-blanchissant', 15, '', 1, 0, NULL, 11, '2026-01-09 19:53:46'),
('Soins du visage MELA B3', 187, 'article_696151f1de5e4', 'soins-du-visage-mela-b3', 15, '', 1, 0, NULL, 11, '2026-01-09 20:07:29'),
('Crème medicube', 188, 'article_696152209ef13', 'cr-eme-medicube', 56, '', 1, 0, NULL, 11, '2026-01-09 20:08:16'),
('Serum renitol intense', 189, 'article_696152592714b', 'serum-renitol-intense', 25, '', 1, 0, NULL, 11, '2026-01-09 20:09:13'),
('Fond de teint HudaBeauty', 190, 'article_6961532729af7', 'fond-de-teint-hudabeauty', 50, '', 1, 0, NULL, 12, '2026-01-09 20:12:39'),
('Eyeliner HudaBeauty', 191, 'article_696153659ed11', 'eyeliner-hudabeauty', 5, '', 1, 0, NULL, 12, '2026-01-09 20:13:41'),
('Fond de teint NYX', 192, 'article_69615390ba572', 'fond-de-teint-nyx', 25, '', 1, 0, NULL, 12, '2026-01-09 20:14:24'),
('Fond de teint Elf (10 couleurs disponible)', 193, 'article_696153c4cb5aa', 'fond-de-teint-elf-10-couleurs-disponible', 12, '', 1, 0, NULL, 12, '2026-01-09 20:15:16'),
('Fond BARE WHITE ME by NIX', 194, 'article_696153eb44b2e', 'fond-bare-white-me-by-nix', 15, '', 1, 0, NULL, 12, '2026-01-09 20:15:55'),
('Fond de teint by NARS', 195, 'article_69615472bfa14', 'fond-de-teint-by-nars', 20, '', 1, 0, NULL, 12, '2026-01-09 20:18:10'),
('belle go', 196, 'article_69e6735776048', 'belle-go', 120, 'J\'aime que je sois belle avec cette tenue', 1, 0, NULL, 9, '2026-04-20 19:41:27'),
('beni', 197, 'article_69e67b3d5f422', 'beni', 1000, '', 1, 1, 50, 9, '2026-04-20 20:15:09'),
('Quaerat dignissimos', 198, 'article_69e67b6040b76', 'quaerat-dignissimos', 6, 'Ad dolore voluptatib', 1, 0, 0, 9, '2026-04-20 20:15:44'),
('Quaerat dignissimos', 199, 'article_69e67b6b36c1c', 'quaerat-dignissimos-1', 6, 'Ad dolore voluptatib', 1, 0, NULL, 9, '2026-04-20 20:15:55');

-- --------------------------------------------------------

--
-- Structure de la table `article_likes`
--

DROP TABLE IF EXISTS `article_likes`;
CREATE TABLE IF NOT EXISTS `article_likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `account_id` int NOT NULL,
  `account_type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article_likes`
--

INSERT INTO `article_likes` (`id`, `article_id`, `account_id`, `account_type`, `date_ajout`) VALUES
(1, 186, 1, 'admin', '2026-04-12 21:36:05');

-- --------------------------------------------------------

--
-- Structure de la table `bienvenue_email`
--

DROP TABLE IF EXISTS `bienvenue_email`;
CREATE TABLE IF NOT EXISTS `bienvenue_email` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_unique_id` text,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `bienvenue_email`
--

INSERT INTO `bienvenue_email` (`id`, `client_unique_id`, `date_ajout`) VALUES
(1, 'user_69905578cb08c8.80774205', '2026-02-14 12:14:21'),
(2, 'user_6992240d4e5902.57722582', '2026-02-15 20:59:15'),
(3, 'user_69977a0abf5c02.91140663', '2026-02-19 22:11:23'),
(4, 'user_69ad48b37eb757.40001033', '2026-03-08 11:00:23'),
(5, 'user_69ad4e59f08873.89164713', '2026-03-08 11:24:30'),
(6, 'user_69db7adc9ee7e9.15116718', '2026-04-12 12:02:44'),
(7, 'store_69e671c0d82d15.89018025', '2026-04-20 19:35:10');

-- --------------------------------------------------------

--
-- Structure de la table `boutiques`
--

DROP TABLE IF EXISTS `boutiques`;
CREATE TABLE IF NOT EXISTS `boutiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unique_id` text COLLATE utf8mb4_general_ci,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `adresse_email` text COLLATE utf8mb4_general_ci,
  `mdp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code_password` text COLLATE utf8mb4_general_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` text COLLATE utf8mb4_general_ci,
  `profile` text COLLATE utf8mb4_general_ci,
  `fileId` text COLLATE utf8mb4_general_ci,
  `tiktok` text COLLATE utf8mb4_general_ci,
  `whatsapp` text COLLATE utf8mb4_general_ci,
  `telephone_whatsapp` text COLLATE utf8mb4_general_ci,
  `instagram` text COLLATE utf8mb4_general_ci,
  `trends` text COLLATE utf8mb4_general_ci,
  `twitter` text COLLATE utf8mb4_general_ci,
  `facebook` text COLLATE utf8mb4_general_ci,
  `backgrounds` text COLLATE utf8mb4_general_ci,
  `activer` int NOT NULL,
  `date_activation_debut` date DEFAULT NULL,
  `date_activation_fin` date DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `boutiques`
--

INSERT INTO `boutiques` (`id`, `unique_id`, `nom`, `adresse_email`, `mdp`, `code_password`, `description`, `slug`, `profile`, `fileId`, `tiktok`, `whatsapp`, `telephone_whatsapp`, `instagram`, `trends`, `twitter`, `facebook`, `backgrounds`, `activer`, `date_activation_debut`, `date_activation_fin`, `date_ajout`) VALUES
(1, NULL, 'Bk sacs', 'test@test.com', NULL, NULL, 'Une boutique vintage de sac, une fouineuse amoureuse des sacs en friperie boutique 2025', 'bk-sacs', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-12', '2026-05-12', '2025-12-18 20:28:28'),
(2, NULL, 'Luxe lunetti', NULL, NULL, NULL, 'Luxe lunetti est une boutique en ligne de précommande de lunettes de luxe depuis 2023, au menu des marques comme loewe, ferragano, Tom Ford, etc...', 'luxe-lunetti', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-04 15:01:25'),
(3, NULL, 'Chaussure Store', NULL, NULL, NULL, 'Est une boutique pour chaussure vintage Paris - Kinshasa depuis 2020, si vous aimez des pièces iconiques vous êtes au bons endroits.', 'chaussure-store', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-05 13:54:27'),
(4, NULL, 'Fashion exo kin', NULL, NULL, NULL, 'Nous vendons en ligne les articles fashion nova, shein, maxi dress, ohpolly', 'fashion-exo-kin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-07 19:18:39'),
(5, NULL, 'Boubou Kin', NULL, NULL, NULL, NULL, 'boubou-kin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-07 23:13:02'),
(6, NULL, 'Accessoire de Sarah', NULL, NULL, NULL, NULL, 'accessoire-de-sarah', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-07 23:35:58'),
(7, NULL, 'Hommes de goûts', NULL, NULL, NULL, 'Boutique homme articles France et Turquie', 'hommes-de-go-uts', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-09 02:57:22'),
(8, NULL, 'Perruque era', NULL, NULL, NULL, 'Perruque actuellement disponible en boutique', 'perruque-era', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-09 04:02:29'),
(9, 'store_69e671c0d82d15.89018025', 'Barbody friperie', 'Naomiegracekatunga@gmail.com', '$2y$12$1269M6JHnze2XWwqX1yDLejDg8vZP44NVXF4gzQdM7PRjGCLxCzsS', NULL, 'Barbody est une boutique de friperie à kinshasa, since 2023', 'barbody-friperie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-09 04:53:59'),
(10, NULL, 'Kermesse shopping', NULL, NULL, NULL, NULL, 'kermesse-shopping', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-09 05:14:50'),
(11, NULL, 'Kinscare', NULL, NULL, NULL, 'Bienvenue dans la skincare', 'kinscare', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-01-09 19:35:17'),
(12, NULL, 'Mariam beauté', NULL, NULL, NULL, 'Fond de teint', 'mariam-beaut-e', 'https://ik.imagekit.io/nyombi1997/OhNous/profile/mariam-beaut-e_1776838735763_CUVOFuSxb.webp', '69e868515c7cd75eb882eb3a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rgb(161, 136, 120)', 1, NULL, NULL, '2026-01-09 20:11:34'),
(19, 'user_698c06d429bd79.14557670', 'In nihil dolorum cum', 'zosylo@mailinator.com', '$2y$12$E44UGw7wY4jrKnZBoWJvrO9KaZmnAPRUbHAutVXXR6f.LOQ8IsHUO', NULL, NULL, 'in-nihil-dolorum-cum', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-02-11 05:34:28'),
(20, 'user_698c06eb72d017.65720452', 'Aut voluptate at ani', 'todages@mailinator.com', '$2y$12$qxEESgdEOhUKvnD59q.PIOXTsy0pBWrhe/aEdBgpT8aMOJApnB2yK', NULL, NULL, 'aut-voluptate-at-ani', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-02-11 05:34:51'),
(21, 'user_698ce771695ab9.46834543', 'Error mollit amet i', 'samina@mailinator.com', '$2y$12$QIZD7aKqZORG1upnki54/e1w36qgTBv/IQjEtRPOKnEoClwqftwVe', NULL, NULL, 'error-mollit-amet-i', 'https://ik.imagekit.io/nyombi1997/OhNous/profile/error-mollit-amet-i_1770850382222_wfo6swjGm.webp', '698d084f5c7cd75eb85f3918', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rgb(240, 231, 216)', 0, NULL, NULL, '2026-02-11 21:32:49'),
(23, 'user_69905578cb08c8.80774205', 'Sunt ea rerum volup', 'nyombi126@gmail.com', '$2y$12$vMtsC4dh13SLvcvEZKEVKOC8hqHkEkl3AGoKWbE5DLzDP9aZg/60W', '$2y$12$D4xL0Wi4ITFYmIZTd/QQVuWu7LwZ4h3S12LQa5qTaKrHZ9egnqeUW', NULL, 'sunt-ea-rerum-volup', 'https://ik.imagekit.io/nyombi1997/OhNous/profile/sunt-ea-rerum-volup_1776530228861_Lnt0eog6N.webp', '69e3b3375c7cd75eb8860486', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rgb(240, 235, 238)', 0, NULL, NULL, '2026-02-14 11:59:05'),
(24, 'user_6992240d4e5902.57722582', 'Numquam est anim qua', 'zagkinshasa@gmail.com', '$2y$12$QGtE624.GTK.3AON.zbese5/3tZEjXQfMKcZHegeDOQyYzE343jJW', NULL, 'C\'est une boutique parfaite !', 'numquam-est-anim-qua', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-02-15 20:52:45'),
(25, 'user_69ad4e59f08873.89164713', 'Nostrum facere elige', 'lepeveg@mailinator.com', '$2y$12$V/w5j.dNdmyG8X4RZ3vLb.zPyfPOgbCFa61NhAW3rLu9E9rcGh/SS', NULL, NULL, 'nostrum-facere-elige', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-03-08 11:24:26');

-- --------------------------------------------------------

--
-- Structure de la table `boutique_activation_requests`
--

DROP TABLE IF EXISTS `boutique_activation_requests`;
CREATE TABLE IF NOT EXISTS `boutique_activation_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `boutique_id` int NOT NULL,
  `token` text COLLATE utf8mb4_general_ci,
  `statut` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente',
  `duree_jours` int NOT NULL DEFAULT '0',
  `date_traitement` datetime DEFAULT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(9, 'Parfums', 'parfums', '2025-12-14 06:41:24'),
(10, 'Soins dentaire', 'soins-dentaire', '2026-01-09 19:49:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(109, 133, 2, '2026-01-08 00:13:14'),
(110, 134, 1, '2026-01-09 03:11:22'),
(111, 135, 1, '2026-01-09 03:13:38'),
(112, 136, 1, '2026-01-09 03:15:39'),
(113, 137, 1, '2026-01-09 03:17:46'),
(114, 138, 1, '2026-01-09 03:20:13'),
(115, 139, 1, '2026-01-09 03:21:10'),
(116, 140, 1, '2026-01-09 03:22:10'),
(117, 141, 1, '2026-01-09 03:38:49'),
(118, 142, 1, '2026-01-09 03:39:41'),
(119, 143, 1, '2026-01-09 03:40:16'),
(120, 144, 1, '2026-01-09 03:41:06'),
(121, 145, 1, '2026-01-09 03:42:05'),
(122, 146, 1, '2026-01-09 03:42:41'),
(123, 147, 1, '2026-01-09 03:43:36'),
(124, 148, 1, '2026-01-09 03:44:36'),
(125, 149, 1, '2026-01-09 03:46:03'),
(126, 150, 1, '2026-01-09 03:46:59'),
(127, 151, 1, '2026-01-09 03:47:33'),
(128, 152, 1, '2026-01-09 03:54:13'),
(129, 153, 1, '2026-01-09 03:58:46'),
(130, 154, 1, '2026-01-09 04:00:41'),
(131, 155, 8, '2026-01-09 04:29:33'),
(132, 156, 8, '2026-01-09 04:30:14'),
(133, 157, 8, '2026-01-09 04:31:46'),
(134, 158, 8, '2026-01-09 04:32:47'),
(135, 159, 8, '2026-01-09 04:39:26'),
(136, 160, 8, '2026-01-09 04:40:16'),
(137, 161, 8, '2026-01-09 04:41:02'),
(138, 162, 8, '2026-01-09 04:41:40'),
(139, 163, 8, '2026-01-09 04:51:34'),
(140, 164, 8, '2026-01-09 04:52:13'),
(141, 165, 2, '2026-01-09 04:59:04'),
(142, 166, 2, '2026-01-09 04:59:49'),
(143, 167, 2, '2026-01-09 05:01:12'),
(144, 168, 2, '2026-01-09 05:04:02'),
(145, 169, 2, '2026-01-09 05:05:05'),
(146, 170, 2, '2026-01-09 05:06:40'),
(147, 171, 2, '2026-01-09 05:07:51'),
(148, 172, 2, '2026-01-09 05:18:05'),
(149, 173, 2, '2026-01-09 05:19:13'),
(150, 174, 2, '2026-01-09 05:20:11'),
(151, 175, 2, '2026-01-09 05:21:54'),
(152, 176, 2, '2026-01-09 05:24:59'),
(153, 177, 2, '2026-01-09 05:25:50'),
(154, 178, 2, '2026-01-09 05:27:35'),
(155, 179, 6, '2026-01-09 19:38:31'),
(156, 180, 6, '2026-01-09 19:39:37'),
(157, 181, 7, '2026-01-09 19:42:30'),
(158, 182, 7, '2026-01-09 19:43:28'),
(159, 183, 6, '2026-01-09 19:45:11'),
(160, 184, 6, '2026-01-09 19:46:01'),
(161, 185, 6, '2026-01-09 19:47:33'),
(162, 186, 10, '2026-01-09 19:53:46'),
(163, 187, 6, '2026-01-09 20:07:29'),
(164, 188, 6, '2026-01-09 20:08:16'),
(165, 189, 6, '2026-01-09 20:09:13'),
(166, 190, 5, '2026-01-09 20:12:39'),
(167, 191, 5, '2026-01-09 20:13:41'),
(168, 192, 5, '2026-01-09 20:14:24'),
(169, 193, 5, '2026-01-09 20:15:16'),
(170, 194, 5, '2026-01-09 20:15:55'),
(171, 195, 5, '2026-01-09 20:18:10'),
(172, 196, 5, '2026-04-20 19:41:27'),
(173, 197, 7, '2026-04-20 20:15:09'),
(174, 198, 8, '2026-04-20 20:15:44'),
(177, 199, 8, '2026-04-22 07:20:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(11, 2, 5, '2025-12-14 09:11:28'),
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
(49, 2, 49, '2026-01-07 23:21:14'),
(50, 1, 42, '2026-01-09 02:58:15'),
(51, 1, 50, '2026-01-09 03:14:55'),
(52, 1, 51, '2026-01-09 03:34:38'),
(53, 1, 40, '2026-01-09 03:53:49'),
(54, 8, 52, '2026-01-09 04:29:16'),
(55, 6, 53, '2026-01-09 19:37:29'),
(56, 7, 53, '2026-01-09 19:37:29'),
(57, 7, 5, '2026-01-09 19:41:06'),
(58, 6, 5, '2026-01-09 19:41:06'),
(59, 10, 55, '2026-01-09 19:53:33');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `checkout_mode` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'cart',
  `client_type` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `client_id` int NOT NULL DEFAULT '0',
  `nom_client` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telephone` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `zone_id` int NOT NULL,
  `zone_nom` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `livraison_prix` double NOT NULL DEFAULT '0',
  `sous_total` double NOT NULL DEFAULT '0',
  `total` double NOT NULL DEFAULT '0',
  `statut` varchar(40) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'nouvelle',
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_order_number` (`order_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `order_number`, `checkout_mode`, `client_type`, `client_id`, `nom_client`, `telephone`, `adresse`, `email`, `zone_id`, `zone_nom`, `livraison_prix`, `sous_total`, `total`, `statut`, `date_ajout`) VALUES
(1, 'OHN-20260418140955-214', 'cart', 'invite', 0, 'Clare Bean', '+1 (195) 354-5454', 'Aut alias qui qui cu', 'riduh@mailinator.com', 1, 'Ngaliema', 5, 12, 17, 'paiement_initié', '2026-04-18 13:09:55');

-- --------------------------------------------------------

--
-- Structure de la table `commande_articles`
--

DROP TABLE IF EXISTS `commande_articles`;
CREATE TABLE IF NOT EXISTS `commande_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `commande_id` int NOT NULL,
  `article_id` int NOT NULL DEFAULT '0',
  `article_nom` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `article_slug` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taille` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  `prix_unitaire` double NOT NULL DEFAULT '0',
  `image` text COLLATE utf8mb4_general_ci,
  `boutique_id` int NOT NULL DEFAULT '0',
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_commande_articles_commande_id` (`commande_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_articles`
--

INSERT INTO `commande_articles` (`id`, `commande_id`, `article_id`, `article_nom`, `article_slug`, `taille`, `quantite`, `prix_unitaire`, `image`, `boutique_id`, `date_ajout`) VALUES
(1, 1, 193, 'Fond de teint Elf (10 couleurs disponible)', 'fond-de-teint-elf-10-couleurs-disponible', '', 1, 12, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767986114388_0jrLLANbj.webp', 12, '2026-04-18 13:09:55');

-- --------------------------------------------------------

--
-- Structure de la table `delivery_settings`
--

DROP TABLE IF EXISTS `delivery_settings`;
CREATE TABLE IF NOT EXISTS `delivery_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_general_ci,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_delivery_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `delivery_settings`
--

INSERT INTO `delivery_settings` (`id`, `setting_key`, `setting_value`, `date_ajout`) VALUES
(1, 'use_global_price', '0', '2026-04-13 21:18:32'),
(2, 'global_price', '0', '2026-04-13 21:18:32');

-- --------------------------------------------------------

--
-- Structure de la table `delivery_zones`
--

DROP TABLE IF EXISTS `delivery_zones`;
CREATE TABLE IF NOT EXISTS `delivery_zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prix` double NOT NULL DEFAULT '0',
  `actif` int NOT NULL DEFAULT '1',
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `delivery_zones`
--

INSERT INTO `delivery_zones` (`id`, `nom`, `slug`, `prix`, `actif`, `date_ajout`) VALUES
(1, 'Ngaliema', 'ngaliema', 5, 1, '2026-04-13 21:32:07');

-- --------------------------------------------------------

--
-- Structure de la table `image_articles`
--

DROP TABLE IF EXISTS `image_articles`;
CREATE TABLE IF NOT EXISTS `image_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article` int NOT NULL,
  `img` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `fileId` text COLLATE utf8mb4_general_ci,
  `alt_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `styles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  `modif_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `img_article` (`article`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `image_articles`
--

INSERT INTO `image_articles` (`id`, `article`, `img`, `fileId`, `alt_text`, `background`, `styles`, `date_ajout`, `modif_date`) VALUES
(70, 22, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1766335652223_Dw_CtxCPa.webp', NULL, 'sac-a-main-femme-vintage', 'rgb(128, 116, 100)', 'width: auto; height: 100%;', '2025-12-21 17:47:35', '2025-12-21 17:47:35'),
(71, 23, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1766340913536_vzMIi4OCt.webp', NULL, 'sac-guess', 'rgb(173, 165, 163)', 'width: 100%; height: auto;', '2025-12-21 19:15:16', '2025-12-21 19:15:16'),
(82, 35, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533615157_bPPTwzrej.webp', NULL, 'prada-sac-a-main', 'rgb(190, 199, 190)', 'width: auto; height: 100%;', '2026-01-04 14:33:40', '2026-01-04 14:33:40'),
(83, 36, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533745439_ZoYXVqrYg.webp', NULL, 'sac-four-tout', 'rgb(136, 114, 103)', 'width: auto; height: 100%;', '2026-01-04 14:35:47', '2026-01-04 14:35:47'),
(84, 37, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533871747_W65o-tNbg.webp', NULL, 'sac-etudiante', 'rgb(151, 110, 102)', 'width: 100%; height: auto;', '2026-01-04 14:37:54', '2026-01-04 14:37:54'),
(85, 38, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767533976830_Bxs4KVnqH.webp', NULL, 'mini-sac-de-soir-ee', 'rgb(139, 113, 114)', 'width: 100%; height: auto;', '2026-01-04 14:39:39', '2026-01-04 14:39:39'),
(86, 39, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534065661_vw-z8gzRE.webp', NULL, 'sac-rebelle', 'rgb(38, 22, 20)', 'width: auto; height: 100%;', '2026-01-04 14:41:08', '2026-01-04 14:41:08'),
(87, 40, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534153579_fyxatQAoj.webp', NULL, 'mini-sac-crocos', 'rgb(100, 99, 87)', 'width: auto; height: 100%;', '2026-01-04 14:42:36', '2026-01-04 14:42:36'),
(88, 41, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534311007_7c7yXiHQw.webp', NULL, 'sac-coraille', 'rgb(156, 139, 127)', 'width: 100%; height: auto;', '2026-01-04 14:45:13', '2026-01-04 14:45:13'),
(89, 42, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534381852_HvMY_8zgf.webp', NULL, 'sac-corde-rouge', 'rgb(107, 91, 86)', 'width: auto; height: 100%;', '2026-01-04 14:46:23', '2026-01-04 14:46:23'),
(90, 43, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767534505726_y04zJSgG9.webp', NULL, 'sac-jaguar', 'rgb(130, 101, 94)', 'width: auto; height: 100%;', '2026-01-04 14:48:28', '2026-01-04 14:48:28'),
(95, 56, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767549238994_a4NVcL4yZ.webp', NULL, 'lunettes-tom-ford', 'rgb(157, 147, 134)', 'width: auto; height: 100%;', '2026-01-04 18:54:02', '2026-01-04 18:54:02'),
(96, 57, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557120632_jwyeDtZJBO.webp', NULL, 'lunette-de-soleil-loewe', 'rgb(184, 185, 184)', 'width: 100%; height: auto;', '2026-01-04 21:05:25', '2026-01-04 21:05:25'),
(97, 58, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557206646_dVjrbrk1x.webp', NULL, 'lunette-all-black-loewe', 'rgb(176, 174, 173)', 'width: auto; height: 100%;', '2026-01-04 21:06:49', '2026-01-04 21:06:49'),
(98, 59, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557384436__6uYzatorl.webp', NULL, 'lunettes-rose-view', 'rgb(220, 218, 215)', 'width: auto; height: 100%;', '2026-01-04 21:09:46', '2026-01-04 21:09:46'),
(99, 60, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557476850_S2K2ilIz8.webp', NULL, 'lunette-de-plage-blanche', 'rgb(169, 161, 147)', 'width: auto; height: 100%;', '2026-01-04 21:11:19', '2026-01-04 21:11:19'),
(100, 61, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557550670_eK4-RZRcZ.webp', NULL, 'duo-montarde-et-dalmacien', 'rgb(154, 149, 133)', 'width: auto; height: 100%;', '2026-01-04 21:12:33', '2026-01-04 21:12:33'),
(101, 62, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557624415_nWDQQW1e8.webp', NULL, 'lunette-metal-dor-ee', 'rgb(156, 152, 144)', 'width: auto; height: 100%;', '2026-01-04 21:13:46', '2026-01-04 21:13:46'),
(102, 63, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767557683935_Eosj1tvSl.webp', NULL, 'lunette-hibou', 'rgb(167, 147, 130)', 'width: auto; height: 100%;', '2026-01-04 21:14:46', '2026-01-04 21:14:46'),
(103, 64, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767624293757_HQL6fbb144.webp', NULL, 'talons-noir-a-pointe-rouge', 'rgb(139, 134, 131)', 'width: auto; height: 100%;', '2026-01-05 15:44:56', '2026-01-05 15:44:56'),
(104, 65, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672661453_Ogc2qm6Wm.webp', NULL, 'talon-brun-a-pointe-argent-e', 'rgb(26, 16, 17)', 'width: auto; height: 100%;', '2026-01-06 05:11:04', '2026-01-06 05:11:04'),
(105, 66, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672729163_3eC29U4_D.webp', NULL, 'talon-bleu-alala', 'rgb(220, 222, 227)', 'width: auto; height: 100%;', '2026-01-06 05:12:10', '2026-01-06 05:12:10'),
(106, 67, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672810266_oJV2jBUhJ.webp', NULL, 'talon-pleine-rouge-bordeau', 'rgb(145, 106, 81)', 'width: auto; height: 100%;', '2026-01-06 05:13:32', '2026-01-06 05:13:32'),
(107, 68, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767672879606_NmCSIsavQ.webp', NULL, 'talon-jumelle-de-jeffrey-campbell', 'rgb(138, 119, 91)', 'width: auto; height: 100%;', '2026-01-06 05:14:42', '2026-01-06 05:14:42'),
(108, 69, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673001239_wDIcI8VkO.webp', NULL, 'talon-vert-croco', 'rgb(47, 50, 40)', 'width: auto; height: 100%;', '2026-01-06 05:16:43', '2026-01-06 05:16:43'),
(109, 70, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673081834_QdLFF6qf1.webp', NULL, 'talon-de-gala-dor-ee', 'rgb(170, 168, 163)', 'width: auto; height: 100%;', '2026-01-06 05:18:03', '2026-01-06 05:18:03'),
(110, 71, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673460963_VRNURcGFj.webp', NULL, 'talon-a-semelle-compens-ee', 'rgb(151, 138, 133)', 'width: 100%; height: auto;', '2026-01-06 05:24:23', '2026-01-06 05:24:23'),
(111, 72, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673542624_JWV7dXLBl.webp', NULL, 'talon-fleurie-jaune-dor-e', 'rgb(143, 137, 128)', 'width: auto; height: 100%;', '2026-01-06 05:25:45', '2026-01-06 05:25:45'),
(112, 73, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673597692_3r-KrKOBE.webp', NULL, 'talon-dara-noir-dor-e', 'rgb(153, 149, 149)', 'width: auto; height: 100%;', '2026-01-06 05:26:39', '2026-01-06 05:26:39'),
(113, 74, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673657830_qze1MaN4f.webp', NULL, 'talon-vert-sombre-saint-laurent', 'rgb(159, 160, 142)', 'width: auto; height: 100%;', '2026-01-06 05:27:39', '2026-01-06 05:27:39'),
(114, 75, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673733166_RicCxGONH.webp', NULL, 'talon-bois-e', 'rgb(116, 105, 97)', 'width: auto; height: 100%;', '2026-01-06 05:28:55', '2026-01-06 05:28:55'),
(115, 76, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673778734_wpcMp6ESnG.webp', NULL, 'talon-coeur-d-afrique', 'rgb(113, 98, 65)', 'width: auto; height: 100%;', '2026-01-06 05:29:40', '2026-01-06 05:29:40'),
(116, 77, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767673877105_ONGk6X8QE.webp', NULL, 'talon-grande-dame-rouge-bordeau', 'rgb(129, 113, 109)', 'width: 100%; height: auto;', '2026-01-06 05:31:19', '2026-01-06 05:31:19'),
(117, 78, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767810950855_tOlsYU-GL.webp', NULL, 'haut-corset-gris', 'rgb(146, 139, 138)', 'width: auto; height: 100%;', '2026-01-07 19:35:53', '2026-01-07 19:35:53'),
(118, 79, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811118630_WeaD_ebze.webp', NULL, 'longue-robe-rouge-d-ecolt-e-plongeant', 'rgb(119, 104, 109)', 'width: auto; height: 100%;', '2026-01-07 19:38:42', '2026-01-07 19:38:42'),
(119, 80, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811353588_WbZwro7n6.webp', NULL, 'robe-orange-a-manche-longue', 'rgb(114, 93, 82)', 'width: auto; height: 100%;', '2026-01-07 19:42:36', '2026-01-07 19:42:36'),
(120, 81, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811455148_LBo68tpi2w.webp', NULL, 'mini-robe-noir-de-soir-ee', 'rgb(198, 187, 183)', 'width: auto; height: 100%;', '2026-01-07 19:44:17', '2026-01-07 19:44:17'),
(121, 82, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811616445_DmKFd73qp.webp', NULL, 'robe-elegante-vest-grise', 'rgb(142, 144, 153)', 'width: auto; height: 100%;', '2026-01-07 19:46:58', '2026-01-07 19:46:58'),
(122, 83, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811675412__2_UmR7eV.webp', NULL, 'robe-fleure-roge', 'rgb(128, 110, 103)', 'width: auto; height: 100%;', '2026-01-07 19:47:58', '2026-01-07 19:47:58'),
(123, 84, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811727116_FlqRk9vOi.webp', NULL, 'mini-robe-de-soir-ee-coeur-rouge', 'rgb(120, 85, 72)', 'width: auto; height: 100%;', '2026-01-07 19:48:49', '2026-01-07 19:48:49'),
(124, 85, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811839463_FxZxiOaus.webp', NULL, 'robe-noire-hasse-de-pique', 'rgb(104, 92, 85)', 'width: auto; height: 100%;', '2026-01-07 19:50:41', '2026-01-07 19:50:41'),
(125, 86, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767811879053_YGckweprh.webp', NULL, 'robe-cr-eme-jaune', 'rgb(143, 116, 88)', 'width: auto; height: 100%;', '2026-01-07 19:51:21', '2026-01-07 19:51:21'),
(126, 87, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812097132_J-IgzirKM.webp', NULL, 'robe-de-gala-coeur-bleu', 'rgb(160, 176, 192)', 'width: auto; height: 100%;', '2026-01-07 19:54:59', '2026-01-07 19:54:59'),
(127, 88, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812175893_XeDmhm3Sz.webp', NULL, 'robe-noir-aux-roses-blanches', 'rgb(173, 150, 116)', 'width: auto; height: 100%;', '2026-01-07 19:56:18', '2026-01-07 19:56:18'),
(128, 89, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812342133_hFI2f9POS.webp', NULL, 'robe-bleu-evas-ee', 'rgb(120, 101, 97)', 'width: auto; height: 100%;', '2026-01-07 19:59:04', '2026-01-07 19:59:04'),
(129, 90, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812442952_ykFMwpAJR.webp', NULL, 'robe-fleurie', 'rgb(221, 209, 201)', 'width: auto; height: 100%;', '2026-01-07 20:00:45', '2026-01-07 20:00:45'),
(130, 91, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812627157_LNw83zDzu.webp', NULL, 'robe-dame-nature', 'rgb(196, 197, 180)', 'width: auto; height: 100%;', '2026-01-07 20:03:49', '2026-01-07 20:03:49'),
(131, 92, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812699148_0t9-X1F8c.webp', NULL, 'robe-libellule', 'rgb(192, 184, 195)', 'width: auto; height: 100%;', '2026-01-07 20:05:01', '2026-01-07 20:05:01'),
(132, 93, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767812752907_KncJC5AU9s.webp', NULL, 'robe-rose-jaune', 'rgb(190, 187, 166)', 'width: auto; height: 100%;', '2026-01-07 20:05:55', '2026-01-07 20:05:55'),
(133, 94, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813304998_BP0cpar5F.webp', NULL, 'ensemble-en-jeans', 'rgb(127, 119, 113)', 'width: auto; height: 100%;', '2026-01-07 20:15:07', '2026-01-07 20:15:07'),
(134, 95, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813347527_zJrq52VDa.webp', NULL, 'mini-robe-arc-en-ciel', 'rgb(160, 90, 81)', 'width: auto; height: 100%;', '2026-01-07 20:15:50', '2026-01-07 20:15:50'),
(135, 96, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813429835_uJ05LntOe.webp', NULL, 'duo-vert-et-bleu', 'rgb(89, 94, 79)', 'width: auto; height: 100%;', '2026-01-07 20:17:11', '2026-01-07 20:17:11'),
(136, 97, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813474167_aX-ictxP7.webp', NULL, 'robe-rouge-femme-africaine', 'rgb(26, 80, 118)', 'width: auto; height: 100%;', '2026-01-07 20:17:56', '2026-01-07 20:17:56'),
(137, 98, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813523678_-k0RBGi9-.webp', NULL, 'robe-fleure-rose', 'rgb(171, 140, 157)', 'width: auto; height: 100%;', '2026-01-07 20:18:45', '2026-01-07 20:18:45'),
(138, 99, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813597194_-pn3Fi7KS.webp', NULL, 'une-danse-dor-ee', 'rgb(98, 87, 81)', 'width: auto; height: 100%;', '2026-01-07 20:19:59', '2026-01-07 20:19:59'),
(139, 100, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767813848102_rI_IlvZXM.webp', NULL, 'robe-de-soir-ee-dor-ee-caramel', 'rgb(61, 48, 37)', 'width: auto; height: 100%;', '2026-01-07 20:24:10', '2026-01-07 20:24:10'),
(140, 101, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824228549_nYwNsXV28.webp', NULL, 'robe-feuille-de-fleure', 'rgb(142, 110, 80)', 'width: auto; height: 100%;', '2026-01-07 23:17:10', '2026-01-07 23:17:10'),
(141, 102, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824499277_6XyBCV8qK.webp', NULL, 'boubou-couleur-metal', 'rgb(159, 149, 161)', 'width: auto; height: 100%;', '2026-01-07 23:21:41', '2026-01-07 23:21:41'),
(142, 103, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824541438_l21CDyjUtc.webp', NULL, 'robe-rose-fleurie', 'rgb(164, 119, 126)', 'width: auto; height: 100%;', '2026-01-07 23:22:24', '2026-01-07 23:22:24'),
(143, 104, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824586526_HMxGz5WZA.webp', NULL, 'robe-violette', 'rgb(175, 123, 139)', 'width: auto; height: 100%;', '2026-01-07 23:23:08', '2026-01-07 23:23:08'),
(144, 105, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824628832_MevvlITvm.webp', NULL, 'robe-orange-neud-papillon', 'rgb(180, 103, 93)', 'width: auto; height: 100%;', '2026-01-07 23:23:51', '2026-01-07 23:23:51'),
(145, 106, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824663834_heX-h9qXf.webp', NULL, 'robe-nouvelle-plante-verte', 'rgb(132, 118, 94)', 'width: auto; height: 100%;', '2026-01-07 23:24:25', '2026-01-07 23:24:25'),
(146, 107, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824717119_0CPU3R_FK.webp', NULL, 'robe-rouge-sang', 'rgb(118, 90, 86)', 'width: auto; height: 100%;', '2026-01-07 23:25:19', '2026-01-07 23:25:19'),
(147, 108, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824765107_EC_46h2lH.webp', NULL, 'robe-en-pagne-dor-ee', 'rgb(161, 147, 137)', 'width: auto; height: 100%;', '2026-01-07 23:26:07', '2026-01-07 23:26:07'),
(148, 109, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824812192_mKWOPNjzx.webp', NULL, 'boubou-grande-dame-vert-sombre', 'rgb(134, 133, 129)', 'width: auto; height: 100%;', '2026-01-07 23:26:54', '2026-01-07 23:26:54'),
(149, 110, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824861158_MTg2IpHR1.webp', NULL, 'boubou-cam-el-eon', 'rgb(120, 138, 151)', 'width: auto; height: 100%;', '2026-01-07 23:27:43', '2026-01-07 23:27:43'),
(150, 111, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824927109_1CtC24xHv.webp', NULL, 'robe-violette-grande-dame', 'rgb(104, 79, 127)', 'width: auto; height: 100%;', '2026-01-07 23:28:49', '2026-01-07 23:28:49'),
(151, 112, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767824987474_sRxICNyS3.webp', NULL, 'boubou-beige', 'rgb(190, 167, 141)', 'width: auto; height: 100%;', '2026-01-07 23:29:49', '2026-01-07 23:29:49'),
(152, 113, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825068817_tQc0UNkeb.webp', NULL, 'boubou-jaune-dor-ee', 'rgb(239, 207, 167)', 'width: auto; height: 100%;', '2026-01-07 23:31:10', '2026-01-07 23:31:10'),
(153, 114, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825106622_K3I6mtCBA.webp', NULL, 'robe-jupe-l-eopard', 'rgb(145, 126, 122)', 'width: auto; height: 100%;', '2026-01-07 23:31:49', '2026-01-07 23:31:49'),
(154, 115, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825248217_QANYQlrxN.webp', NULL, 'robe-beige', 'rgb(162, 150, 134)', 'width: auto; height: 100%;', '2026-01-07 23:34:10', '2026-01-07 23:34:10'),
(155, 116, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767825477764_hzsUgul0O.webp', NULL, 'boucle-d-oreille-soleil', 'rgb(144, 131, 126)', 'width: auto; height: 100%;', '2026-01-07 23:37:59', '2026-01-07 23:37:59'),
(156, 117, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826045320_j8HCzFlld.webp', NULL, 'ensemble-coliers-boucles-d-oreilles-bagues', 'rgb(146, 104, 79)', 'width: auto; height: 100%;', '2026-01-07 23:47:27', '2026-01-07 23:47:27'),
(157, 118, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826115791_WxGibu7S7.webp', NULL, 'braceles-motife-marbre-brun', 'rgb(187, 159, 139)', 'width: auto; height: 100%;', '2026-01-07 23:48:38', '2026-01-07 23:48:38'),
(158, 119, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826219553_ZvcruG0we.webp', NULL, 'braceles-dor-ee', 'rgb(139, 113, 94)', 'width: 100%; height: auto;', '2026-01-07 23:50:21', '2026-01-07 23:50:21'),
(159, 120, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826297355_MZNXSiIDO.webp', NULL, 'bagues-perles-argent-ee', 'rgb(170, 155, 143)', 'width: auto; height: 100%;', '2026-01-07 23:51:39', '2026-01-07 23:51:39'),
(160, 121, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826542741_i-Bzwt6ot.webp', NULL, 'bracelets-motif-marbre-noir', 'rgb(172, 149, 130)', 'width: auto; height: 100%;', '2026-01-07 23:55:44', '2026-01-07 23:55:44'),
(161, 122, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826737044_qG0EmuDlX.webp', NULL, 'complet-bracelet-marbre-beige-et-vert', 'rgb(160, 150, 126)', 'width: auto; height: 100%;', '2026-01-07 23:58:59', '2026-01-07 23:58:59'),
(162, 123, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826783460_mc2eiA2N4.webp', NULL, 'boucles-d-oreilles-see-me', 'rgb(146, 105, 74)', 'width: auto; height: 100%;', '2026-01-07 23:59:45', '2026-01-07 23:59:45'),
(163, 124, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826867158_nIkL5PulW.webp', NULL, 'colliers-en-perle-grande-dame', 'rgb(137, 115, 104)', 'width: auto; height: 100%;', '2026-01-08 00:01:09', '2026-01-08 00:01:09'),
(164, 125, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767826995148_ghboIl9IZ.webp', NULL, 'bracelet-diad-eme-en-perles', 'rgb(119, 72, 51)', 'width: auto; height: 100%;', '2026-01-08 00:03:17', '2026-01-08 00:03:17'),
(165, 126, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827098826_aIf_kLY0T.webp', NULL, 'trois-bracelets-dor-ee', 'rgb(115, 104, 89)', 'width: 100%; height: auto;', '2026-01-08 00:05:00', '2026-01-08 00:05:00'),
(166, 127, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827238226_xmzUOog-x.webp', NULL, 'coiffe-de-perle', 'rgb(123, 117, 111)', 'width: auto; height: 100%;', '2026-01-08 00:07:20', '2026-01-08 00:07:20'),
(167, 128, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827315266_gf0D3yfcW.webp', NULL, 'bracelet-motif-marbre-blanc-assorti-a-ses-bagues', 'rgb(119, 100, 88)', 'width: auto; height: 100%;', '2026-01-08 00:08:36', '2026-01-08 00:08:36'),
(168, 129, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827356069_RtZX2K6T-.webp', NULL, 'bracelets-el-egante-dame', 'rgb(166, 140, 129)', 'width: auto; height: 100%;', '2026-01-08 00:09:18', '2026-01-08 00:09:18'),
(169, 130, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827407583_KPUH3D6bK.webp', NULL, 'coiffe-de-perle-queen-africa', 'rgb(62, 61, 54)', 'width: auto; height: 100%;', '2026-01-08 00:10:09', '2026-01-08 00:10:09'),
(170, 131, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827470295_4oqn4sucL.webp', NULL, 'complet-bracelets-et-bagues-argent-ee', 'rgb(153, 130, 116)', 'width: auto; height: 100%;', '2026-01-08 00:11:12', '2026-01-08 00:11:12'),
(171, 132, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827536679_BvUrlgFsj.webp', NULL, 'parure-collier-et-boucles-d-oreilles-dor-e', 'rgb(127, 82, 63)', 'width: auto; height: 100%;', '2026-01-08 00:12:18', '2026-01-08 00:12:18'),
(172, 133, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767827592887_hzEDEyBvK.webp', NULL, 'bracelets-motif-marbre-blanc', 'rgb(184, 181, 174)', 'width: auto; height: 100%;', '2026-01-08 00:13:14', '2026-01-08 00:13:14'),
(173, 134, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767924679366_ykGJOAUo1.webp', NULL, 'casquettes-boston-6-couleurs-disponible', 'rgb(204, 199, 194)', 'width: auto; height: 100%;', '2026-01-09 03:11:22', '2026-01-09 03:11:22'),
(174, 135, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767924816754_f8Z6zhI7Q.webp', NULL, 'bandana-z-ebre', 'rgb(230, 228, 228)', 'width: auto; height: 100%;', '2026-01-09 03:13:38', '2026-01-09 03:13:38'),
(175, 136, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767924936766_aNrT2PgUu.webp', NULL, 'patek-philipe-dor-ee', 'rgb(132, 114, 105)', 'width: 100%; height: auto;', '2026-01-09 03:15:39', '2026-01-09 03:15:39'),
(176, 137, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767925063632_Q28yJwSsn.webp', NULL, 'montres-casio-3-couleurs-disponibles', 'rgb(141, 137, 125)', 'width: auto; height: 100%;', '2026-01-09 03:17:46', '2026-01-09 03:17:46'),
(177, 138, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767925210789_rh-UfEdbG.webp', NULL, 'chaussures-noir-sir', 'rgb(155, 147, 136)', 'width: auto; height: 100%;', '2026-01-09 03:20:13', '2026-01-09 03:20:13'),
(178, 139, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767925267564_r8wjgW1yvt.webp', NULL, 'chaussure-miu-miu-brun', 'rgb(149, 140, 136)', 'width: auto; height: 100%;', '2026-01-09 03:21:10', '2026-01-09 03:21:10'),
(179, 140, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767925327922_dkosUcnU7.webp', NULL, 'chaussure-grenson-couleur-gazon', 'rgb(164, 152, 140)', 'width: auto; height: 100%;', '2026-01-09 03:22:10', '2026-01-09 03:22:10'),
(180, 141, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926327163_mzcsk3sjg.webp', NULL, 'chausson-noir-cuire', 'rgb(162, 159, 157)', 'width: auto; height: 100%;', '2026-01-09 03:38:49', '2026-01-09 03:38:49'),
(181, 142, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926379342_c6hVbxCXa.webp', NULL, 'montre-casio-full-argent-e', 'rgb(71, 68, 65)', 'width: auto; height: 100%;', '2026-01-09 03:39:41', '2026-01-09 03:39:41'),
(182, 143, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926413968_oIhxaXEVX.webp', NULL, 'chausure-prada', 'rgb(164, 160, 158)', 'width: auto; height: 100%;', '2026-01-09 03:40:16', '2026-01-09 03:40:16'),
(183, 144, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926464322_pN3PY4frn.webp', NULL, 'montre-casio-argent-e-coeur-du-gazon', 'rgb(122, 135, 151)', 'width: 100%; height: auto;', '2026-01-09 03:41:06', '2026-01-09 03:41:06'),
(184, 145, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926523069_OTD77Tx6a.webp', NULL, 'chaussure-homme-brun-fonc-e', 'rgb(182, 180, 180)', 'width: auto; height: 100%;', '2026-01-09 03:42:05', '2026-01-09 03:42:05'),
(185, 146, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926559517_ARfVYHnLFa.webp', NULL, 'montre-casio-coeur-noir', 'rgb(114, 114, 114)', 'width: auto; height: 100%;', '2026-01-09 03:42:41', '2026-01-09 03:42:41'),
(186, 147, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926614349_HgSd2ptU4.webp', NULL, 'chaussure-saint-laurent-imprim-e-marbre', 'rgb(138, 124, 117)', 'width: auto; height: 100%;', '2026-01-09 03:43:36', '2026-01-09 03:43:36'),
(187, 148, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926674282_9OM1CCXlY7.webp', NULL, 'chaussures-jacques-soloni-ere', 'rgb(210, 197, 191)', 'width: auto; height: 100%;', '2026-01-09 03:44:36', '2026-01-09 03:44:36'),
(188, 149, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926760882_kJ9b9K75B.webp', NULL, 'chaussure-charles-keith-brun', 'rgb(168, 166, 166)', 'width: auto; height: 100%;', '2026-01-09 03:46:03', '2026-01-09 03:46:03'),
(189, 150, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926816939_m6XrJd9tL.webp', NULL, 'zara-chaussure-brun', 'rgb(201, 199, 197)', 'width: auto; height: 100%;', '2026-01-09 03:46:59', '2026-01-09 03:46:59'),
(190, 151, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767926851688_8OPVDn7k9Z.webp', NULL, 'bob-mongolia', 'rgb(160, 136, 106)', 'width: auto; height: 100%;', '2026-01-09 03:47:33', '2026-01-09 03:47:33'),
(191, 152, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767927251619_sCREcdXmo.webp', NULL, 'sac-pour-homme-2-couleurs-disponibles', 'rgb(134, 129, 132)', 'width: auto; height: 100%;', '2026-01-09 03:54:13', '2026-01-09 03:54:13'),
(192, 153, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767927524521_2QOEhRJiR.webp', NULL, 'montre-casio-coeur-rouge', 'rgb(95, 76, 78)', 'width: auto; height: 100%;', '2026-01-09 03:58:46', '2026-01-09 03:58:46'),
(193, 154, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767927638901_lOKkCyUY7.webp', NULL, 'casquette-foulard', 'rgb(184, 174, 168)', 'width: auto; height: 100%;', '2026-01-09 04:00:41', '2026-01-09 04:00:41'),
(194, 155, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767929370718_pPMFJtxy0.webp', NULL, 'perruque-ch-atain', 'rgb(138, 126, 123)', 'width: auto; height: 100%;', '2026-01-09 04:29:33', '2026-01-09 04:29:33'),
(195, 156, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767929410763_GwR1zm0Kj.webp', NULL, 'longue-perruque-ch-atain', 'rgb(136, 118, 114)', 'width: 100%; height: auto;', '2026-01-09 04:30:14', '2026-01-09 04:30:14'),
(196, 157, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767929503765_T6gl4o7JE.webp', NULL, 'mallia-perruque-noir-a-frange', 'rgb(113, 101, 104)', 'width: auto; height: 100%;', '2026-01-09 04:31:46', '2026-01-09 04:31:46'),
(197, 158, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767929565271_tmbqkDJLa.webp', NULL, 'perruque-noir-a-frange-gauche', 'rgb(86, 41, 47)', 'width: auto; height: 100%;', '2026-01-09 04:32:47', '2026-01-09 04:32:47'),
(198, 159, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767929964228_-tcenCAur.webp', NULL, 'mod-ele-rihanna-longue-perruque-noir', 'rgb(62, 57, 53)', 'width: auto; height: 100%;', '2026-01-09 04:39:26', '2026-01-09 04:39:26'),
(199, 160, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767930014248_2kbjZxV8C.webp', NULL, 'perruque-courte-blonde', 'rgb(129, 110, 104)', 'width: auto; height: 100%;', '2026-01-09 04:40:16', '2026-01-09 04:40:16'),
(200, 161, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767930059083_6iaFloJkR.webp', NULL, 'perruque-blonde-a-frange-droite', 'rgb(146, 134, 122)', 'width: 100%; height: auto;', '2026-01-09 04:41:02', '2026-01-09 04:41:02'),
(201, 162, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767930098314_2xXJe-4fa.webp', NULL, 'perruque-argent-ee', 'rgb(110, 110, 113)', 'width: auto; height: 100%;', '2026-01-09 04:41:40', '2026-01-09 04:41:40'),
(202, 163, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767930692348_RDYCqBENK.webp', NULL, 'perruques-noires', 'rgb(108, 101, 97)', 'width: auto; height: 100%;', '2026-01-09 04:51:34', '2026-01-09 04:51:34'),
(203, 164, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767930731050_G0PsvzjzG.webp', NULL, 'perruques-badies', 'rgb(88, 83, 88)', 'width: auto; height: 100%;', '2026-01-09 04:52:13', '2026-01-09 04:52:13'),
(204, 165, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931142540_Ch3gDaQmg.webp', NULL, 'robe-noir-haut-argent-e', 'rgb(84, 82, 82)', 'width: auto; height: 100%;', '2026-01-09 04:59:04', '2026-01-09 04:59:04'),
(205, 166, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931187421_KElcqIFir.webp', NULL, 'robe-rose-grande-dame', 'rgb(146, 79, 91)', 'width: auto; height: 100%;', '2026-01-09 04:59:49', '2026-01-09 04:59:49'),
(206, 167, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931270304_MtoLEf9LM.webp', NULL, 'robe-noire-duchesse', 'rgb(90, 82, 79)', 'width: auto; height: 100%;', '2026-01-09 05:01:12', '2026-01-09 05:01:12'),
(207, 168, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931439306_CDoyMrv-O.webp', NULL, 'robe-de-galla-verte-mure', 'rgb(140, 138, 120)', 'width: auto; height: 100%;', '2026-01-09 05:04:02', '2026-01-09 05:04:02'),
(208, 169, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931503172_Bky3BMv3L.webp', NULL, 'robe-verte-argent-ee', 'rgb(125, 129, 124)', 'width: auto; height: 100%;', '2026-01-09 05:05:05', '2026-01-09 05:05:05'),
(209, 170, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931598493_o_1YvCiJQ.webp', NULL, 'robe-d-ecollet-e-argent-ee', 'rgb(119, 108, 103)', 'width: auto; height: 100%;', '2026-01-09 05:06:40', '2026-01-09 05:06:40'),
(210, 171, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767931668796_4wzDSr1UO.webp', NULL, 'robe-moulante-rouge', 'rgb(138, 84, 83)', 'width: auto; height: 100%;', '2026-01-09 05:07:51', '2026-01-09 05:07:51'),
(211, 172, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932282799_97UcmSndK.webp', NULL, 'robe-de-soir-ee-violette', 'rgb(157, 136, 142)', 'width: auto; height: 100%;', '2026-01-09 05:18:05', '2026-01-09 05:18:05'),
(212, 173, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932351238_KSmV2NHfP.webp', NULL, 'complet-cowgril', 'rgb(130, 113, 98)', 'width: auto; height: 100%;', '2026-01-09 05:19:13', '2026-01-09 05:19:13'),
(213, 174, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932409641_CxWBRtfgo.webp', NULL, 'corset-love-your-body-deux-couleurs-disponibles', 'rgb(177, 169, 169)', 'width: auto; height: 100%;', '2026-01-09 05:20:11', '2026-01-09 05:20:11'),
(214, 175, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932512261_uL_6WJ_3h.webp', NULL, 'robe-jaune-moutarde', 'rgb(135, 111, 86)', 'width: auto; height: 100%;', '2026-01-09 05:21:54', '2026-01-09 05:21:54'),
(215, 176, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932697247_vjl-glQpy.webp', NULL, 'robe-moulante-violette', 'rgb(155, 135, 139)', 'width: auto; height: 100%;', '2026-01-09 05:24:59', '2026-01-09 05:24:59'),
(216, 177, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932747540_YN8x7vo_d.webp', NULL, 'robe-orange-d-ecollet-e', 'rgb(149, 113, 109)', 'width: auto; height: 100%;', '2026-01-09 05:25:50', '2026-01-09 05:25:50'),
(217, 178, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767932853262_cuvDrbCip.webp', NULL, 'robe-verte-a-fente', 'rgb(157, 151, 121)', 'width: auto; height: 100%;', '2026-01-09 05:27:35', '2026-01-09 05:27:35'),
(218, 179, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767983908413_Sybn_DfMB9.webp', NULL, 'skincare-by-laroche-posay', 'rgb(134, 139, 141)', 'width: auto; height: 100%;', '2026-01-09 19:38:31', '2026-01-09 19:38:31'),
(219, 180, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767983974881_tInsCUN4R.webp', NULL, 'soins-du-visage-by-vichy-laboratoire', 'rgb(138, 114, 116)', 'width: auto; height: 100%;', '2026-01-09 19:39:37', '2026-01-09 19:39:37'),
(220, 181, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767984148397_HdkZx--9J.webp', NULL, 'brosses-pour-le-corps', 'rgb(142, 138, 130)', 'width: auto; height: 100%;', '2026-01-09 19:42:30', '2026-01-09 19:42:30'),
(221, 182, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767984205742_XKTrGPfpz.webp', NULL, 'brosses-pour-le-h-s', 'rgb(142, 120, 94)', 'width: auto; height: 100%;', '2026-01-09 19:43:28', '2026-01-09 19:43:28'),
(222, 183, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767984309009_xNtyOxhjN.webp', NULL, 'serum-pour-le-visage-la-roche-posay', 'rgb(147, 133, 120)', 'width: auto; height: 100%;', '2026-01-09 19:45:11', '2026-01-09 19:45:11'),
(223, 184, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767984359137_o_PMltXqd.webp', NULL, 'demaquillant-medicube', 'rgb(158, 158, 160)', 'width: auto; height: 100%;', '2026-01-09 19:46:01', '2026-01-09 19:46:01'),
(224, 185, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767984451297_EgGJUHI1w.webp', NULL, 'visage-care', 'rgb(159, 146, 130)', 'width: auto; height: 100%;', '2026-01-09 19:47:33', '2026-01-09 19:47:33'),
(225, 186, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767984824362_CaUIppnt1y.webp', NULL, 'brosse-a-dents-dentifrice-blanchissant', 'rgb(89, 85, 78)', 'width: auto; height: 100%;', '2026-01-09 19:53:46', '2026-01-09 19:53:46'),
(226, 187, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767985647275_t6UsHTd3F.webp', NULL, 'soins-du-visage-mela-b3', 'rgb(144, 143, 139)', 'width: auto; height: 100%;', '2026-01-09 20:07:29', '2026-01-09 20:07:29'),
(227, 188, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767985694377_Iw4SkEsrAI.webp', NULL, 'cr-eme-medicube', 'rgb(222, 166, 176)', 'width: auto; height: 100%;', '2026-01-09 20:08:16', '2026-01-09 20:08:16'),
(228, 189, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767985750913_HliARhx4o.webp', NULL, 'serum-renitol-intense', 'rgb(162, 162, 161)', 'width: auto; height: 100%;', '2026-01-09 20:09:13', '2026-01-09 20:09:13'),
(229, 190, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767985956941_8axOW5Yhb.webp', NULL, 'fond-de-teint-hudabeauty', 'rgb(159, 158, 151)', 'width: auto; height: 100%;', '2026-01-09 20:12:39', '2026-01-09 20:12:39'),
(230, 191, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767986019534_oPErVXTCV.webp', NULL, 'eyeliner-hudabeauty', 'rgb(111, 91, 88)', 'width: auto; height: 100%;', '2026-01-09 20:13:41', '2026-01-09 20:13:41'),
(231, 192, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767986062339_3nzxdQp2B.webp', NULL, 'fond-de-teint-nyx', 'rgb(230, 219, 219)', 'width: 100%; height: auto;', '2026-01-09 20:14:24', '2026-01-09 20:14:24'),
(232, 193, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767986114388_0jrLLANbj.webp', NULL, 'fond-de-teint-elf-10-couleurs-disponible', 'rgb(131, 99, 85)', 'width: 100%; height: auto;', '2026-01-09 20:15:16', '2026-01-09 20:15:16'),
(233, 194, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767986153361_6EDF5uXHt.webp', NULL, 'fond-bare-white-me-by-nix', 'rgb(244, 239, 237)', 'width: 100%; height: auto;', '2026-01-09 20:15:55', '2026-01-09 20:15:55'),
(234, 195, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/crop_1767986288664_UamjGKTB4.webp', NULL, 'fond-de-teint-by-nars', 'rgb(156, 143, 142)', 'width: auto; height: 100%;', '2026-01-09 20:18:10', '2026-01-09 20:18:10'),
(235, 196, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/barbody-friperie_1776710482768_HbSlYGan1.webp', '69e673565c7cd75eb83063f5', 'belle-go', 'rgb(136, 114, 103)', 'width: auto; height: 100%;', '2026-04-20 19:41:27', '2026-04-20 19:41:27'),
(236, 196, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/barbody-friperie_1776710485870_3A5ZS_JVo.webp', '69e673575c7cd75eb83075a0', 'belle-go', 'rgb(144, 131, 126)', 'width: auto; height: 100%;', '2026-04-20 19:41:27', '2026-04-20 19:41:27'),
(237, 197, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/barbody-friperie_1776712506276_vImFnJUf6.webp', '69e67b3d5c7cd75eb87c683d', 'beni', 'rgb(135, 130, 127)', 'width: auto; height: 100%;', '2026-04-20 20:15:09', '2026-04-20 20:15:09'),
(238, 198, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/barbody-friperie_1776712541285_lxHPdfqOJL.webp', '69e67b605c7cd75eb87d40fc', 'quaerat-dignissimos', 'rgb(177, 188, 178)', 'width: auto; height: 100%;', '2026-04-20 20:15:44', '2026-04-20 20:15:44'),
(241, 199, 'https://ik.imagekit.io/nyombi1997/OhNous/articles/barbody-friperie_1776838810941_PoU5ES5Re.webp', '69e8689c5c7cd75eb88474f3', 'quaerat-dignissimos-1', 'rgb(135, 106, 85)', 'width: 100%; height: auto;', '2026-04-22 07:20:14', '2026-04-22 07:20:14');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `boutique_id` int NOT NULL,
  `from_id` int NOT NULL,
  `messages` text NOT NULL,
  `lu` int NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notes_article`
--

DROP TABLE IF EXISTS `notes_article`;
CREATE TABLE IF NOT EXISTS `notes_article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `client_type` varchar(30) DEFAULT NULL,
  `article_id` int NOT NULL,
  `note` double NOT NULL,
  `commentaire` text,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `notes_article`
--

INSERT INTO `notes_article` (`id`, `client_id`, `client_type`, `article_id`, `note`, `commentaire`, `date_ajout`) VALUES
(1, 23, 'boutique', 74, 5, 'J\'aime beaucoup ça', '2026-04-12 11:47:28');

-- --------------------------------------------------------

--
-- Structure de la table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `provider` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `reference` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `freshpay_transaction_id` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `financial_institution_id` varchar(190) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_number` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `request_payload` longtext COLLATE utf8mb4_general_ci,
  `response_payload` longtext COLLATE utf8mb4_general_ci,
  `callback_payload` longtext COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'initiated',
  `trans_status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `trans_status_description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_payment_reference` (`reference`),
  KEY `idx_payment_order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `order_id`, `provider`, `payment_method`, `reference`, `freshpay_transaction_id`, `financial_institution_id`, `customer_number`, `amount`, `currency`, `request_payload`, `response_payload`, `callback_payload`, `status`, `trans_status`, `trans_status_description`, `created_at`, `updated_at`) VALUES
(1, 1, 'freshpay', 'mobile_money', 'FP-20260418140955-1-35B9DB', '', '', '******2988', 17.00, 'USD', '{\"merchant_id\":\"your_merchant_id\",\"merchant_secrete\":\"your_merchant_secret\",\"amount\":\"17.00\",\"currency\":\"USD\",\"action\":\"debit\",\"customer_number\":\"0898212988\",\"firstname\":\"Clare\",\"lastname\":\"Bean\",\"e-mail\":\"riduh@mailinator.com\",\"reference\":\"FP-20260418140955-1-35B9DB\",\"method\":\"orange\",\"callback_url\":\"http:\\/\\/ohnous.new.local\\/paiement-callback-freshpay\"}', '', '', 'initiated', 'pending', 'Paiement initié côté OhNous, en attente de la confirmation FreshPay.', '2026-04-18 13:09:55', '2026-04-18 13:09:55');

-- --------------------------------------------------------

--
-- Structure de la table `tailles`
--

DROP TABLE IF EXISTS `tailles`;
CREATE TABLE IF NOT EXISTS `tailles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` text COLLATE utf8mb4_general_ci,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `date_ajout` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tailles`
--

INSERT INTO `tailles` (`id`, `nom`, `slug`, `commentaire`, `date_ajout`) VALUES
(1, 'S', 's', NULL, '2025-10-19 15:18:40'),
(2, 'L', 'l', NULL, '2025-10-19 15:18:40'),
(3, 'M', 'm', NULL, '2025-10-19 15:18:40'),
(4, 'XL', 'xl', NULL, '2025-10-19 15:18:40'),
(5, '2XL', '2xl', NULL, '2025-10-19 15:18:40'),
(6, 'Micro / Mini', 'micro-mini', '< 5 L', '2025-12-20 13:03:24'),
(7, 'Petit(e)', 'petit-e', '5 - 15 L', '2025-12-20 13:03:24'),
(8, 'Moyen(ne)', 'moyen-ne', '15 - 30 L', '2025-12-20 13:03:24'),
(9, 'Grand(e)', 'grand-e', '30 - 50 L', '2025-12-20 13:03:24'),
(10, 'Extra Grand(e)', 'extra-grand-e', '50 L et +', '2025-12-20 13:03:24'),
(209, '36', '36', 'Pointure EU', '2026-01-05 14:24:50'),
(210, '37', '37', 'Pointure EU', '2026-01-05 14:24:50'),
(211, '38', '38', 'Pointure EU', '2026-01-05 14:24:50'),
(212, '39', '39', 'Pointure EU', '2026-01-05 14:24:50'),
(213, '40', '40', 'Pointure EU', '2026-01-05 14:24:50'),
(214, '41', '41', 'Pointure EU', '2026-01-05 14:24:50'),
(215, '42', '42', 'Pointure EU', '2026-01-05 14:24:50'),
(216, '43', '43', 'Pointure EU', '2026-01-05 14:24:50'),
(217, '44', '44', 'Pointure EU', '2026-01-05 14:24:50'),
(218, '45', '45', 'Pointure EU', '2026-01-05 14:24:50'),
(219, '46', '46', 'Pointure EU', '2026-01-05 14:24:50'),
(220, '5.5', '5-5', 'Pointure US', '2026-01-05 14:24:50'),
(221, '6', '6', 'Pointure US', '2026-01-05 14:24:50'),
(222, '6.5', '6-5', 'Pointure US', '2026-01-05 14:24:50'),
(223, '7.5', '7-5', 'Pointure US', '2026-01-05 14:24:50'),
(224, '8.5', '8-5', 'Pointure US', '2026-01-05 14:24:50'),
(225, '9.5', '9-5', 'Pointure US', '2026-01-05 14:24:50'),
(226, '10', '10', 'Pointure US', '2026-01-05 14:24:50'),
(227, '11', '11', 'Pointure US', '2026-01-05 14:24:50'),
(228, '12', '12', 'Pointure US', '2026-01-05 14:24:50'),
(229, '13', '13', 'Pointure US', '2026-01-05 14:24:50'),
(230, '14', '14', 'Pointure US', '2026-01-05 14:24:50'),
(231, '3.5', '3-5', 'Pointure UK', '2026-01-05 14:24:50'),
(232, '4', '4', 'Pointure UK', '2026-01-05 14:24:50'),
(233, '5', '5', 'Pointure UK', '2026-01-05 14:24:50'),
(234, '6', '6-1', 'Pointure UK', '2026-01-05 14:24:50'),
(235, '6.5', '6-5-1', 'Pointure UK', '2026-01-05 14:24:50'),
(236, '7.5', '7-5-1', 'Pointure UK', '2026-01-05 14:24:50'),
(237, '8', '8', 'Pointure UK', '2026-01-05 14:24:50'),
(238, '9', '9', 'Pointure UK', '2026-01-05 14:24:50'),
(239, '10', '10-1', 'Pointure UK', '2026-01-05 14:24:50'),
(240, '11', '11-1', 'Pointure UK', '2026-01-05 14:24:50'),
(241, '12', '12-1', 'Pointure UK', '2026-01-05 14:24:50'),
(242, 'XS', 'xs', 'Taille', '2026-01-07 01:25:36'),
(243, 'S', 's-1', 'Taille', '2026-01-07 01:25:36'),
(244, 'S/M', 's-m', 'Taille', '2026-01-07 01:25:36'),
(245, 'M', 'm-1', 'Taille', '2026-01-07 01:25:36'),
(246, 'L', 'l-1', 'Taille', '2026-01-07 01:25:36'),
(247, 'L/XL', 'l-xl', 'Taille', '2026-01-07 01:25:36'),
(248, 'XL', 'xl-1', 'Taille', '2026-01-07 01:25:36'),
(249, '2XL', '2xl-1', 'Taille', '2026-01-07 01:25:36'),
(250, '2', '2', 'Taille US', '2026-01-07 01:25:36'),
(251, '4', '4-1', 'Taille US', '2026-01-07 01:25:36'),
(252, '6', '6-2', 'Taille US', '2026-01-07 01:25:36'),
(253, '8', '8-1', 'Taille US', '2026-01-07 01:25:36'),
(254, '10', '10-2', 'Taille US', '2026-01-07 01:25:36'),
(255, '12', '12-2', 'Taille US', '2026-01-07 01:25:36'),
(256, '14', '14-1', 'Taille US', '2026-01-07 01:25:36'),
(257, '16', '16', 'Taille US', '2026-01-07 01:25:36'),
(258, '18', '18', 'Taille US', '2026-01-07 01:25:36'),
(259, '20', '20', 'Taille US', '2026-01-07 01:25:36'),
(260, '34', '34', 'Taille EU', '2026-01-07 01:25:36'),
(261, '36', '36-1', 'Taille EU', '2026-01-07 01:25:36'),
(262, '38', '38-1', 'Taille EU', '2026-01-07 01:25:36'),
(263, '40', '40-1', 'Taille EU', '2026-01-07 01:25:36'),
(264, '42', '42-1', 'Taille EU', '2026-01-07 01:25:36'),
(265, '44', '44-1', 'Taille EU', '2026-01-07 01:25:36'),
(266, '46', '46-1', 'Taille EU', '2026-01-07 01:25:36'),
(267, '48', '48', 'Taille EU', '2026-01-07 01:25:36'),
(268, 'XXS', 'xxs', 'Taille', '2026-01-07 01:25:36'),
(269, '0', '0', 'Taille US', '2026-01-07 01:25:36'),
(270, '32', '32', 'Taille EU', '2026-01-07 01:25:36'),
(271, '47', '47', 'Pointure EU', '2026-01-05 14:24:50'),
(272, '48', '48-1', 'Pointure EU', '2026-01-05 14:24:50'),
(273, '7', '7', 'Pointure US', '2026-01-05 14:24:50'),
(274, '8', '8-2', 'Pointure US', '2026-01-05 14:24:50'),
(275, '10.5', '10-5', 'Pointure US', '2026-01-05 14:24:50'),
(276, '11.5', '11-5', 'Pointure US', '2026-01-05 14:24:50'),
(277, '12.5', '12-5', 'Pointure US', '2026-01-05 14:24:50'),
(278, '13.5', '13-5', 'Pointure US', '2026-01-05 14:24:50'),
(279, '14.5', '14-5', 'Pointure US', '2026-01-05 14:24:50'),
(280, '5.5', '5-5-1', 'Pointure UK', '2026-01-05 14:24:50'),
(281, '13', '13-1', 'Pointure UK', '2026-01-05 14:24:50'),
(282, '14', '14-2', 'Pointure UK', '2026-01-05 14:24:50'),
(283, '51–54 cm', '51-54-cm', 'Circonférence (cm)', '2026-01-09 04:08:27'),
(284, '55–57 cm', '55-57-cm', 'Circonférence (cm)', '2026-01-09 04:08:27'),
(285, '58–61 cm', '58-61-cm', 'Circonférence (cm)', '2026-01-09 04:08:27'),
(286, '20–21.5\"', '20-21-5', 'Circonférence (pouces)', '2026-01-09 04:08:27'),
(287, '21.5–22.5\"', '21-5-22-5', 'Circonférence (pouces)', '2026-01-09 04:08:27'),
(288, '22.5–24\"', '22-5-24', 'Circonférence (pouces)', '2026-01-09 04:08:27');

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
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(161, 261, 49, '2026-01-07 01:35:58'),
(162, 212, 42, '2026-01-09 03:09:33'),
(163, 213, 42, '2026-01-09 03:09:33'),
(164, 214, 42, '2026-01-09 03:09:33'),
(165, 215, 42, '2026-01-09 03:09:33'),
(166, 216, 42, '2026-01-09 03:09:33'),
(167, 217, 42, '2026-01-09 03:09:33'),
(168, 218, 42, '2026-01-09 03:09:33'),
(169, 219, 42, '2026-01-09 03:09:33'),
(170, 271, 42, '2026-01-09 03:09:33'),
(171, 272, 42, '2026-01-09 03:09:33'),
(172, 221, 42, '2026-01-09 03:09:33'),
(173, 273, 42, '2026-01-09 03:09:33'),
(174, 274, 42, '2026-01-09 03:09:33'),
(175, 224, 42, '2026-01-09 03:09:33'),
(176, 225, 42, '2026-01-09 03:09:33'),
(177, 275, 42, '2026-01-09 03:09:33'),
(178, 276, 42, '2026-01-09 03:09:33'),
(179, 277, 42, '2026-01-09 03:09:33'),
(180, 278, 42, '2026-01-09 03:09:33'),
(181, 279, 42, '2026-01-09 03:09:33'),
(182, 280, 42, '2026-01-09 03:09:33'),
(183, 235, 42, '2026-01-09 03:09:33'),
(184, 236, 42, '2026-01-09 03:09:33'),
(185, 237, 42, '2026-01-09 03:09:33'),
(186, 238, 42, '2026-01-09 03:09:33'),
(187, 239, 42, '2026-01-09 03:09:33'),
(188, 240, 42, '2026-01-09 03:09:33'),
(189, 241, 42, '2026-01-09 03:09:33'),
(190, 281, 42, '2026-01-09 03:09:33'),
(191, 282, 42, '2026-01-09 03:09:33'),
(192, 212, 51, '2026-01-09 03:09:33'),
(193, 213, 51, '2026-01-09 03:09:33'),
(194, 214, 51, '2026-01-09 03:09:33'),
(195, 215, 51, '2026-01-09 03:09:33'),
(196, 216, 51, '2026-01-09 03:09:33'),
(197, 217, 51, '2026-01-09 03:09:33'),
(198, 218, 51, '2026-01-09 03:09:33'),
(199, 219, 51, '2026-01-09 03:09:33'),
(200, 271, 51, '2026-01-09 03:09:33'),
(201, 272, 51, '2026-01-09 03:09:33'),
(202, 221, 51, '2026-01-09 03:09:33'),
(203, 273, 51, '2026-01-09 03:09:33'),
(204, 274, 51, '2026-01-09 03:09:33'),
(205, 224, 51, '2026-01-09 03:09:33'),
(206, 225, 51, '2026-01-09 03:09:33'),
(207, 275, 51, '2026-01-09 03:09:33'),
(208, 276, 51, '2026-01-09 03:09:33'),
(209, 277, 51, '2026-01-09 03:09:33'),
(210, 278, 51, '2026-01-09 03:09:33'),
(211, 279, 51, '2026-01-09 03:09:33'),
(212, 280, 51, '2026-01-09 03:09:33'),
(213, 235, 51, '2026-01-09 03:09:33'),
(214, 236, 51, '2026-01-09 03:09:33'),
(215, 237, 51, '2026-01-09 03:09:33'),
(216, 238, 51, '2026-01-09 03:09:33'),
(217, 239, 51, '2026-01-09 03:09:33'),
(218, 240, 51, '2026-01-09 03:09:33'),
(219, 241, 51, '2026-01-09 03:09:33'),
(220, 281, 51, '2026-01-09 03:09:33'),
(221, 282, 51, '2026-01-09 03:09:33'),
(222, 283, 52, '2026-01-09 04:27:04'),
(223, 284, 52, '2026-01-09 04:27:04'),
(224, 285, 52, '2026-01-09 04:27:04'),
(225, 286, 52, '2026-01-09 04:27:04'),
(226, 287, 52, '2026-01-09 04:27:04'),
(227, 288, 52, '2026-01-09 04:27:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(86, 262, 115, '2026-01-07 23:34:10'),
(87, 215, 138, '2026-01-09 03:20:13'),
(88, 216, 139, '2026-01-09 03:21:10'),
(89, 216, 141, '2026-01-09 03:38:49'),
(90, 216, 143, '2026-01-09 03:40:16'),
(91, 216, 145, '2026-01-09 03:42:05'),
(92, 218, 147, '2026-01-09 03:43:36'),
(93, 217, 148, '2026-01-09 03:44:36'),
(94, 217, 149, '2026-01-09 03:46:03'),
(95, 218, 150, '2026-01-09 03:46:59'),
(96, 7, 152, '2026-01-09 03:54:13'),
(97, 287, 155, '2026-01-09 04:29:33'),
(98, 286, 156, '2026-01-09 04:30:14'),
(99, 287, 157, '2026-01-09 04:31:46'),
(100, 288, 158, '2026-01-09 04:32:47'),
(101, 287, 159, '2026-01-09 04:39:26'),
(102, 286, 160, '2026-01-09 04:40:16'),
(103, 287, 161, '2026-01-09 04:41:02'),
(104, 287, 162, '2026-01-09 04:41:40'),
(105, 288, 163, '2026-01-09 04:51:34'),
(106, 287, 164, '2026-01-09 04:52:13'),
(107, 242, 165, '2026-01-09 04:59:04'),
(108, 242, 166, '2026-01-09 04:59:49'),
(109, 242, 167, '2026-01-09 05:01:12'),
(110, 243, 168, '2026-01-09 05:04:02'),
(111, 243, 169, '2026-01-09 05:05:05'),
(112, 243, 170, '2026-01-09 05:06:40'),
(113, 243, 171, '2026-01-09 05:07:51'),
(114, 242, 172, '2026-01-09 05:18:05'),
(115, 243, 173, '2026-01-09 05:19:13'),
(116, 243, 174, '2026-01-09 05:20:11'),
(117, 248, 175, '2026-01-09 05:21:54'),
(118, 243, 176, '2026-01-09 05:24:59'),
(119, 248, 177, '2026-01-09 05:25:50'),
(120, 248, 178, '2026-01-09 05:27:35'),
(121, 285, 198, '2026-04-20 20:15:44'),
(124, 285, 199, '2026-04-22 07:20:14');

-- --------------------------------------------------------

--
-- Structure de la table `types`
--

DROP TABLE IF EXISTS `types`;
CREATE TABLE IF NOT EXISTS `types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` text COLLATE utf8mb4_general_ci,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `types`
--

INSERT INTO `types` (`id`, `nom`, `slug`, `date_ajout`) VALUES
(1, 'T-shirts & polos', 't-shirts-polos', '2025-12-14 07:31:02'),
(2, 'Chemises', 'chemises', '2025-12-14 07:31:02'),
(3, 'Pantalons & jeans', 'pantalons-jeans', '2025-12-14 07:31:02'),
(4, 'Vestes & manteaux', 'vestes-manteaux', '2025-12-14 07:31:02'),
(5, 'Accessoires', 'accessoires', '2025-12-14 07:31:02'),
(6, 'Robe', 'robe', '2025-12-14 07:31:02'),
(7, 'Tops & blouses', 'tops-blouses', '2025-12-14 07:31:02'),
(8, 'Jupes', 'jupes', '2025-12-14 07:31:02'),
(9, 'Pantalons & jeans', 'pantalons-jeans-1', '2025-12-14 07:31:02'),
(10, 'Vestes & manteaux', 'vestes-manteaux-1', '2025-12-14 07:31:02'),
(12, 'Bébé (0–2 ans)', 'b-eb-e-0-2-ans', '2025-12-14 07:31:02'),
(13, 'Garçon', 'garcon', '2025-12-14 07:31:02'),
(14, 'Fille', 'fille', '2025-12-14 07:31:02'),
(15, 'Chaussures enfants', 'chaussures-enfants', '2025-12-14 07:31:02'),
(16, 'Accessoires scolaires', 'accessoires-scolaires', '2025-12-14 07:31:02'),
(17, 'Homme', 'homme-1', '2025-12-14 07:31:02'),
(18, 'Femme', 'femme-1', '2025-12-14 07:31:02'),
(19, 'Enfant', 'enfant-1', '2025-12-14 07:31:02'),
(20, 'Sport', 'sport', '2025-12-14 07:31:02'),
(21, 'Teint (fond de teint, poudre, correcteur)', 'teint-fond-de-teint-poudre-correcteur', '2025-12-14 07:31:02'),
(22, 'Yeux (mascara, eyeliner, fards)', 'yeux-mascara-eyeliner-fards', '2025-12-14 07:31:02'),
(23, 'Lèvres (rouges à lèvres, gloss)', 'l-evres-rouges-a-l-evres-gloss', '2025-12-14 07:31:02'),
(24, 'Ongles (vernis, soins)', 'ongles-vernis-soins', '2025-12-14 07:31:02'),
(25, 'Nettoyants & démaquillants', 'nettoyants-d-emaquillants', '2025-12-14 07:31:02'),
(26, 'Crèmes hydratantes', 'cr-emes-hydratantes', '2025-12-14 07:31:02'),
(27, 'Sérums & masques', 's-erums-masques', '2025-12-14 07:31:02'),
(28, 'Anti-âge', 'anti-age', '2025-12-14 07:31:02'),
(29, 'Crèmes & laits', 'cr-emes-laits', '2025-12-14 07:31:02'),
(30, 'Gommages', 'gommages', '2025-12-14 07:31:02'),
(31, 'Huiles & beurres', 'huiles-beurres', '2025-12-14 07:31:02'),
(32, 'Shampoings', 'shampoings', '2025-12-14 07:31:02'),
(33, 'Après-shampoings', 'apr-es-shampoings', '2025-12-14 07:31:02'),
(34, 'Masques & soins', 'masques-soins', '2025-12-14 07:31:02'),
(35, 'Produits coiffants', 'produits-coiffants', '2025-12-14 07:31:02'),
(36, 'Homme', 'homme-2', '2025-12-14 07:31:02'),
(37, 'Femme', 'femme-2', '2025-12-14 07:31:02'),
(38, 'Unisexes', 'unisexes', '2025-12-14 07:31:02'),
(40, 'Sac', 'sac', '2025-12-20 13:14:38'),
(41, 'Lunettes', 'lunettes', '2026-01-04 15:03:25'),
(42, 'Chaussure', 'chaussure', '2026-01-05 13:55:47'),
(43, 'Talon', 'talon', '2026-01-05 14:26:40'),
(47, 'Haut', 'haut', '2026-01-07 02:08:26'),
(48, 'Ensemble', 'ensemble', '2026-01-07 20:08:58'),
(49, 'Boubou', 'boubou', '2026-01-07 23:17:43'),
(50, 'Montre', 'montre', '2026-01-09 03:14:39'),
(51, 'Babouches/Sandales/Crocs', 'babouches-sandales-crocs', '2026-01-09 03:25:23'),
(52, 'Perruques', 'perruques', '2026-01-09 04:05:21'),
(53, 'Skincare', 'skincare', '2026-01-09 19:36:58'),
(55, 'Brosse à dents / dentifrice', 'brosse-a-dents-dentifrice', '2026-01-09 19:51:02');

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
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(110, 134, 5, '2026-01-09 03:11:22'),
(111, 135, 5, '2026-01-09 03:13:38'),
(112, 136, 50, '2026-01-09 03:15:39'),
(113, 137, 50, '2026-01-09 03:17:46'),
(114, 138, 42, '2026-01-09 03:20:13'),
(115, 139, 42, '2026-01-09 03:21:10'),
(116, 140, 50, '2026-01-09 03:22:10'),
(117, 141, 51, '2026-01-09 03:38:49'),
(118, 142, 50, '2026-01-09 03:39:41'),
(119, 143, 42, '2026-01-09 03:40:16'),
(120, 144, 50, '2026-01-09 03:41:06'),
(121, 145, 42, '2026-01-09 03:42:05'),
(122, 146, 50, '2026-01-09 03:42:41'),
(123, 147, 42, '2026-01-09 03:43:36'),
(124, 148, 42, '2026-01-09 03:44:36'),
(125, 149, 42, '2026-01-09 03:46:03'),
(126, 150, 42, '2026-01-09 03:46:59'),
(127, 151, 5, '2026-01-09 03:47:33'),
(128, 152, 40, '2026-01-09 03:54:13'),
(129, 153, 50, '2026-01-09 03:58:46'),
(130, 154, 5, '2026-01-09 04:00:41'),
(131, 155, 52, '2026-01-09 04:29:33'),
(132, 156, 52, '2026-01-09 04:30:14'),
(133, 157, 52, '2026-01-09 04:31:46'),
(134, 158, 52, '2026-01-09 04:32:47'),
(135, 159, 52, '2026-01-09 04:39:26'),
(136, 160, 52, '2026-01-09 04:40:16'),
(137, 161, 52, '2026-01-09 04:41:02'),
(138, 162, 52, '2026-01-09 04:41:40'),
(139, 163, 52, '2026-01-09 04:51:34'),
(140, 164, 52, '2026-01-09 04:52:13'),
(141, 165, 6, '2026-01-09 04:59:04'),
(142, 166, 6, '2026-01-09 04:59:49'),
(143, 167, 6, '2026-01-09 05:01:12'),
(144, 168, 6, '2026-01-09 05:04:02'),
(145, 169, 6, '2026-01-09 05:05:05'),
(146, 170, 6, '2026-01-09 05:06:40'),
(147, 171, 6, '2026-01-09 05:07:51'),
(148, 172, 6, '2026-01-09 05:18:05'),
(149, 173, 48, '2026-01-09 05:19:13'),
(150, 174, 47, '2026-01-09 05:20:11'),
(151, 175, 6, '2026-01-09 05:21:54'),
(152, 176, 6, '2026-01-09 05:24:59'),
(153, 177, 6, '2026-01-09 05:25:50'),
(154, 178, 6, '2026-01-09 05:27:35'),
(155, 179, 53, '2026-01-09 19:38:31'),
(156, 180, 27, '2026-01-09 19:39:37'),
(157, 181, 5, '2026-01-09 19:42:30'),
(158, 182, 5, '2026-01-09 19:43:28'),
(159, 183, 27, '2026-01-09 19:45:11'),
(160, 184, 25, '2026-01-09 19:46:01'),
(161, 185, 26, '2026-01-09 19:47:33'),
(162, 186, 55, '2026-01-09 19:53:46'),
(163, 187, 27, '2026-01-09 20:07:29'),
(164, 188, 26, '2026-01-09 20:08:16'),
(165, 189, 27, '2026-01-09 20:09:13'),
(166, 190, 21, '2026-01-09 20:12:39'),
(167, 191, 22, '2026-01-09 20:13:41'),
(168, 192, 21, '2026-01-09 20:14:24'),
(169, 193, 21, '2026-01-09 20:15:16'),
(170, 194, 21, '2026-01-09 20:15:55'),
(171, 195, 21, '2026-01-09 20:18:10'),
(172, 196, 22, '2026-04-20 19:41:27'),
(173, 197, 30, '2026-04-20 20:15:09'),
(174, 198, 52, '2026-04-20 20:15:44'),
(177, 199, 52, '2026-04-22 07:20:14');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unique_id` text,
  `nom` text,
  `adresse_email` text,
  `mdp` text,
  `code_password` text,
  `description` text,
  `slug` text,
  `profile` text,
  `fileId` text,
  `field` text,
  `backgrounds` text,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `unique_id`, `nom`, `adresse_email`, `mdp`, `code_password`, `description`, `slug`, `profile`, `fileId`, `field`, `backgrounds`, `date_ajout`) VALUES
(2, 'user_69977a0abf5c02.91140663', 'edo système', 'edosysteme@gmail.com', '$2y$12$WMLOHHAnipXsH7fjL/5SU..trMyMHOgzUEH.SblpqplNeJcu4UOpC', NULL, NULL, 'edo-syst-eme', 'https://ik.imagekit.io/nyombi1997/OhNous/profile/edo-syst-eme_1771543503284_ZH64dn5pF.webp', '69979bd05c7cd75eb863c8b8', NULL, 'rgb(201, 174, 165)', '2026-02-19 22:00:59'),
(3, 'user_69ad48b37eb757.40001033', 'Illo quae saepe corp', 'fajutuz@mailinator.com', '$2y$12$7wObPqTvE1fOH33hk16LSO7jysTValbZD8Sn3aTEYpZejo4tukDmC', NULL, NULL, 'illo-quae-saepe-corp', NULL, NULL, NULL, NULL, '2026-03-08 11:00:19'),
(4, 'user_69db7adc9ee7e9.15116718', 'Dolores corrupti el', 'liquidvacancy@gmail.com', '$2y$12$OQ.adjV3vVTy5MqFqa5tC.HegptvyLIK34P/fH436h3NQTPKBy7tG', NULL, NULL, 'dolores-corrupti-el', NULL, NULL, NULL, NULL, '2026-04-12 11:58:36');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `relation_boutique` FOREIGN KEY (`boutique`) REFERENCES `boutiques` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
