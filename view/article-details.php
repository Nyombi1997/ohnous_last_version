<?php
    /* si article */
    if(isset($GLOBALS['article']))
    {
        $article = $GLOBALS['article'];
    }
    else
    {
        // Rediriger vers une page d'erreur ou afficher un message
        header("Location:/404");
        exit();
    }
?>
<script>
    let home_page = true;
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
                    $liquid_image = ohnous_prepare_liquid_image($image_article[0]['img']);
                    $image_article_id = 'img_produit_'.$image_article[0]['id'];
                    $image_article_style = $image_article[0]['styles'];
                    $image_article_div_img_id = 'div_img_produit_'.$image_article[0]['id'];
                    $image_article_background = $image_article[0]['background'];
                    echo '
                        <div class="div_img_affiche_produit" id="'.$image_article_div_img_id.'" style="background: '.$image_article_background.';">
                            <img
                                crossorigin="anonymous"
                                src="'.$liquid_image['placeholder'].'"
                                alt="'.$article['slug'].'" 
                                class="img_affiche blur-up js-liquid-image"
                                data-img ="'.$image_article[0]['img'].'"
                                data-image-base="'.$liquid_image['base'].'"
                                data-image-fallback="'.$liquid_image['fallback'].'"
                                data-image-high="'.$liquid_image['high'].'"
                                data-image-srcset="'.$liquid_image['srcset'].'"
                                data-image-sizes="'.$liquid_image['sizes'].'"
                                id="'.$image_article_id.'"
                                style="'.$image_article_style.'"
                                loading="lazy"
                            >
                        </div>';
                ?>
            </div>
            <!-- details -->
            <div class="div_detail_vu_article">
                <div class="nom"><?= $article['nom'] ?></div>
                <div class="avis">
                    <?php 
                        /* afficher les avis */
                        $notes = select_bdd($bdd, "notes_article", $where = "article_id = '".$article['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                        $total_notes = 0;
                        if(count($notes) == 0)
                        {
                            echo '
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <i class="fa-regular fa-star"></i>
                                <span>(0 avis)</span>';
                                /* 
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <!-- <i class="fa-regular fa-star"></i> -->
                                <i class="fa-solid fa-star-half"></i>
                                <span>(24 avis)</span> */

                        }
                        else
                        {

                        }
                    ?>
                </div>
                <div class="prix"><?= $article['prix'] ?> USD</div>
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
                            /* si panier */
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }
                            $key = cartKey($article['id'], $tailles);
                            $panier = '';
                            $icone = 'icon-panier_plus';
                            if (isset($_SESSION['cart-ohnous-123456789'][$key])) {
                                $panier = 'active'; 
                                $icone = 'icon-panier_moins';               
                            }
                            echo
                            '
                            <button class="panier '.$panier.'" id="btn_panier_'.$article['id'].'" onclick=\'ajouterAuPanier('.json_encode($image_article[0]['img']).','.(int)$article['id'].','.json_encode($article['nom']).','.json_encode($article['slug']).','.json_encode($tailles).','.(int)$article['prix'].','.json_encode($image_article_style).','.json_encode($image_article_background).')\'><span class="'.$icone.'"></span></button>';
                        ?>
                        <!-- acheter directement -->
                        <button class="acheter_directement">Commander maintenant</button>
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
                                'slug' => '/']
                            ;
                        }
                        echo '<div class="details"><strong>Boutique : </strong><a href="/boutique/'.$boutique['slug'].'">'.$boutique['nom'].'</a></div>';
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
                                    'slug' => '/']
                                ;
                            }
                        }
                        else
                        {
                            $categorie = [
                                'nom' => 'Aucune catégorie',
                                'slug' => '/']
                            ;
                        }
                        echo '<div class="details"><strong>Catégorie : </strong><a href="/categorie/'.$categorie['slug'].'">'.$categorie['nom'].'</a></div>';
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
                                    'nom' => 'Aucune type',
                                    'slug' => '/']
                                ;
                            }
                        }
                        else
                        {
                            $types = [
                                'nom' => 'Aucune type',
                                'slug' => '/']
                            ;
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
                <div class="rating-container">
                    <h3>Notez cet article</h3>
                    
                    <div class="stars" id="star-rating">
                        <span class="star" data-value="5">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="1">&#9733;</span>
                    </div>

                    <p id="rating-value">Note : 0/5</p>

                    <textarea id="comment-text" placeholder="Votre avis ici..."></textarea>
                    <button id="submit-rating" class="btn_ohnous">Publier mon avis</button>

                    <hr>
                    <h4>Commentaires récents</h4>
                    <div id="reviews-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- afficher les articles -->
<div class="container_affiche_produit" id="afficher_article">
    <div class="titre">Article(s) similaire(s)</div>
    <?php
        $article = getSimilarArticles($article['id'],8,null,false);
        foreach($article as $donnee)
        {
            affiche_produit($donnee);
        }
    ?>
</div>



<!-- script filtre produit -->
<script src="/asset/js/article_article.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/article_article.js") ?>"></script> 
