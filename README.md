# Checkout, FreshPay, filtres, formulaires et multi-admin

## Correctifs FreshPay production du 18 avril 2026

- Mode FreshPay par défaut passé en `production`.
- Endpoint d’initiation et de vérification aligné sur `https://paydrc.gofreshbakery.net/api/v5/`.
- Requêtes FreshPay envoyées en `json`.
- Action d’initiation corrigée en `debit`.
- Action de vérification corrigée en `verify`.
- Payload FreshPay aligné sur le contrat communiqué par FreshPay, avec conservation de l’envoi en `USD`, champ `email`, `callback_url` vide et profil client FreshPay figé.
- Le statut réel du paiement repose désormais sur `Trans_Status`, pas sur `Status`.
- Callback durci avec lecture JSON, signature `X-Signature`, HMAC SHA-256 et déchiffrement configurable.
- Méthodes Mobile Money alignées côté config et checkout : `airtel`, `orange`, `mpesa`, `afrimoney`.
- Visa laissé désactivé avec commentaire `TODO FreshPay`.

## Variables FreshPay production

```env
FRESHPAY_MODE=production
FRESHPAY_MERCHANT_ID=jV]M|@2gr{b+G])6b
FRESHPAY_MERCHANT_SECRET=jz5epFB9Z2xfr!nNJb
FRESHPAY_SECRET_KEY4=4357975872d4498e
FRESHPAY_HMAC_KEY4=2f76bc4319f04357
FRESHPAY_PROD_INITIATE_URL=https://paydrc.gofreshbakery.net/api/v5/
FRESHPAY_PROD_STATUS_URL=https://paydrc.gofreshbakery.net/api/v5/
FRESHPAY_REQUEST_FORMAT=json
FRESHPAY_HTTP_TIMEOUT=20
FRESHPAY_HTTP_CONNECT_TIMEOUT=10
FRESHPAY_CALLBACK_DECRYPT_MODE=aes
FRESHPAY_CALLBACK_DECRYPT_CIPHER=AES-128-CBC
FRESHPAY_CALLBACK_URL=https://ohnous.store/payments/freshpay/callback
FRESHPAY_METHOD_AIRTEL=airtel
FRESHPAY_METHOD_ORANGE=orange
FRESHPAY_METHOD_MPESA=mpesa
FRESHPAY_METHOD_AFRIMONEY=afrimoney
```

## À compléter manuellement pour la production

- Configurer réellement `FRESHPAY_CALLBACK_URL` sur l’URL publique qui reçoit le callback.
- Vérifier que l’URL exposée côté FreshPay pointe bien vers la route MVC active `/paiement-callback-freshpay` ou ajouter la redirection serveur nécessaire.
- Whitelister les IP callback FreshPay si l’environnement serveur le permet.
- Confirmer avec FreshPay si `afrimoney` ou `africell` est la valeur finale attendue en production.
- Confirmer avec FreshPay si le callback de production doit rester en `AES-128-CBC` ou passer en `AES-256-CBC`.

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
- `/admin-paiements`

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

## Ou creer ces variables

Ce projet ne charge pas automatiquement un fichier `.env`.
`config/payment.php` utilise directement `getenv(...)`.

Concretement, pour que le systeme fonctionne, il faut definir ces variables dans l'environnement PHP du serveur qui execute le site.

Cas les plus frequents :

- hebergement Apache mutualise : dans `.htaccess` avec `SetEnv`
- serveur Apache/VPS : dans le VirtualHost Apache
- panel d'hebergement : dans la zone `Environment Variables` si ton hebergeur la propose

Exemple dans `.htaccess` :

```apache
SetEnv FRESHPAY_MODE test
SetEnv FRESHPAY_SECRET_KEY4 4357975872d4498e
SetEnv FRESHPAY_HMAC_KEY4 2f76bc4319f04357
SetEnv FRESHPAY_MERCHANT_ID your_merchant_id
SetEnv FRESHPAY_MERCHANT_SECRET your_merchant_secret

SetEnv FRESHPAY_TEST_INITIATE_URL https://sandbox.example.com/initiate
SetEnv FRESHPAY_TEST_STATUS_URL https://sandbox.example.com/status
SetEnv FRESHPAY_PROD_INITIATE_URL https://api.example.com/initiate
SetEnv FRESHPAY_PROD_STATUS_URL https://api.example.com/status

SetEnv FRESHPAY_METHOD_MOBILE_MONEY mobile_money
SetEnv FRESHPAY_METHOD_VISA visa

SetEnv FRESHPAY_HTTP_TIMEOUT 20
SetEnv FRESHPAY_HTTP_CONNECT_TIMEOUT 10
SetEnv FRESHPAY_REQUEST_FORMAT form

SetEnv FRESHPAY_CALLBACK_SIGNATURE_FIELD signature
SetEnv FRESHPAY_CALLBACK_ENCRYPTED_FIELD data
SetEnv FRESHPAY_CALLBACK_STATUS_FIELD Status
SetEnv FRESHPAY_CALLBACK_TRANS_STATUS_FIELD Trans_Status
SetEnv FRESHPAY_CALLBACK_DESCRIPTION_FIELD Trans_Status_Description
SetEnv FRESHPAY_CALLBACK_TRANSACTION_ID_FIELD TransactionId
SetEnv FRESHPAY_CALLBACK_FINANCIAL_INSTITUTION_ID_FIELD FinancialInstitutionId
SetEnv FRESHPAY_CALLBACK_DECRYPT_MODE plain_json

SetEnv FRESHPAY_ENABLE_VISA 0
SetEnv FRESHPAY_VISA_SHARED_ENDPOINT 1
```

Exemple dans la configuration Apache du site :

```apache
<VirtualHost *:80>
    ServerName ohnous.store
    DocumentRoot /var/www/ohnous

    SetEnv FRESHPAY_MODE test
    SetEnv FRESHPAY_SECRET_KEY4 4357975872d4498e
    SetEnv FRESHPAY_HMAC_KEY4 2f76bc4319f04357
    SetEnv FRESHPAY_MERCHANT_ID your_merchant_id
    SetEnv FRESHPAY_MERCHANT_SECRET your_merchant_secret
</VirtualHost>
```

Si tu developpes en local sous Windows :

1. N'ecris pas seulement un fichier `.env`, car il ne sera pas lu tout seul.
2. Definis les variables dans Apache, dans ton terminal avant de lancer PHP, ou ajoute ensuite un vrai chargeur `.env` au projet.

Verification rapide :

```php
var_dump(getenv('FRESHPAY_MODE'));
```

Si cette ligne retourne `false` ou une chaine vide, PHP ne voit pas encore ta variable d'environnement.

## Important sur FreshPay

- Le total envoyé est bien calculé ainsi : `(sous_total + frais_livraison) + 10 %`.
- La devise envoyée à FreshPay est `USD`.
- Les champs FreshPay `merchant_id`, `merchant_secrete`, `firstname`, `lastname` et `email` sont figés dans `config/payment.php` selon les valeurs validées par FreshPay.
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
```

Si une colonne existe déjà, ne relance pas sa ligne `ADD COLUMN`.

## Gestion paiements du 9 juillet 2026

- Ajout automatique de 10 % sur le montant HT au checkout.
- Affichage checkout : sous-total, livraison, TVA / frais 10 %, total TTC.
- Historique admin disponible sur `/admin-paiements` avec recherche, filtres, détail et export CSV.
- Enregistrement des références internes, références prestataire, identifiants de transaction, montants HT/frais/total et payloads FreshPay.
- Reçu email envoyé uniquement après confirmation d’un statut réussi FreshPay.
- Messages Mobile Money détaillés conservés quand FreshPay renvoie une description ou un code d'erreur.

## Gestion paiements et PayOut du 14 juillet 2026

- Rapport paiements pleine largeur et page détail dédiée `/admin-paiement-details?id={id}`.
- Dashboard de détail avec ApexCharts et articles achetés.
- Formulaire PayOut `/admin-payout`, suivi temps réel `/admin-payout-suivi?reference={reference}`, historique `/admin-payouts` et détail `/admin-payout-details?id={id}`.
- Les numéros PayOut sont validés par `intl-tel-input` puis enregistrés au format international E.164.
- Les tables `payout_transactions`, `payout_status_history`, `payout_audit_log` et la permission `admins.can_payout` doivent être créées avec le SQL ci-dessus (ou le bloc de `update_bdd.txt`).
- L'action PayOut FreshPay vaut `credit` par défaut et peut être remplacée avec `FRESHPAY_PAYOUT_ACTION` si le contrat FreshPay de production exige une autre valeur.

## Correctif suivi PayOut FreshPay

Le Check Status FreshPay attend dans `reference` le `Transaction_id` (`PD...`) retourné à l'initiation, et non la référence interne OHNOUS (`PO-...`). Le numéro reste enregistré en E.164 mais est envoyé à FreshPay sans le signe `+`. Le PayOut transmet `https://ohnous.store/payments/freshpay/callback`, surchargeable avec `FRESHPAY_PAYOUT_CALLBACK_URL`. Active temporairement le journal et les payloads administrateur avec `FRESHPAY_PAYOUT_DEBUG=1`; le fichier produit est `logs/freshpay-payout-debug.log` et les secrets y sont masqués.

Le dump `u577654037_ohnous(20).sql` ne contient pas toutes les colonnes utilisées par le module ni les tables de suivi. Exécuter une seule fois dans phpMyAdmin si elles sont absentes :

```sql
ALTER TABLE payout_transactions
  ADD COLUMN error_detail TEXT NULL AFTER status_description,
  ADD COLUMN operator_reference VARCHAR(190) NULL AFTER freshpay_reference,
  ADD COLUMN admin_id INT NOT NULL DEFAULT 0 AFTER transaction_id,
  ADD COLUMN admin_name VARCHAR(190) NULL AFTER admin_id;

CREATE TABLE IF NOT EXISTS payout_status_history (
  id INT AUTO_INCREMENT PRIMARY KEY, payout_id INT NOT NULL, status VARCHAR(40) NOT NULL,
  description TEXT NULL, source VARCHAR(40) NOT NULL DEFAULT 'system', payload LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_payout_history (payout_id, created_at)
);

CREATE TABLE IF NOT EXISTS payout_audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY, payout_id INT NOT NULL, admin_id INT NOT NULL DEFAULT 0,
  admin_name VARCHAR(190) NULL, action VARCHAR(80) NOT NULL, amount DECIMAL(15,2) NOT NULL,
  currency VARCHAR(3) NOT NULL, phone_number VARCHAR(32) NOT NULL, operator VARCHAR(30) NOT NULL,
  ip_address VARCHAR(64) NULL, user_agent VARCHAR(500) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payout_audit (payout_id, created_at)
);
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

## SQL activation compte boutique

```sql
```
# Protection Honeypot des formulaires publics

La protection antibot est centralisée dans `fonctions/honeypot.php`, chargé automatiquement par `fonctions/fonctions.php`. Elle associe à chaque formulaire un champ leurre hors écran, un jeton aléatoire conservé en session, l’heure de création côté serveur et le token CSRF existant. Une soumission est neutralisée avant tout traitement métier si le champ leurre est rempli, si le jeton ou le CSRF est invalide, si le formulaire a expiré ou s’il est envoyé en moins d’une seconde.

Les blocages sont écrits au format JSON, une ligne par événement, dans `logs/security-honeypot.log`. Le journal contient uniquement la date, l’heure, le formulaire, la route, l’adresse IP, le User-Agent et la raison. Il ne contient aucune donnée de formulaire, aucun mot de passe et aucun token. Le fichier peut être archivé ou supprimé périodiquement selon la politique de conservation du serveur.

## Ajouter la protection à un formulaire

Dans la vue, à l’intérieur du formulaire :

```php
<?php renderHoneypot('nom_unique_du_formulaire'); ?>
```

Dans le point d’entrée PHP, immédiatement après le chargement de `fonctions.php` et avant toute lecture métier, écriture, API ou envoi d’e-mail :

```php
if (!validateHoneypot('nom_unique_du_formulaire')) {
    ohnous_honeypot_neutral_json();
}
```

Le nom doit être strictement identique des deux côtés. Avec AJAX, transmettre le formulaire avec `$(form).serialize()` afin d’inclure automatiquement `website_contact`, `ohnous_hp_token` et `csrf_token`. Pour un formulaire construit en JavaScript, sérialiser le conteneur des champs de sécurité et concaténer les champs métier encodés.

## Tester

1. Charger le formulaire, attendre au moins deux secondes et le soumettre normalement : le traitement existant doit continuer.
2. Renseigner `website_contact` depuis les outils développeur puis soumettre : la réponse doit rester neutre et aucune action métier ne doit être exécutée.
3. Soumettre immédiatement après le chargement, supprimer `ohnous_hp_token`, modifier `csrf_token` ou réutiliser un jeton expiré : la requête doit être neutralisée.
4. Vérifier que `logs/security-honeypot.log` contient uniquement les métadonnées de sécurité prévues.
