<?php
    /* si article */
    if(isset($GLOBALS['article']))
    {
        $article = $GLOBALS['article'];
    }
    else
    {
        header("Location:/404");
        exit();
    }

    $currentAccount = ohnous_get_current_account();
    $ratingSummary = ohnous_get_article_rating_summary($article['id']);
    $reviewSummaryHtml = ohnous_render_article_rating_summary($article['id'], 'detail');
    $reviewListHtml = ohnous_render_article_reviews_html($article['id'], 20);
    $likeSummary = ohnous_get_article_likes_summary($article['id']);
    $pricing = ohnous_get_article_pricing($article);
?>
<script>
    let home_page = true;
    window.articleShareConfig = {
        title: <?= json_encode($article['nom'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        text: <?= json_encode('Decouvrez cet article sur OhNous : '.$article['nom'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        url: <?= json_encode((((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http').'://'.($_SERVER['HTTP_HOST'] ?? 'localhost').($_SERVER['REQUEST_URI'] ?? '/article/'.$article['slug'])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };
    window.articleReviewsConfig = {
        articleId: <?= (int)$article['id'] ?>,
        isConnected: <?= $currentAccount['connected'] ? 'true' : 'false' ?>,
        accountType: <?= json_encode($currentAccount['type']) ?>,
        loginUrl: '/connexion',
        signupUrl: '/choix-compte',
        currentPath: <?= json_encode($_SERVER['REQUEST_URI'] ?? '/article/'.$article['slug']) ?>
    };
</script>
<!-- content page -->
<div class="content_page">
    <div class="content_vue_article">
        <div class="parent_div_image_vu_article">
            <!-- image -->
            <div class="div_image_vu_article">
                <?php
                    /* afficher les images de l'article */
                    $image_article = select_bdd($bdd, "image_articles", $where = "article = '".$article['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                    $mainImage = $image_article[0];
                    $image_article_style = $mainImage['styles'];
                    $image_article_background = $mainImage['background'];
                    echo '<div class="swiper article-gallery-swiper js-article-gallery-swiper">';
                    echo '<div class="swiper-wrapper">';
                    foreach($image_article as $singleImage)
                    {
                        $liquid_image = ohnous_prepare_liquid_image($singleImage['img']);
                        $image_article_id = 'img_produit_'.$singleImage['id'];
                        $image_article_div_img_id = 'div_img_produit_'.$singleImage['id'];
                        echo '
                            <div class="swiper-slide">
                                <div class="div_img_affiche_produit div_img_affiche_produit--detail" id="'.$image_article_div_img_id.'" style="background: '.$singleImage['background'].';">
                                    <img
                                        crossorigin="anonymous"
                                        src="'.$liquid_image['placeholder'].'"
                                        alt="'.$article['slug'].'"
                                        class="img_affiche blur-up js-liquid-image"
                                        data-img ="'.$singleImage['img'].'"
                                        data-image-base="'.$liquid_image['base'].'"
                                        data-image-fallback="'.$liquid_image['fallback'].'"
                                        data-image-high="'.$liquid_image['high'].'"
                                        data-image-srcset="'.$liquid_image['srcset'].'"
                                        data-image-sizes="'.$liquid_image['sizes'].'"
                                        id="'.$image_article_id.'"
                                        style="'.$singleImage['styles'].'"
                                        loading="lazy"
                                    >
                                </div>
                            </div>';
                    }
                    echo '</div>';
                    echo '<div class="article-gallery-counter"><span class="current">1</span>/<span class="total">'.count($image_article).'</span></div>';
                    echo '</div>';
                ?>
            </div>
            <!-- details -->
            <div class="div_detail_vu_article">
                <div class="nom"><?= $article['nom'] ?></div>
                <div class="article-detail-top-actions">
                    <?= ohnous_render_article_admin_edit_link($article['id'], 'detail') ?>
                    <?= ohnous_render_like_button($article['id'], 'detail') ?>
                    <div class="article-detail-top-actions__likes">
                        <span data-like-total-label="<?= (int)$article['id'] ?>"><?= $likeSummary['count_formatted'] ?></span> j'aime
                    </div>
                </div>
                <div class="avis">
                    <div id="article-review-summary"><?= $reviewSummaryHtml ?></div>
                </div>
                <div class="prix <?= $pricing['promo_actif'] ? 'promo' : '' ?>">
                    <?php if($pricing['promo_actif']): ?>
                        <span class="old-price">$ <?= number_format($pricing['prix_initial'], 2, '.', ' ') ?></span>
                        <span class="new-price">$ <?= number_format($pricing['prix_final'], 2, '.', ' ') ?></span>
                        <span class="promo-inline-badge">En promotion</span>
                    <?php else: ?>
                        <?= number_format($pricing['prix_final'], 2, '.', ' ') ?> USD
                    <?php endif; ?>
                </div>
                <div class="taille">
                    <?php
                        /* afficher les tailles */
                        $tailles = select_bdd($bdd, "taille_articles", $where = "article = '".$article['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                        foreach($tailles as $taille)
                        {
                            $nom_taille_vu_article = only_select("tailles", $where = "id = '".$taille['taille']."'", $order = null, $limit = null);
                            echo '
                                <span>'.$nom_taille_vu_article['nom'].'</span>';
                        }
                        $tailles = fetch_tailles($article['id']);
                        if(empty($tailles))
                        {
                            $tailles = "";
                        }
                    ?>
                </div>
                <div class="action_panier">
                    <?php
                        $key = cartKey($article['id'], $tailles);
                        $panier = '';
                        $icone = 'icon-panier_plus';
                        $cartItems = ohnous_get_cart_items();
                        if (isset($cartItems[$key])) {
                            $panier = 'active';
                            $icone = 'icon-panier_moins';
                        }
                        echo '
                            <button class="panier '.$panier.'" id="btn_panier_'.$article['id'].'" onclick="ajouterAuPanier('.ohnous_js_html_arg($mainImage['img']).','.(int)$article['id'].','.ohnous_js_html_arg($article['nom']).','.ohnous_js_html_arg($article['slug']).','.ohnous_js_html_arg($tailles).','.ohnous_js_html_arg((string)$pricing['prix_final']).','.ohnous_js_html_arg($image_article_style).','.ohnous_js_html_arg($image_article_background).')"><span class="'.$icone.'"></span></button>';
                    ?>
                    <button
                        type="button"
                        class="acheter_directement"
                        onclick="commanderDirectement(<?= ohnous_js_html_arg($mainImage['img']) ?>, <?= (int)$article['id'] ?>, <?= ohnous_js_html_arg($article['nom']) ?>, <?= ohnous_js_html_arg($article['slug']) ?>, <?= ohnous_js_html_arg($tailles) ?>, <?= ohnous_js_html_arg((string)$pricing['prix_final']) ?>, <?= ohnous_js_html_arg($image_article_style) ?>, <?= ohnous_js_html_arg($image_article_background) ?>)"
                    >Commander maintenant</button>
                    <button type="button" class="partager_article js-article-share-trigger">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>Partager</span>
                    </button>
                </div>
                <div class="plus_details">
                    <?php
                        /* boutique */
                        $boutique = select_bdd($bdd, "boutiques", $where = "id = '".$article['boutique']."'", $limit = null, $offset = 0, $order = null, $random = false);
                        if($boutique)
                        {
                            $boutique = $boutique[0];
                        }
                        else
                        {
                            $boutique = [
                                'nom' => 'OhNous',
                                'slug' => '/'
                            ];
                        }
                        $storeLink = ohnous_is_store_active($boutique) ? '/boutique/'.$boutique['slug'] : '#';
                        echo '<div class="details"><strong>Boutique : </strong><a href="'.$storeLink.'">'.$boutique['nom'].'</a></div>';

                        /* categorie */
                        $categorie = select_bdd($bdd, "categorie_article", $where = "article = '".$article['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                        if($categorie)
                        {
                            $categorie = select_bdd($bdd, "categorie", $where = "id = '".$categorie[0]['categorie']."'", $limit = null, $offset = 0, $order = null, $random = false);
                            if($categorie)
                            {
                                $categorie = $categorie[0];
                            }
                            else
                            {
                                $categorie = [
                                    'nom' => 'Aucune catégorie',
                                    'slug' => '/'
                                ];
                            }
                        }
                        else
                        {
                            $categorie = [
                                'nom' => 'Aucune cat&eacute;gorie',
                                'slug' => '/'
                            ];
                        }
                        echo '<div class="details"><strong>Cat&eacute;gorie : </strong><a href="/categorie/'.$categorie['slug'].'">'.$categorie['nom'].'</a></div>';

                        /* types */
                        $types = select_bdd($bdd, "types_article", $where = "article = '".$article['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                        if($types)
                        {
                            $types = select_bdd($bdd, "types", $where = "id = '".$types[0]['types']."'", $limit = null, $offset = 0, $order = null, $random = false);
                            if($types)
                            {
                                $types = $types[0];
                            }
                            else
                            {
                                $types = [
                                    'nom' => 'Aucun type',
                                    'slug' => '/'
                                ];
                            }
                        }
                        else
                        {
                            $types = [
                                'nom' => 'Aucun type',
                                'slug' => '/'
                            ];
                        }
                        echo '<div class="details"><strong>Type : </strong><a href="/type/'.$types['slug'].'">'.$types['nom'].'</a></div>';
                    ?>
                </div>
            </div>
        </div>
    </div>
    <!-- details articles -->
    <div class="div_details_article_vu_article">
        <div class="div_titre">
            <div class="titres js_titre_details_article">Description</div>
            <div class="titres js_titre_details_article">Note(s)</div>
            <div class="background js_background_details_article"></div>
        </div>
        <!-- div_details -->
        <div class="div_details js_description_vu_article">
            <div class="titre">Description</div>
            <div class="content">
                <p>
                    <?php
                        if($article['description'] != "")
                        {
                            echo nl2br($article['description']);
                        }
                        else
                        {
                            echo "Aucune description pour le moment.";
                        }
                    ?>
                </p>
            </div>
        </div>
        <!-- div_details -->
        <div class="div_details notes js_note_vu_article null">
            <div class="content">
                <div class="rating-container liquid-review-box" id="article-review-app">
                    <div class="liquid-review-box__intro">
                        <div>
                            <h3>Les avis sur cet article</h3>
                        </div>
                        <div class="liquid-review-box__highlight">
                            <span><?= $ratingSummary['total'] > 0 ? $ratingSummary['moyenne_formatted'].'/5' : 'Nouveau' ?></span>
                            <small><?= $ratingSummary['total_formatted'] ?> avis</small>
                        </div>
                    </div>

                    <?php if(!$currentAccount['connected']): ?>
                        <div class="review-login-callout" id="review-login-callout">
                            <div class="review-login-callout__icon">
                                <i class="fa-solid fa-user-lock"></i>
                            </div>
                            <div class="review-login-callout__content">
                                <strong>Connectez-vous pour noter et commenter.</strong>
                                <p>Votre connexion et votre inscription restent g&eacute;r&eacute;es par vos pages actuelles, sans changer votre logique existante.</p>
                            </div>
                            <button type="button" class="btn_ohnous review-login-callout__button" id="open-review-auth">
                                Se connecter ou s&rsquo;inscrire
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="review-editor <?= !$currentAccount['connected'] ? 'is-locked' : '' ?>">
                        <div class="review-editor__head">
                            <h4><?= $currentAccount['connected'] ? 'Partagez votre avis' : 'Connexion requise pour publier' ?></h4>
                            <p id="rating-value">Note : 0/5</p>
                        </div>

                        <div class="stars" id="star-rating">
                            <button type="button" class="star" data-value="5" aria-label="Noter 5 sur 5">&#9733;</button>
                            <button type="button" class="star" data-value="4" aria-label="Noter 4 sur 5">&#9733;</button>
                            <button type="button" class="star" data-value="3" aria-label="Noter 3 sur 5">&#9733;</button>
                            <button type="button" class="star" data-value="2" aria-label="Noter 2 sur 5">&#9733;</button>
                            <button type="button" class="star" data-value="1" aria-label="Noter 1 sur 5">&#9733;</button>
                        </div>

                        <textarea id="comment-text" placeholder="Dites ce que vous avez aim&eacute;, la qualit&eacute;, la taille, la livraison, ou ce qui pourrait &ecirc;tre am&eacute;lior&eacute;."></textarea>
                        <div class="review-editor__actions">
                            <span class="review-editor__hint">Un seul avis par compte. Si vous republiez, votre avis sera mis &agrave; jour.</span>
                            <button id="submit-rating" class="btn_ohnous">Publier mon avis</button>
                        </div>
                    </div>

                    <div class="review-feed">
                        <div class="review-feed__head">
                            <h4>Commentaires r&eacute;cents</h4>
                            <span id="review-feed-count"><?= $ratingSummary['total_formatted'] ?> avis</span>
                        </div>
                        <div id="reviews-list"><?= $reviewListHtml ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- afficher les articles -->
<div class="container_affiche_produit" id="afficher_article">
    <div class="titre">Article(s) similaire(s)</div>
    <?php
        $article = ohnous_filter_visible_articles(getSimilarArticles($article['id'],12,null,false));
        $article = array_slice($article, 0, 8);
        foreach($article as $donnee)
        {
            affiche_produit($donnee);
        }
    ?>
</div>

<!-- script filtre produit -->
<script src="/asset/js/article_article.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/article_article.js") ?>"></script>
