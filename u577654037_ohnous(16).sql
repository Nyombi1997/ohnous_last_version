-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 20 avr. 2026 à 18:48
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
