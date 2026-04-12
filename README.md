# Espace admin, promotions et gestion boutiques

## Ce qui a été intégré

- Ajout d’un vrai espace admin avec connexion dédiée sur `/admin-login`.
- Session admin intégrée à la logique existante sans modifier `model/bdd.php`.
- Changement automatique de l’icône de compte quand l’admin est connecté.
  Icône choisie : `fa-user-shield`.
- Tableau de bord admin sur `/admin`.
- Page boutiques admin sur `/admin-boutiques` avec recherche.
- Fiche boutique admin sur `/admin-boutique?id=...` avec :
  activation ou désactivation du compte,
  envoi d’un message à la boutique,
  historique des messages admin,
  logo OhNous comme avatar admin dans la conversation,
  liste dédiée des articles de cette boutique.
- Page articles admin sur `/admin-articles` avec barre de recherche.
- Page d’édition d’article sur `/admin-editer-article?id=...`.
- Icône de modification visible sur les articles publics quand le compte admin est connecté.
- Support des promotions sur les articles :
  badge promotion,
  prix barré + prix remisé,
  filtre “Articles en promotion”,
  prise en compte dans la recherche.
- Déconnexion admin prise en charge avec la page `/deconnexion`.
- Réinitialisation du mot de passe admin :
  page `/admin-mot-de-passe`,
  envoi d’un email de réinitialisation à `edosysteme@gmail.com`,
  envoi automatique aussi après une erreur de mot de passe admin.
- Les conversations du site utilisent maintenant une vraie image de profil par défaut au lieu d’une simple icône.

## Fichiers principaux touchés

- `classes/Routeur.php`
- `controller/Home.php`
- `fonctions/fonctions.php`
- `fonctions/email.php`
- `fonctions/filtre_article.php`
- `fonctions/admin_login.php`
- `fonctions/admin_password_request.php`
- `fonctions/admin_password_reset.php`
- `fonctions/admin_store_actions.php`
- `fonctions/admin_article_actions.php`
- `model/select.php`
- `view/login.php`
- `view/logout.php`
- `view/message.php`
- `view/articles.php`
- `view/article-details.php`
- `view/composants/fonction_produit.php`
- `view/admin-login.php`
- `view/admin-dashboard.php`
- `view/admin-boutiques.php`
- `view/admin-boutique-details.php`
- `view/admin-articles.php`
- `view/admin-edit-article.php`
- `view/admin-password.php`
- `view/admin-new-password.php`
- `asset/js/filtre_produit.js`
- `asset/js/messages.js`
- `asset/js/admin_login.js`
- `asset/js/admin_boutiques.js`
- `asset/js/admin_edit_article.js`
- `asset/js/admin_password.js`
- `asset/js/admin_new_password.js`
- `asset/css/style.css`
- `asset/css/responsive.css`

## SQL à coller dans phpMyAdmin

Important :
- Ces requêtes complètent la base pour les promotions et les nouveaux flux admin.
- Elles sont écrites pour être collées directement dans phpMyAdmin.

```sql
ALTER TABLE `articles`
  ADD COLUMN `promo_actif` INT NOT NULL DEFAULT 0 AFTER `reserve`,
  ADD COLUMN `promo_prix` DOUBLE NULL AFTER `promo_actif`;

CREATE TABLE IF NOT EXISTS `admin_password_resets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `token` VARCHAR(190) NOT NULL,
  `expire_at` DATETIME NOT NULL,
  `used_at` DATETIME NULL,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `admin_boutique_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `boutique_id` INT NOT NULL,
  `from_type` VARCHAR(30) NOT NULL,
  `message` TEXT NOT NULL,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Remarques

- `model/bdd.php` n’a pas été modifié.
- La session admin utilisée est `admin_ohnous_987654321`.
- Les promotions fonctionnent sans casser l’affichage actuel, mais la base doit avoir `promo_actif` et `promo_prix` pour que la fonctionnalité soit complète.
- La conversation admin vers boutique est séparée des conversations client/boutique actuelles pour ne pas casser votre logique existante.
- Les boutiques sans adresse email sont maintenant traitées comme des boutiques test et restent actives automatiquement.
- Les images d’articles ne sont pas rééditées depuis la page admin dans cette version.
  Cette remarque n’est plus valable après cette mise à jour : l’édition admin recharge maintenant les images existantes et permet de les remplacer dans une interface inspirée de `view/upload_image.php`.
