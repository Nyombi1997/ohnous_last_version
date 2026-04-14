# Checkout, FreshPay, filtres, formulaires et multi-admin

## Ce qui a été intégré

- Loader visuel pendant le chargement des articles dans l’espace shop.
- Nouveau tri catalogue `Plus chers aux moins chers`, avec prise en charge côté front et côté backend.
- Refonte des inputs checkout et admin livraison avec un rendu plus moderne et un texte minimum de `16px`.
- Textarea du checkout non redimensionnable manuellement, avec hauteur automatique selon le texte.
- Checkout enrichi avec choix de méthode de paiement, Mobile Money opérationnel côté architecture, Visa préparé proprement.
- Création d’un module FreshPay structuré avec configuration séparée, services, modèles, contrôleurs et page de retour.
- Vérification de statut FreshPay via route dédiée.
- Callback FreshPay avec vérification HMAC et architecture prête pour le déchiffrement documenté.
- Nouvelle page admin dédiée pour créer d’autres admins.
- Génération automatique de mots de passe admins.
- Envoi d’un email d’invitation admin avec lien d’accès direct à usage unique.
- Avatar admin par défaut basculé vers `/asset/images/icons/favicon-1.png`.

## Architecture ajoutée

- `config/payment.php`
- `controller/CheckoutController.php`
- `controller/PaymentController.php`
- `controller/AdminController.php`
- `service/FreshPayService.php`
- `service/OrderAmountService.php`
- `model/PaymentTransaction.php`
- `model/AdminAccessToken.php`
- `view/admin-admins.php`
- `view/payment-return.php`
- `asset/js/admin_accounts.js`
- `fonctions/admin_accounts.php`

## Routes ajoutées

- `/checkout`
- `/admin-admins`
- `/admin-acces`
- `/paiement-demarrer`
- `/paiement-callback-freshpay`
- `/paiement-verifier`
- `/paiement-retour`

## Configuration FreshPay

Le projet lit la configuration dans `config/payment.php`, lui-même alimenté par des variables d’environnement.

Variables à définir :

```env
FRESHPAY_MODE=test
FRESHPAY_SECRET_KEY4=4357975872d4498e
FRESHPAY_HMAC_KEY4=2f76bc4319f04357
FRESHPAY_MERCHANT_ID=
FRESHPAY_MERCHANT_SECRET=

FRESHPAY_TEST_INITIATE_URL=
FRESHPAY_TEST_STATUS_URL=
FRESHPAY_PROD_INITIATE_URL=
FRESHPAY_PROD_STATUS_URL=

FRESHPAY_METHOD_MOBILE_MONEY=mobile_money
FRESHPAY_METHOD_VISA=visa

FRESHPAY_HTTP_TIMEOUT=20
FRESHPAY_HTTP_CONNECT_TIMEOUT=10
FRESHPAY_REQUEST_FORMAT=form

FRESHPAY_CALLBACK_SIGNATURE_FIELD=signature
FRESHPAY_CALLBACK_ENCRYPTED_FIELD=data
FRESHPAY_CALLBACK_STATUS_FIELD=Status
FRESHPAY_CALLBACK_TRANS_STATUS_FIELD=Trans_Status
FRESHPAY_CALLBACK_DESCRIPTION_FIELD=Trans_Status_Description
FRESHPAY_CALLBACK_TRANSACTION_ID_FIELD=TransactionId
FRESHPAY_CALLBACK_FINANCIAL_INSTITUTION_ID_FIELD=FinancialInstitutionId
FRESHPAY_CALLBACK_DECRYPT_MODE=plain_json

FRESHPAY_ENABLE_VISA=0
FRESHPAY_VISA_SHARED_ENDPOINT=1
```

## Important sur FreshPay

- Le total envoyé est bien calculé ainsi : `sous_total + frais_livraison`.
- La devise envoyée est `USD`.
- Le flux est asynchrone :
  - initiation du paiement
  - enregistrement local
  - attente callback
  - vérification possible manuelle
- Le statut métier principal exploité est `Trans_Status`.
- La signature callback est vérifiée en `HMAC SHA256`.

## Point à compléter pour FreshPay

Je n’ai pas figé dans le code des URL FreshPay inventées ni un schéma de déchiffrement hasardeux.

À compléter dans la configuration selon ta doc FreshPay finale :

- les URL exactes `test` et `production`
- le format exact de requête si FreshPay exige du JSON au lieu du `form-urlencoded`
- le mode exact de déchiffrement callback si `data` n’est pas déjà un JSON exploitable
- les paramètres finaux Visa si le flux diffère du Mobile Money

Le code est déjà structuré pour recevoir ces précisions sans refonte.

## Visa

La structure Visa est prête, mais elle reste désactivée tant que les paramètres FreshPay Visa ne sont pas confirmés.

À finaliser pour l’activer réellement :

- la valeur exacte du champ `method`
- l’éventuel endpoint dédié Visa
- les paramètres additionnels éventuels imposés par FreshPay
- le comportement exact de retour/callback Visa

## Comment tester Mobile Money

1. Configure les variables FreshPay.
2. Assure-toi que les tables SQL ci-dessous sont bien créées.
3. Configure au moins une zone de livraison dans `/admin-zones-livraison`.
4. Va sur `/checkout`.
5. Sélectionne `Mobile Money`.
6. Remplis le numéro, l’opérateur, la zone, l’adresse et l’email.
7. Clique sur `Payer maintenant`.
8. La page de retour `/paiement-retour` permettra aussi une vérification manuelle via `/paiement-verifier`.

## Callback FreshPay

URL prévue :

```text
https://ohnous.store/paiement-callback-freshpay
```

Le callback :

- valide la signature HMAC
- tente d’extraire la donnée utile
- met à jour `status`, `trans_status`, `description`
- synchronise le statut de la commande

## Vérification manuelle

Exemple :

```text
https://ohnous.store/paiement-verifier?reference=FP-XXXX
```

## SQL à coller dans phpMyAdmin

```sql
ALTER TABLE `admins`
  ADD COLUMN `nom` VARCHAR(190) NULL AFTER `email`,
  ADD COLUMN `profile` TEXT NULL AFTER `nom`,
  ADD COLUMN `created_by` INT NOT NULL DEFAULT 0 AFTER `profile`;

UPDATE `admins`
SET `profile` = '/asset/images/icons/favicon-1.png'
WHERE `profile` IS NULL OR TRIM(`profile`) = '';

CREATE TABLE IF NOT EXISTS `admin_access_tokens` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `token` VARCHAR(190) NOT NULL,
  `redirect_path` VARCHAR(255) NULL,
  `expire_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_access_admin_id` (`admin_id`),
  UNIQUE KEY `uniq_admin_access_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `provider` VARCHAR(50) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `reference` VARCHAR(120) NOT NULL,
  `freshpay_transaction_id` VARCHAR(190) NULL,
  `financial_institution_id` VARCHAR(190) NULL,
  `customer_number` VARCHAR(80) NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `request_payload` LONGTEXT NULL,
  `response_payload` LONGTEXT NULL,
  `callback_payload` LONGTEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'initiated',
  `trans_status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `trans_status_description` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_payment_reference` (`reference`),
  KEY `idx_payment_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Vérifications avant production

- renseigner toutes les variables FreshPay
- valider les endpoints test et production
- confirmer le mode exact de déchiffrement callback
- tester un callback réel FreshPay
- tester la vérification manuelle
- activer Visa uniquement après validation documentaire complète
- vérifier que les emails admins partent bien depuis ton SMTP

## Notes projet

- `model/bdd.php` n’a pas été modifié.
- Le projet reste branché sur ton routeur MVC existant.
- Les requêtes AJAX front continuent d’utiliser `jQuery`.
- Les nouveaux textes ont été réécrits en UTF-8 côté fichiers modifiés.
- Si ta base existante ne contient pas encore `payment_transactions` et `admin_access_tokens`, colle simplement le SQL ci-dessus dans phpMyAdmin.
