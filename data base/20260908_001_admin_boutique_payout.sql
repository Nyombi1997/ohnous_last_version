-- Objectif : mémoriser les coordonnées de payout et rattacher les versements aux boutiques.
-- Compatibilité : MySQL 9.1 (dump fourni), MySQL 8 et MariaDB 10.6+.
-- Prérequis : boutiques, admins et module Moko déjà installé (migration 20260907_001).
-- Importer dans la base sélectionnée dans phpMyAdmin. Migration rejouable, sans modification des données existantes.
CREATE TABLE IF NOT EXISTS boutique_payout_profiles (
 boutique_id INT NOT NULL PRIMARY KEY,
 merchant_recipient_id VARCHAR(128) NOT NULL UNIQUE,
 beneficiary VARCHAR(190) NOT NULL,
 phone_number VARCHAR(32) NOT NULL,
 operator VARCHAR(16) NOT NULL,
 kyc_reference VARCHAR(128) NULL,
 existing_recipient_id VARCHAR(64) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS boutique_payout_links (
 payout_id INT NOT NULL PRIMARY KEY,
 boutique_id INT NOT NULL,
 INDEX idx_boutique_payout (boutique_id, payout_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS admin_password_resets (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 admin_id INT NOT NULL,
 token VARCHAR(190) NOT NULL,
 expire_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
