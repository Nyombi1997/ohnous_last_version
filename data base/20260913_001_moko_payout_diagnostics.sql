-- Objectif : synchronisation des bénéficiaires et diagnostic des échanges PayOut.
-- Prérequis : tables du dump u577654037_ohnous(3).sql, sans importer ses données.
-- Compatibilité : MariaDB 10.6+ (dump fourni : MariaDB 11.8.9).
-- Ordre : après les migrations 20260907_001 et 20260908_001 si installation ancienne.
-- Importer dans la base sélectionnée dans phpMyAdmin. Rejouable, aucune suppression.
ALTER TABLE moko_recipients
 ADD COLUMN IF NOT EXISTS country CHAR(2) NOT NULL DEFAULT 'CD',
 ADD COLUMN IF NOT EXISTS last_sync_at DATETIME NULL,
 ADD COLUMN IF NOT EXISTS last_api_exchange LONGTEXT NULL;

ALTER TABLE payout_transactions
 ADD COLUMN IF NOT EXISTS last_api_exchange LONGTEXT NULL,
 ADD COLUMN IF NOT EXISTS finalized_at DATETIME NULL;
