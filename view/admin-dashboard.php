<?php
    ohnous_require_admin_or_redirect();

    $totalBoutiques = getRowCount($bdd, 'boutiques');
    $totalArticles = getRowCount($bdd, 'articles');
    $boutiquesActives = getRowCount($bdd, 'boutiques', 'activer = 1');
    $articlesPromo = ohnous_column_exists('articles', 'promo_actif')
        ? getRowCount($bdd, 'articles', 'promo_actif = 1')
        : 0;
?>
<div class="content_page admin-page-shell">
    <section class="admin-hero liquid-panel">
        <div>
            <span class="admin-hero__eyebrow">Administration OhNous</span>
            <h1>Espace admin</h1>
            <p>Activez les boutiques, gérez les articles et gardez la main sur les promotions depuis un seul espace.</p>
        </div>
        <div class="admin-hero__logo">
            <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
        </div>
    </section>

    <?= ohnous_render_admin_nav('dashboard') ?>

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
    </section>
</div>
