<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* si boutique */
    if(isset($GLOBALS['boutique']))
    {
        $boutique = $GLOBALS['boutique'];
        /* si il n'y a pas encore de session */
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $boutique = select_bdd($bdd, "boutiques", $where = 'id = "'.$boutique['id'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)>0){
            $boutique = $boutique[0];
            $backgrounds = "";
            if($boutique['backgrounds']!='')
            {
                $backgrounds = 'style="background : '.$boutique['backgrounds'].';"';
            }
            $profile = '<img src="'.ASSET.'images/profile/default.jpg" alt="" srcset="">';
            if($boutique['profile']!='')
            {
                $profile = '
                            <img 
                                class="blur-up"
                                src="'.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                srcset="
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                                sizes="(max-width:768px) 90vw, 600px"
                                loading="lazy"
                                class="blur-up"
                            />';
            }

        }
        else
        {
            // Rediriger vers une page d'erreur ou afficher un message
            header("Location:/404");
            exit();            
        }
    }
    else if(isset($_SESSION['store_ohnous_987654321']))
    {
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['store_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)!=0)
        {
            $boutique = $boutique[0];
            $backgrounds = "";
            if($boutique['backgrounds']!='')
            {
                $backgrounds = 'style="background : '.$boutique['backgrounds'].';"';
            }
            $profile = '<img src="'.ASSET.'images/profile/default.jpg" alt="" srcset="">';
            if($boutique['profile']!='')
            {
                $profile = '
                            <img 
                                class="blur-up"
                                src="'.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                srcset="
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                                sizes="(max-width:768px) 90vw, 600px"
                                loading="lazy"
                                class="blur-up"
                            />';
            }
            /* verifier si l'utilisateur a déjà reçu l'email de bienvenue */
            $verif_welcome_email = select_bdd($bdd, "bienvenue_email", $where = 'client_unique_id = "'.$boutique['unique_id'].'"', $limit = null, $offset = 0, $order = null, $random = false);
            if(count($verif_welcome_email)==0)
            {
                welcome($email = $boutique['adresse_email']);
                $insert_data = [
                    "client_unique_id" => $boutique['unique_id']
                ];
                insert_bdd($bdd, "bienvenue_email", $insert_data);
            }
        }
        else
        {
            // Rediriger vers une page d'erreur ou afficher un message
            header("Location:/404");
            exit();
        }
    }
    else
    {
        // Rediriger vers une page d'erreur ou afficher un message
        header("Location:/404");
        exit();
    }

    $currentAccount = ohnous_get_current_account();
    $isOwner = $currentAccount['connected']
        && $currentAccount['type'] === 'boutique'
        && (int)$currentAccount['id'] === (int)$boutique['id'];
    $isActiveStore = ohnous_is_store_active($boutique);

    if(!$isActiveStore && !$isOwner)
    {
        header("Location:/404");
        exit();
    }

    $storeSocials = $isActiveStore ? ohnous_get_store_social_links($boutique) : [];
?>
<script>
    let home_page = true;
</script>


<!-- baniere -->
<div class="banniere_boutique">
    <!-- container profile -->
    <div class="container_profile_boutique">
        <div class="background_baniere_boutique" <?= $backgrounds; ?>></div>

        <!-- logout -->
        <?php
            if(isset($_SESSION['store_ohnous_987654321']))
            {
                echo '<a href="/deconnexion" class="deconnexion_boutique">Deconnexion</a>';
            }
        ?>

        <!-- profile -->
        <div class="div_profile_boutique">
            <div class="profile_boutique">
                <?= $profile; ?>
            </div>
        </div>  

        <!-- div nom -->
        <div class="div_nom_boutique">
            <h1 class="nom_boutique"><?= $boutique['nom'] ?></h1>
        </div>

        <div class="store-status-pill <?= $isActiveStore ? 'is-active' : 'is-pending' ?>">
            <?= $isActiveStore ? 'Boutique active' : 'Boutique en attente d’activation' ?>
        </div>

        <!-- description boutique -->
        <div class="div_description_boutique">
            <p class="div_description_boutique">
                <?= $boutique['description'] ?>
            </p>
        </div>

        <!-- bouton editer laisser une message et social media -->
        <div class="container_message_edit_social_media_boutique">
            <div class="div_edit_message_boutique">
                <?php
                    if($isOwner)
                    {
                        echo '
                        <a href="/editer-boutique" class="editer_boutique message">Editer</a>';
                    }
                ?>
                <?php
                    if($isOwner && !$isActiveStore)
                    {
                        echo '
                        <a href="/activer-boutique" class="editer_boutique message">Activer boutique <i class="fa-solid fa-triangle-exclamation"></i></a>';
                    }
                ?>
                <?php
                    if($isOwner && $isActiveStore)
                    {
                        echo '<a href="/ajouter-articles" class="editer_boutique message">Ajouter un article</a>';
                    }
                ?>
                <?php
                    if($currentAccount['connected'])
                    {
                        echo '<a href="/articles-aimes" class="editer_boutique message">Articles aimés</a>';
                    }
                ?>
                <?php
                    $messageLink = '/connexion';
                    if($isOwner)
                    {
                        $messageLink = '/message';
                    }
                    elseif($currentAccount['connected'] && $currentAccount['type'] === 'utilisateur')
                    {
                        $messageLink = '/message?client='.(int)$currentAccount['id'].'&boutique='.(int)$boutique['id'];
                    }
                ?>
                <a href="<?= $messageLink ?>" class="editer_boutique message">Message <?php
                    if($isOwner)
                    {
                        $messages = gestion_9_plus(ohnous_get_unread_messages_count($currentAccount));
                        echo '
                        <span>'.$messages.'</span>';
                    }
                ?></a>
            </div>
            <div class="social_media_boutique">
                <?php
                    foreach($storeSocials as $social)
                    {
                        echo '
                        <a href="'.htmlspecialchars($social['url'], ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener"><i class="fa-brands '.$social['icon'].'"></i></a>';
                    }
                ?>                
            </div>
        </div>

        <!-- div categories -->
        <div class="parent_div_section_categorie">
            <!-- Swiper -->
            <div class="swiper section_categorie">
                <div class="swiper-wrapper">
                    <?php
                        /* afficher les categories */
                        $categories = $isActiveStore ? categorieBoutique($boutique['id']) : [];
                        $category_ids = array();
                        foreach ($categories as $category) {
                            $detail_category = only_select("categorie", $where = "id = '".$category['id']."'", $order = null, $limit = null);
                            $category = only_select("categorie_article", $where = "categorie = '".$category['id']."'", $order = null, $limit = null);
                            $detail_article = select_bdd($bdd, "image_articles", $where = "article = '".$category['article']."'", $limit = null, $offset = 0, $order = null, $random = true);
                            if(empty($detail_article))
                            {
                                continue;
                            }
                            $liquid_image = ohnous_prepare_liquid_image($detail_article[0]['img'], '(max-width: 768px) 35vw, 180px');
                            if(in_array($detail_category['id'], $category_ids)) {
                                continue; // Passer à l'itération suivante si l'ID de catégorie a déjà été traité
                            }
                            echo '
                                <!-- details -->
                                <a href="categorie/'.$detail_category['slug'].'" class="swiper-slide">
                                    <div class="section_categorie_nom">
                                        <p>'.$detail_category['nom'].'</p>
                                    </div>
                                    <div class="section_categorie_img" style="background: '.$detail_article[0]['background'].';">
                                        <img 
                                            class="blur-up js-liquid-image"
                                            src="'.$liquid_image['placeholder'].'"
                                            data-image-base="'.$liquid_image['base'].'"
                                            data-image-fallback="'.$liquid_image['fallback'].'"
                                            data-image-high="'.$liquid_image['high'].'"
                                            data-image-srcset="'.$liquid_image['srcset'].'"
                                            data-image-sizes="'.$liquid_image['sizes'].'"
                                            loading="lazy"
                                            alt="'.$detail_category['nom'].'"
                                        />
                                    </div>
                                </a>';
                            $category_ids[] = $detail_category['id'];
                        }
                    ?>
                </div>
            </div>
            
            <script>
                var swiper = new Swiper('.section_categorie', {
                    slidesPerView: "auto",
                    spaceBetween: 10,
                    freeMode: true,
                    autoplay: {
                        delay: 2500,
                        disableOnInteraction: true,
                    }
                });
            </script>
        </div>
    </div>
</div>


<!-- afficher les articles -->
<div class="container_affiche_produit" id="afficher_article">
    <?php
        if(!$isActiveStore)
        {
            echo '<div class="empty-liquid-state"><div class="empty-liquid-state__icon"><i class="fa-solid fa-store-slash"></i></div><p>Cette boutique n’est pas encore visible sur le site. Dès l’activation, ses articles apparaîtront ici.</p></div>';
        }
        /* si c'est une recherche */
        else if(isset($_GET['query']))
        {
            $query =  found($_GET['query'], $limit = null, 0, $order = null, $random = false);
            $donnee = ohnous_filter_visible_articles(getArticlesFromSearch($query, $limit = 24, 0, $order = null, $random = false));
            foreach($donnee as $data)
            {
                if((int)$data['boutique'] === (int)$boutique['id'])
                {
                    affiche_produit($data);
                }
            }
        }
        else
        {
            $donnee = ohnous_filter_visible_articles(select_bdd($bdd, "articles", $where = "boutique = '".$boutique['id']."'", $limit = null, $offset = 0, $order = null, $random = true));
            foreach($donnee as $data)
            {
                affiche_produit($data);
            }
        }
    ?>
</div>
