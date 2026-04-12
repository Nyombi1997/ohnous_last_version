<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentAccount = ohnous_get_current_account();
    if(!$currentAccount['connected'])
    {
        header("Location:/connexion");
        exit();
    }

    $likedArticles = ohnous_get_liked_articles_for_current_account();
    $suggestions = empty($likedArticles)
        ? ohnous_get_article_suggestions([], 12)
        : [];
?>
<script>
    let home_page = true;
</script>
<div class="content_page content_page--liked">
    <section class="liquid-panel liked-hero">
        <div class="liked-hero__icon"><i class="fa-solid fa-heart"></i></div>
        <div>
            <h1>Articles aimés</h1>
            <p>
                <?php if(!empty($likedArticles)): ?>
                    Retrouvez ici tous les articles que vous avez ajoutés à vos favoris.
                <?php else: ?>
                    Vous n’avez encore aucun favori. On vous propose quelques idées qui respectent les boutiques actives.
                <?php endif; ?>
            </p>
        </div>
    </section>

    <div class="container_affiche_produit" id="liked_articles_page">
        <?php
            if(!empty($likedArticles))
            {
                foreach($likedArticles as $article)
                {
                    affiche_produit($article);
                }
            }
            else
            {
                echo '<div class="empty-liquid-state"><div class="empty-liquid-state__icon"><i class="fa-regular fa-heart"></i></div><p>Aucun article aimé pour le moment. Explorez les suggestions ci-dessous.</p></div>';
                foreach($suggestions as $suggestion)
                {
                    affiche_produit($suggestion);
                }
            }
        ?>
    </div>
</div>
