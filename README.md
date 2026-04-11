# Correctifs dépendances externes et chargement d'images

## Ce qui a été corrigé

- Suppression du chargement direct de PHPMailer dans `classes/View.php` pour éviter les warnings sur toutes les pages.
- Ajout d'un chargeur central dans `fonctions/dependances.php` pour PHPMailer et ImageKit.
- Sécurisation de `fonctions/email.php`, `fonctions/email_code_password_recovery.php` et `fonctions/auth.php` quand `vendor/` est absent.
- Ajout d'un `session_start()` propre dans `fonctions/email_code_password_recovery.php` pour respecter votre logique de sessions dans `fonctions/`.
- Ajout d'une préparation centralisée des URLs ImageKit dans `fonctions/fonctions.php`.
- Ajout d'un chargeur front dans `asset/js/image_loader.js` qui garde une image légère visible, puis retente automatiquement le chargement de la version nette jusqu'à réussite.
- Mise à jour des vues produits pour utiliser ce comportement sur l'accueil, les cartes produits, la fiche article et les images du panier.

## Action à faire sur votre projet

Votre dépôt local a actuellement `composer.json`, `composer.lock` et le dossier `vendor/` supprimés. Tant que les dépendances Composer ne sont pas remises, l'envoi d'e-mails et l'authentification ImageKit ne pourront pas fonctionner.

Recréez d'abord `composer.json` à la racine avec ce contenu :

```json
{
    "require": {
        "imagekit/imagekit": "^4.0"
    }
}
```

Ensuite, exécutez à la racine du projet :

```bash
composer install
composer require phpmailer/phpmailer
```

## Base de données

Aucune modification SQL n'est nécessaire pour ce correctif.
