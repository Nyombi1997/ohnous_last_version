<?php
    ohnous_require_admin_or_redirect();

    $search = trim((string)($_GET['search'] ?? ''));
    $articles = ohnous_admin_fetch_articles($search);
    $articleSuggestions = array_map(function($article){
        return [
            'id' => (int)$article['id'],
            'label' => (string)$article['nom'],
            'slug' => (string)$article['slug'],
            'url' => '/admin-editer-article?id='.(int)$article['id']
        ];
    }, $articles);
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>Articles</h1>
            <p>Retrouvez un article avec la barre de recherche, voyez ses promotions et ouvrez sa fiche d’édition.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('articles') ?>

    <form method="GET" action="/admin-articles" class="liquid-panel admin-search-bar">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" id="admin_article_search_input" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher un article, une boutique, un prix ou un slug" autocomplete="off">
        <button type="submit" class="btn_ohnous">Rechercher</button>
        <div class="admin-search-suggestions" id="admin_article_search_suggestions"></div>
    </form>

    <div class="admin-article-table liquid-panel">
        <?php if(empty($articles)): ?>
            <div class="empty-liquid-state">
                <div class="empty-liquid-state__icon"><i class="fa-regular fa-rectangle-list"></i></div>
                <p>Aucun article ne correspond à votre recherche.</p>
            </div>
        <?php else: ?>
            <div class="admin-article-table__head">
                <span>Image</span>
                <span>Article</span>
                <span>Boutique</span>
                <span>Prix</span>
                <span>Statut</span>
                <span>Action</span>
            </div>
            <?php foreach($articles as $article): ?>
                <?php
                    $boutique = only_select('boutiques', 'id = '.(int)$article['boutique'], null, null);
                    $pricing = ohnous_get_article_pricing($article);
                    $images = ohnous_get_article_images((int)$article['id']);
                ?>
                <div class="admin-article-table__row">
                    <div class="admin-article-table__thumb">
                        <?php if(!empty($images)): ?>
                            <?= ohnous_render_article_gallery((int)$article['id'], (string)$article['nom'], 'admin-thumb', '/admin-editer-article?id='.(int)$article['id'], $images) ?>
                        <?php else: ?>
                            <span><i class="fa-regular fa-image"></i></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($article['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small><?= htmlspecialchars((string)$article['slug'], ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <div><?= htmlspecialchars((string)($boutique['nom'] ?? 'Boutique inconnue'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div>
                        <?php if($pricing['promo_actif']): ?>
                            <span class="promo-price">$ <?= number_format($pricing['prix_final'], 2, '.', ' ') ?></span>
                            <small><s>$ <?= number_format($pricing['prix_initial'], 2, '.', ' ') ?></s></small>
                        <?php else: ?>
                            <span>$ <?= number_format($pricing['prix_final'], 2, '.', ' ') ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="status <?= $pricing['promo_actif'] ? 'promo' : 'active' ?>"><?= $pricing['promo_actif'] ? 'Promotion' : 'Standard' ?></span>
                    </div>
                    <div>
                        <a href="/admin-editer-article?id=<?= (int)$article['id'] ?>" class="btn_ohnous second">Modifier</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
    window.adminArticleSuggestions = <?= json_encode($articleSuggestions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="/asset/js/admin_boutiques.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_boutiques.js") ?>" defer></script>
