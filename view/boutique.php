<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(isset($GLOBALS['boutique']))
    {
        $boutique = $GLOBALS['boutique'];
        $boutique = select_bdd($bdd, "boutiques", 'id = "'.$boutique['id'].'"', null, 0, null, false);
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
            header("Location:/404");
            exit();            
        }
    }
    else if(isset($_SESSION['store_ohnous_987654321']))
    {
        $boutique = select_bdd($bdd, "boutiques", 'unique_id = "'.$_SESSION['store_ohnous_987654321'].'"', null, 0, null, false);
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
            welcome($email = $boutique['adresse_email']);
            $verif_welcome_email = select_bdd($bdd, "bienvenue_email", 'client_unique_id = "'.$boutique['unique_id'].'"', null, 0, null, false);
            if(count($verif_welcome_email)==0)
            {
                $insert_data = [
                    "client_unique_id" => $boutique['unique_id']
                ];
                insert_bdd($bdd, "bienvenue_email", $insert_data);
            }
        }
        else
        {
            header("Location:/404");
            exit();
        }
    }
    else
    {
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
    $storeQuery = trim((string)($_GET['query'] ?? ''));
?>
<script>
    let home_page = true;
    window.storeArticlesConfig = {
        storeId: <?= (int)$boutique['id'] ?>,
        query: <?= json_encode($storeQuery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        isOwner: <?= $isOwner ? 'true' : 'false' ?>
    };
</script>

<div class="banniere_boutique">
    <div class="container_profile_boutique">
        <div class="background_baniere_boutique" <?= $backgrounds; ?>></div>

        <?php
            if(isset($_SESSION['store_ohnous_987654321']))
            {
                echo '<a href="/deconnexion" class="deconnexion_boutique">Déconnexion</a>';
            }
        ?>

        <div class="div_profile_boutique">
            <div class="profile_boutique">
                <?= $profile; ?>
            </div>
        </div>

        <div class="div_nom_boutique">
            <h1 class="nom_boutique"><?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></h1>
        </div>

        <div class="store-status-pill <?= $isActiveStore ? 'is-active' : 'is-pending' ?>">
            <?= $isActiveStore ? 'Boutique active' : 'Boutique en attente d’activation' ?>
        </div>

        <div class="div_description_boutique">
            <p class="div_description_boutique">
                <?= nl2br(htmlspecialchars((string)$boutique['description'], ENT_QUOTES, 'UTF-8')) ?>
            </p>
        </div>

        <div class="container_message_edit_social_media_boutique">
            <div class="div_edit_message_boutique">
                <?php
                    if($isOwner)
                    {
                        echo '<a href="/editer-boutique" class="editer_boutique message">Éditer</a>';
                    }

                    if($isOwner && !$isActiveStore)
                    {
                        echo '<a href="/activer-boutique" class="editer_boutique message">Activer boutique <i class="fa-solid fa-triangle-exclamation"></i></a>';
                    }

                    if($isOwner && $isActiveStore)
                    {
                        echo '<a href="/ajouter-articles" class="editer_boutique message">Ajouter un article</a>';
                    }

                    if($currentAccount['connected'])
                    {
                        echo '<a href="/articles-aimes" class="editer_boutique message">Articles aimés</a>';
                    }

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
                        echo '<span>'.$messages.'</span>';
                    }
                ?></a>
            </div>
            <div class="social_media_boutique">
                <?php
                    foreach($storeSocials as $social)
                    {
                        echo '<a href="'.htmlspecialchars($social['url'], ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener"><i class="fa-brands '.$social['icon'].'"></i></a>';
                    }
                ?>
            </div>
        </div>

        <div class="parent_div_section_categorie">
            <div class="swiper section_categorie">
                <div class="swiper-wrapper">
                    <?php
                        $categories = $isActiveStore ? categorieBoutique($boutique['id']) : [];
                        $category_ids = array();
                        foreach ($categories as $category) {
                            $detail_category = only_select("categorie", "id = '".$category['id']."'", null, null);
                            $category = only_select("categorie_article", "categorie = '".$category['id']."'", null, null);
                            $detail_article = select_bdd($bdd, "image_articles", "article = '".$category['article']."'", null, 0, null, true);
                            if(empty($detail_article))
                            {
                                continue;
                            }
                            $liquid_image = ohnous_prepare_liquid_image($detail_article[0]['img'], '(max-width: 768px) 35vw, 180px');
                            if(in_array($detail_category['id'], $category_ids)) {
                                continue;
                            }
                            echo '
                                <a href="/shop?categorie='.rawurlencode((string)$detail_category['slug']).'&query='.rawurlencode((string)$boutique['nom']).'" class="swiper-slide">
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

<section class="store-directory-shell liquid-panel store-articles-shell" id="store_articles_page">
    <div class="shop-results-head">
        <div>
            <p class="shop-results-head__eyebrow">Boutique</p>
            <h2 class="shop-results-head__title"><?= $storeQuery !== '' ? 'Résultats pour “'.htmlspecialchars($storeQuery, ENT_QUOTES, 'UTF-8').'”' : 'Articles de la boutique' ?></h2>
        </div>
        <?php if($isOwner): ?>
            <div class="shop-results-head__owner-note">Vous pouvez maintenant modifier ou supprimer vos articles directement ici.</div>
        <?php endif; ?>
    </div>

    <?php if(!$isActiveStore): ?>
        <div class="empty-liquid-state">
            <div class="empty-liquid-state__icon"><i class="fa-solid fa-store-slash"></i></div>
            <p>Cette boutique n’est pas encore visible sur le site. Dès l’activation, ses articles apparaîtront ici.</p>
        </div>
    <?php else: ?>
        <div class="shop-loading-state" id="store_articles_loader">
            <span class="shop-loading-state__bubble"></span>
            <span class="shop-loading-state__bubble"></span>
            <span class="shop-loading-state__bubble"></span>
        </div>

        <div class="shop-empty-state null" id="store_articles_empty">
            <div class="empty-liquid-state">
                <div class="empty-liquid-state__icon"><i class="fa-solid fa-box-open"></i></div>
                <p>Aucun article n’est disponible pour cette boutique.</p>
            </div>
        </div>

        <div class="container_affiche_produit vue_article" id="store_articles_results"></div>
    <?php endif; ?>
</section>

<?php if($isActiveStore): ?>
    <script src="/asset/js/boutique_articles.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/boutique_articles.js") ?>" defer></script>
<?php endif; ?>
