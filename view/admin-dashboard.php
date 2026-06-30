<?php
    ohnous_require_admin_or_redirect();

    $totalBoutiques = getRowCount($bdd, 'boutiques');
    $totalArticles = getRowCount($bdd, 'articles');
    $boutiquesActives = getRowCount($bdd, 'boutiques', 'activer = 1');
    $articlesPromo = ohnous_column_exists('articles', 'promo_actif')
        ? getRowCount($bdd, 'articles', 'promo_actif = 1')
        : 0;
    $paymentEnabled = ohnous_is_payment_enabled();
?>
<div class="content_page admin-page-shell">
    <section class="admin-hero liquid-panel">
        <div>
            <span class="admin-hero__eyebrow">Administration OhNous</span>
            <h1>Espace admin</h1>
            <p>Activez les boutiques, gérez les articles, la livraison et les comptes admins depuis un seul espace.</p>
        </div>
        <div class="admin-hero__logo">
            <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
        </div>
    </section>

    <?= ohnous_render_admin_nav('dashboard') ?>

    <section class="admin-payment-settings liquid-panel">
        <div>
            <span class="admin-hero__eyebrow">Paiement</span>
            <h2>Mode de paiement</h2>
            <p><?= $paymentEnabled ? "Le paiement est actuellement actif sur le site." : "Le paiement est actuellement désactivé sur le site." ?></p>
        </div>
        <form id="admin_payment_settings_form" class="admin-payment-settings__form">
            <label class="admin-switch-card">
                <input type="checkbox" id="admin_payment_enabled" <?= $paymentEnabled ? 'checked' : '' ?>>
                <span><?= $paymentEnabled ? 'Paiement activé' : 'Paiement désactivé' ?></span>
            </label>
            <button type="submit" class="btn_ohnous">Enregistrer</button>
        </form>
    </section>

    <section class="admin-stat-grid">
        <article class="admin-stat-card liquid-panel">
            <span>Boutiques</span>
            <strong><?= (int)$totalBoutiques ?></strong>
            <small><?= (int)$boutiquesActives ?> actives actuellement</small>
        </article>
        <article class="admin-stat-card liquid-panel">
            <span>Articles</span>
            <strong><?= (int)$totalArticles ?></strong>
            <small>Catalogue total</small>
        </article>
        <article class="admin-stat-card liquid-panel">
            <span>Promotions</span>
            <strong><?= (int)$articlesPromo ?></strong>
            <small>Articles mis en avant</small>
        </article>
    </section>

    <section class="admin-shortcuts">
        <a href="/admin-boutiques" class="liquid-panel admin-shortcut-card">
            <i class="fa-solid fa-store"></i>
            <div>
                <strong>Gérer les boutiques</strong>
                <p>Activer, désactiver, rechercher et contacter les boutiques.</p>
            </div>
        </a>
        <a href="/admin-articles" class="liquid-panel admin-shortcut-card">
            <i class="fa-solid fa-tags"></i>
            <div>
                <strong>Gérer les articles</strong>
                <p>Retrouver rapidement un article et l’éditer.</p>
            </div>
        </a>
        <a href="/admin-zones-livraison" class="liquid-panel admin-shortcut-card">
            <i class="fa-solid fa-truck-fast"></i>
            <div>
                <strong>Gérer la livraison</strong>
                <p>Créer des zones de livraison et piloter leur tarification.</p>
            </div>
        </a>
        <a href="/admin-admins" class="liquid-panel admin-shortcut-card">
            <i class="fa-solid fa-user-shield"></i>
            <div>
                <strong>Gérer les admins</strong>
                <p>Créer d’autres admins et leur envoyer un accès direct par email.</p>
            </div>
        </a>
    </section>
</div>
<script src="/asset/js/admin_payment_settings.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_payment_settings.js") ?>" defer></script>
