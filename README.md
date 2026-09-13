# Checkout, FreshPay, filtres, formulaires et multi-admin

## Bénéficiaires Moko — 12 septembre 2026

Référence : [Guide marchand Moko](Moko_Payout_API_Guide_Marchand.pdf), version 1.0, sections 2.2–2.3, 8.2 et 9.2. L’enregistrement et le versement sont deux actions distinctes. Dans `/admin-payout`, sélectionner une boutique, compléter son nom de titulaire, son numéro RDC et son opérateur, puis cliquer sur « Enregistrer le bénéficiaire ». Une confirmation s’affiche après enregistrement. Les champs de coordonnées disparaissent au profit du bénéficiaire mémorisé ; seuls les détails du versement restent à saisir. « Actualiser le bénéficiaire » relit son état chez Moko. Le bouton de versement exige le statut `ACTIVE`, qui est revérifié côté serveur.

OHNOUS construit `merchant_recipient_id` avec `boutique_<id>` pour un nouveau profil et conserve les identifiants historiques. Le `recipient_id` est exclusivement fourni par Moko puis stocké dans `moko_recipients` et `boutique_payout_profiles`. Aucun de ces identifiants n’est saisi manuellement. Le PayOut utilise les coordonnées stockées côté serveur, même si une requête navigateur transmet d’autres coordonnées. La référence KYC est facultative ; la limite du nom reste à 190 caractères pour respecter la table des profils existante.

La boutique retrouve le formulaire dans **Versements** (`/boutique-versements`). Avant activation, ses coordonnées sont enregistrées localement. Les deux actions administrateur d’activation tentent ensuite l’enregistrement chez Moko si les coordonnées sont complètes. Une erreur Moko n’annule pas l’activation : le rappel non masquable reste affiché à la boutique connectée jusqu’à obtention du `recipient_id`. Les boutiques déjà actives peuvent compléter et enregistrer leur bénéficiaire depuis ce même formulaire. Aucun appel Moko n’est effectué lors du simple affichage du rappel.

Un bénéficiaire `PENDING_KYC` est bien enregistré, mais ses versements restent bloqués jusqu’à validation Moko. Les coordonnées déjà transmises ne sont pas modifiées silencieusement : les modifications sensibles se traitent chez Moko. Après une réponse incertaine, réutiliser le bouton d’enregistrement. En cas de `409`, le service recherche le même identifiant marchand dans la liste paginée, puis vérifie les coordonnées avant rattachement. Un ancien ID retournant explicitement `404 recipient_not_found` est récupéré par ce même parcours, sans changer l’identifiant marchand. Le guide ne précise pas l’enveloppe de la liste : les réponses `items`, `recipients` et les listes JSON directes sont prises en charge ; tout autre format provoque un arrêt explicite. La recherche est bornée à 100 pages de 100 éléments.

Pas de nouvelle migration SQL : réutilisation des tables de [20260907_001_moko_payout.sql](data%20base/20260907_001_moko_payout.sql) et [20260908_001_admin_boutique_payout.sql](data%20base/20260908_001_admin_boutique_payout.sql), supposées déjà installées. Ne pas réimporter la base complète. Les prérequis et différences MySQL/MariaDB de ces migrations restent décrits ci-dessous ; aucun changement de `model/bdd.php`.

La route d’enregistrement contrôle la session, limite la boutique à son propre compte et exige la permission PayOut pour un administrateur. Le Honeypot et son délai serveur sont réutilisés ; un CSRF dédié expire après deux heures et se renouvelle après succès. Les activations conservent les contrôles administrateur et reçoivent une validation CSRF de deux heures. Une tentative d’enregistrement par session toutes les trois secondes au maximum ; les verrous MySQL évitent les créations concurrentes.

Validation : `php tests/boutique_payout_test.php` utilise une base locale jetable et un transport Moko simulé. Définir au besoin `MOKO_TEST_DSN=mysql:host=127.0.0.1;port=33317;charset=utf8mb4`, `MOKO_TEST_USER` et `MOKO_TEST_PASSWORD`. La suite couvre création séparée sans paiement, identifiants serveur, doublons, coordonnées réutilisées à l’activation, données manquantes, numéro invalide, KYC et actualisation, récupération après timeout/409/404, PayOut sans ressaisie, rapports et idempotence. Aucun appel réel Moko ni versement réel n’est effectué par les tests. L’affichage tactile et la réponse réelle de la liste Moko restent à valider en recette.

Contrôle complémentaire du formulaire bénéficiaire : `php tests/moko_recipient_security_test.php` vérifie CSRF, expiration, renouvellement, Honeypot, délai serveur et soumission normale sans JavaScript. Tests exécutés localement avec PHP 8.3 et MariaDB 11.4 sur une base temporaire ; la base du site n’a pas été utilisée.

## Administrateurs et payouts par boutique — 8 septembre 2026

Dans phpMyAdmin, sélectionner la base du site puis importer [20260908_001_admin_boutique_payout.sql](data%20base/20260908_001_admin_boutique_payout.sql). Ce fichier rejouable est compatible avec MySQL 9.1 (version du dump fourni), MySQL 8 et MariaDB 10.6+. Il suppose que le module Moko existant est déjà installé. Le dump fourni ne contient pas les tables payout : cette migration complète une installation payout existante, sans importer le dump ni modifier ses données. La migration Moko antérieure est [20260907_001_moko_payout.sql](data%20base/20260907_001_moko_payout.sql) ; son utilisation de `IF NOT EXISTS` sur les colonnes exige MariaDB.

- Dans « Gestion des admins », « Réinitialiser le mot de passe » envoie au compte sélectionné un code aléatoire à usage unique valable 30 minutes. Seul son condensat est enregistré. L’administrateur saisit ce code depuis le lien de l’email puis choisit son mot de passe. Le mot de passe actuel reste valable tant que le remplacement n’a pas réussi. L’envoi est limité à une demande par minute et par compte ; la consommation est transactionnelle et invalide les autres demandes du compte. Le formulaire public conserve le Honeypot, la vérification temporelle et le CSRF existants.
- Chaque nouveau payout exige une boutique et utilise son bénéficiaire préenregistré, selon le parcours du 12 septembre ci-dessus. Les contraintes d’identité et de validation KYC Moko restent applicables.
- `/admin-payouts?boutique_id=ID` affiche le rapport de la boutique ; l’export Excel reprend le filtre boutique et les filtres de recherche. Les compteurs portent sur tous les payouts de cette boutique ; le tableau affiche les 250 plus récents correspondant aux filtres et l’export contient toutes les lignes. Les anciens payouts restent dans le rapport global : aucun rattachement n’est déduit d’un nom ou d’un téléphone.

Validation locale : `php tests/boutique_payout_test.php` teste sur MySQL local une base jetable (créée puis supprimée), le double import SQL, les profils stables, le dernier opérateur, l’isolation des rapports, les devises, l’idempotence et la réinitialisation à usage unique. Le test utilise `MOKO_TEST_USER` et `MOKO_TEST_PASSWORD` (par défaut `root` sans mot de passe), sans charger `model/bdd.php`. `php tests/moko_payout_test.php` couvre aussi les signatures et transitions Moko. Aucun email ni versement réel n’est déclenché. Vérifier en recette la réception SMTP et l’affichage mobile des formulaires et suggestions. Les formulaires admin modifiés expirent après deux heures ; le formulaire public de réinitialisation conserve son expiration Honeypot et renouvelle la session et le CSRF après réussite.

## PayOut Moko — installation du 7 septembre 2026

Cette section remplace les anciennes instructions FreshPay **pour les nouveaux PayOut uniquement**. Le checkout reste FreshPay et les anciens PayOut restent consultables et vérifiables avec leur prestataire d'origine.

Intégration fondée sur `Moko_Payout_API_Guide_Marchand.pdf`, version 1.0, juin 2026, 23 pages, et sur les tables du fichier `u577654037_ohnous.sql` fourni. Le PDF est la référence du protocole ; ses exemples ne sont pas des identifiants de votre compte. Aucune clé, aucun appel réel ni aucun versement ne sont fournis par l'installation.

### 1. Préparer le compte Moko

Demander au support Moko les éléments suivants pour votre compte marchand actif :

- `public_key` et `secret_key` de l'API **Payout** (distinctes des anciens identifiants FreshPay du checkout) ;
- le secret de signature des webhooks `webhook_secret` ;
- l'activation des opérateurs/devises nécessaires, les plafonds et les modalités d'approvisionnement ;
- si disponible, un environnement de test avec ses propres clés et son URL. Le guide ne fournit qu'une URL : `https://payouts.gofreshpay.com`. Ne pas supposer qu'elle est une sandbox.

Support indiqué dans le document : `henock.barakael@mokoafrika.com`. Console marchand : `https://cd.merchants.gofreshpay.com`. La collection Postman et le schéma OpenAPI sont à demander au support ; ils ne figuraient pas parmi les pièces jointes. En particulier, faire confirmer le format complet des réponses bénéficiaire avant mise en service : le client exige les coordonnées et l'identifiant marchand retournés pour vérifier un rattachement.

### 2. Mettre à jour la base dans phpMyAdmin

Sauvegarder la base du site, sélectionner cette base dans phpMyAdmin, ouvrir **Importer**, choisir `data base/20260907_001_moko_payout.sql`, conserver le format **SQL**, puis cliquer sur **Importer**. **Ne pas réimporter le dump complet sur une base contenant déjà des données.** Ce SQL est prévu pour MariaDB, comme le dump joint ; il a été exécuté deux fois sur une base de test reconstruite à partir de ses trois tables payout.

Les prochaines migrations seront livrées dans le dossier `data base/`, sous la forme `AAAAMMJJ_NNN_description.sql`. Importer les fichiers nécessaires dans l'ordre de leur nom en respectant les prérequis indiqués en commentaire.

Les anciennes lignes sont identifiées comme `freshpay`. Les nouvelles lignes Moko stockent séparément l'identifiant bénéficiaire et le payout ID, sans réutiliser une colonne FreshPay pour ces identifiants.

Migration à importer : [20260907_001_moko_payout.sql](<data base/20260907_001_moko_payout.sql>).

Les tables `payout_transactions`, `payout_status_history`, `payout_audit_log` et le champ `admins.can_payout` existent déjà dans la base jointe. La migration les complète sans importer de comptes ni de données clients. Le droit d'accès reste celui du projet : administrateur connecté et `can_payout = 1`. Pour autoriser un compte, modifier uniquement la cellule `can_payout` de cet administrateur dans phpMyAdmin après avoir vérifié son ID.

### 3. Configurer PHP sur l'hébergement

PHP 8.0 ou supérieur, extensions PDO MySQL et cURL, certificats CA valides, accès HTTPS sortant et horloge synchronisée sont nécessaires. Le fichier `.env` à la racine est désormais chargé par `config/env.php` lorsque la configuration Moko ou FreshPay est lue, aussi bien sur le site que dans le cron PHP. Renseigner les variables suivantes dans `.env` ; les variables déjà définies par l'hébergeur restent prioritaires.

```text
MOKO_PAYOUT_ENABLED=0
MOKO_BASE_URL=https://payouts.gofreshpay.com
MOKO_PUBLIC_KEY=VOTRE_CLE_PUBLIQUE_PAYOUT
MOKO_SECRET_KEY=VOTRE_CLE_SECRETE_PAYOUT
MOKO_WEBHOOK_SECRET=VOTRE_SECRET_WEBHOOK
MOKO_CALLBACK_URL=https://ohnous.store/payout-callback-moko
```

Adapter le domaine au site déployé. Déclarer cette URL HTTPS chez Moko également. Le endpoint reçoit des POST JSON signés, sans connexion administrateur ni CSRF navigateur. La vérification HMAC est obligatoire. Une réponse 401 indique une signature invalide ; 503 indique qu'il faut réessayer, notamment si la base n'est pas accessible.

Le `.env` local est créé avec les clés vides et les versements désactivés. Sur le serveur, créer séparément `.env` à partir de `.env.example`, renseigner les clés et mettre `MOKO_PAYOUT_ENABLED=1` une fois la configuration terminée. `.env` est exclu de Git ; déployer également `config/env.php`, les configurations mises à jour et le `.htaccess`, qui interdit l'accès HTTP aux fichiers `.env`. Avec un serveur autre qu'Apache, configurer cette interdiction dans ce serveur avant déploiement.

Format pris en charge : une variable `NOM=valeur` par ligne, lignes vides et commentaires sur des lignes commençant par `#`. Les guillemets simples ou doubles entourant une valeur sont facultatifs et retirés ; le contenu reste littéral, sans interpolation `${VARIABLE}` ni valeurs multilignes. Ne pas ajouter de commentaire à la fin d'une valeur. Le chargement ne journalise pas les valeurs. Si une ancienne variable serveur vaut `0`, la modifier ou la retirer pour que le `1` du `.env` prenne effet.

Ne pas commiter les valeurs des clés. La rotation décrite dans le guide offre 24 h de coexistence : demander les nouvelles clés au support, modifier l'environnement, redémarrer PHP si nécessaire. Le code n'enregistre pas les clés ni les en-têtes d'authentification dans les historiques.

### 4. Vérifier la connexion puis activer

Depuis le terminal du serveur, à la racine du projet :

```bash
php scripts/moko.php diagnostic
```

Cette commande effectue seulement `GET /v1/health` et `GET /v1/balance`. Vérifier les codes HTTP 200, le `merchant_code` attendu et le solde par opérateur/devise. Elle fonctionne même avec `MOKO_PAYOUT_ENABLED=0`. Elle ne déclenche aucun payout. Les identifiants DB sont ceux de `model/bdd.php`, qui n'a pas été modifié.

Après installation et vérification des paramètres, définir `MOKO_PAYOUT_ENABLED=1`. Le module ne bascule jamais automatiquement sur FreshPay si Moko échoue.

### 5. Effectuer un versement

1. Se connecter avec l'administrateur autorisé et ouvrir `/admin-payout`.
2. Sélectionner la boutique. Son bénéficiaire enregistré est repris automatiquement.
3. Renseigner son numéro RDC `+243` suivi de 9 chiffres et son opérateur : M-Pesa, Airtel, Orange ou Afrimoney.
4. Si aucun bénéficiaire n’est enregistré, cliquer sur « Enregistrer le bénéficiaire ». La référence KYC est facultative : utiliser la référence interne d'un dossier, pas le numéro brut d'une pièce d'identité.
5. Choisir CDF ou USD et saisir le montant. Aucune conversion ni majoration du checkout n'est appliquée au payout.
6. Saisir une référence **propre à cette intention de versement**, par exemple `reversement-commande-123-vendeur-42`. Conserver la même référence après une erreur ou une coupure réseau. Elle est normalisée en minuscules et n'est jamais recyclée localement.
7. Renseigner le motif et cliquer sur « Effectuer le PayOut ». Cette action peut transférer de l'argent réel lorsque les clés sont actives.

L’enregistrement séparé conserve le `recipient_id` avant le premier versement. Chaque nouveau versement relit l’état chez Moko. Un bénéficiaire `PENDING_KYC`, archivé ou en tout autre état que `ACTIVE` ne reçoit pas de payout par ce module.

Si le bénéficiaire existe déjà chez Moko mais que son ID n’a pas été enregistré localement, le bouton d’enregistrement recherche son identifiant marchand dans `GET /v1/recipients` après une réponse de doublon. Le serveur vérifie ses coordonnées avant rattachement. Les modifications sensibles se traitent chez Moko et peuvent entraîner le délai de sécurité de 24 h décrit dans le guide.

Le guide propose 100 CDF comme exemple de test : faire ce premier versement uniquement vers un bénéficiaire contrôlé, avec un solde disponible et en sachant qu'il peut être réel. Le site ne comporte pas de bouton simulant un transfert Moko.

### 6. Suivi et récupération automatique

L'historique est accessible sur `/admin-payouts`, puis via le détail ou `/admin-payout-suivi?reference=VOTRE_REFERENCE`. Les listes et exports conservent chaque devise. L'acceptation HTTP 202 ne signifie pas que le bénéficiaire est crédité.

| Statut Moko | Sens |
| --- | --- |
| RESERVED | Fonds réservés, attente |
| HOLD_REVIEW | Validation du backoffice Moko nécessaire |
| DISPATCHED / WAITING_CALLBACK | Traitement en cours |
| COMPLETED | Bénéficiaire crédité |
| FAILED / EXPIRED | Échec ou expiration ; fonds restitués selon le guide |
| RELEASED | Annulation par le backoffice Moko |

Le statut Moko exact est conservé dans `moko_status`, la réponse API et la chronologie. Les états internes « Résultat incertain » et « À rapprocher » ne prouvent ni un échec ni un crédit.

Configurer une tâche cron toutes les minutes, avec les mêmes variables Moko que PHP web. Exemple à adapter au chemin réel et au binaire PHP de l'hébergeur :

```cron
* * * * * /usr/bin/php /chemin/du/site/scripts/moko.php reconcile
```

Le job traite au plus 50 dossiers par passage, vérifie les payouts connus et reprend les envois incertains avec le **payload enregistré** et la **même référence**. Les lectures du navigateur ne déclenchent jamais de nouvel envoi. Les reprises réseau sont espacées (2 puis 4 minutes), avec trois tentatives au total. Les erreurs 4xx, sauf 429, ne sont pas retentées automatiquement. Après une tentative antérieure incertaine, un refus exige un rapprochement.

Le guide mentionne une possible réutilisation de référence après 24 h : par prudence le module bloque toute reprise après 23 h et conserve la référence indéfiniment en base. Il ne faut jamais supprimer une ligne ni changer sa référence pour « forcer » une reprise. Après trois tentatives ou en état « À rapprocher », rechercher l'opération chez Moko avec la référence marchand. Si elle existe, rattacher son ID, sans nouveau transfert :

```bash
php scripts/moko.php recover reversement-commande-123-vendeur-42 PAY-IDENTIFIANT_RETOURNE_PAR_MOKO
```

Le rattachement exige que l'API confirme référence, bénéficiaire, devise et montant. Si Moko confirme l'absence de transfert, décider manuellement de la suite avec le support ; il n'existe pas de reprise automatique illimitée.

Les webhooks sont vérifiés sur leurs octets bruts, conservés durablement et dédupliqués. Un webhook reçu avant la réponse de création est conservé puis traité après le rattachement du payout. Les états terminaux ne régressent pas si un ancien événement arrive ensuite. Le guide ne définit pas de fenêtre de fraîcheur des webhooks : aucune limite arbitraire de cinq minutes n'est appliquée, pour permettre ses livraisons retardées. Garder une politique de conservation adaptée pour l'audit et les événements ; ne pas supprimer la référence d'idempotence.

### 7. Tests et limites de validation

```bash
php tests/moko_payout_test.php
```

Les tests de signature et de transitions ne contactent pas Moko. Pour les tests DB, définir `MOKO_TEST_DSN=mysql:host=127.0.0.1;port=3307;charset=utf8mb4`, `MOKO_TEST_USER`, `MOKO_TEST_PASSWORD` et éventuellement `MOKO_TEST_DUMP` vers le dump fourni. Le script crée et supprime uniquement une base temporaire nommée `ohnous_moko_test_...` ; il nécessite les droits correspondants sur un serveur local.

Tests réalisés : migration rejouable sur les tables jointes, signature HMAC et nonce UUID v4, tri des paramètres GET, création bénéficiaire, unicité et conflit de référence, timeout, backoff et borne des tentatives, limite d'ancienneté, refus 4xx, webhook anticipé/dupliqué/falsifié, non-régression des états et recherche SQL avec préparations natives. Tous les appels Moko de cette suite sont simulés.

La base du site n'a pas été migrée et aucun test réel chez Moko n'a été effectué. Il reste à appliquer le SQL, installer les clés, vérifier la réponse réelle des endpoints, configurer le cron et le callback HTTPS, puis valider un premier versement contrôlé. Les métriques et alertes externes évoquées par le guide ne sont pas installées par ce module ; surveiller l'historique, les sorties cron et les dossiers à rapprocher.

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

Ce projet charge désormais le fichier `.env` de la racine via `config/env.php` avant la lecture des configurations de paiement. Les variables déjà présentes dans l'environnement PHP restent prioritaires ; `getenv(...)` peut ensuite lire les valeurs chargées.

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

1. Renseigne le fichier `.env` à la racine du projet ; il est chargé automatiquement par les configurations de paiement.
2. Les variables définies dans Apache ou dans le terminal restent prioritaires sur ce fichier.

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
- L'action PayOut FreshPay est fixée à `credit`, la devise à `USD` et le profil API à `Edo Systeme / edosysteme@gmail.com`.

## Correctif suivi PayOut FreshPay

Le Check Status FreshPay attend dans `reference` le `Transaction_id` (`PD...`) retourné à l'initiation, et non la référence interne OHNOUS (`PAYOUT-...`). Le numéro reste enregistré en E.164 mais est envoyé à FreshPay sans le signe `+`. Le PayOut transmet `https://ohnous.store/payments/freshpay/callback`, surchargeable avec `FRESHPAY_PAYOUT_CALLBACK_URL`. Le journal temporaire `logs/freshpay-payout-debug.log` est actif par défaut et masque les secrets ; définir `FRESHPAY_PAYOUT_DEBUG=0` après validation en production.

Lorsqu'une erreur PayOut est retournée par FreshPay, un rapport partageable est automatiquement ajouté dans `logs/freshpay-payout-support.log`. Il contient la réponse HTTP brute, la réponse JSON décodée, l'endpoint et la requête, avec les secrets, signatures et numéros sensibles masqués.

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
