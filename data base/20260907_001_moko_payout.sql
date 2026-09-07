-- Migration Moko Payout - 7 septembre 2026
-- Compatible MariaDB, base u577654037_ohnous fournie.
-- Dans phpMyAdmin : sélectionner la base existante, puis Importer ce fichier.
-- Prérequis : tables payout_transactions, payout_status_history et payout_audit_log existantes.
-- Sauvegarder la base avant import. Migration rejouable, sans suppression de données.

ALTER TABLE payout_transactions
 ADD COLUMN IF NOT EXISTS provider VARCHAR(20) NOT NULL DEFAULT 'freshpay',
 ADD COLUMN IF NOT EXISTS moko_recipient_id VARCHAR(64) NULL,
 ADD COLUMN IF NOT EXISTS moko_payout_id VARCHAR(64) NULL,
 ADD COLUMN IF NOT EXISTS moko_status VARCHAR(32) NULL,
 ADD COLUMN IF NOT EXISTS operator_reference VARCHAR(190) NULL,
 ADD COLUMN IF NOT EXISTS admin_id INT NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS admin_name VARCHAR(190) NULL,
 ADD COLUMN IF NOT EXISTS error_detail TEXT NULL,
 ADD COLUMN IF NOT EXISTS send_attempts INT NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS first_attempt_at DATETIME NULL,
 ADD COLUMN IF NOT EXISTS next_attempt_at DATETIME NULL,
 ADD COLUMN IF NOT EXISTS last_checked_at DATETIME NULL,
 ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL;
CREATE UNIQUE INDEX IF NOT EXISTS uq_moko_payout ON payout_transactions(moko_payout_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_payout_reference ON payout_transactions(reference);

CREATE TABLE IF NOT EXISTS moko_recipients (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 merchant_recipient_id VARCHAR(128) NOT NULL UNIQUE,
 recipient_id VARCHAR(64) NULL UNIQUE,
 full_name VARCHAR(255) NOT NULL,
 phone VARCHAR(32) NOT NULL,
 operator VARCHAR(16) NOT NULL,
 kyc_reference VARCHAR(128) NULL,
 status VARCHAR(32) NOT NULL DEFAULT 'pending_send',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS moko_webhook_events (
 id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 event_hash CHAR(64) NOT NULL UNIQUE,
 payout_id VARCHAR(64) NOT NULL,
 payload LONGTEXT NOT NULL,
 processed_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_moko_inbox (payout_id, processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
