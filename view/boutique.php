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
                    if(isset($_SESSION['store_ohnous_987654321']))
                    {
                        echo '
                        <a href="/editer-boutique" class="editer_boutique">Editer</a>';
                    }
                ?>
                <a href="" class="editer_boutique message">Message <?php
                    if(isset($_SESSION['store_ohnous_987654321']))
                    {
                        $messages = select_bdd($bdd, "messages", $where = "boutique_id = '".$boutique['id']."' AND lu = '0'", $limit = null, $offset = 0, $order = null, $random = false);
                        $messages = gestion_9_plus(count($messages));
                        echo '
                        <span>'.$messages.'</span>';
                    }
                ?></a>
            </div>
            <div class="social_media_boutique">
                <?php
                    if($boutique['facebook']!='')
                    {
                        echo '
                        <a href="'.$boutique['facebook'].'" target="_blank"><i class="fa-brands fa-square-facebook"></i></a>';
                    }
                ?>
                <?php
                    if($boutique['twitter']!='')
                    {
                        echo '
                        <a href="'.$boutique['twitter'].'" target="_blank"><i class="fa-brands fa-square-twitter"></i></a>';
                    }
                ?>
                <?php
                    if($boutique['trends']!='')
                    {
                        echo '
                        <a href="'.$boutique['trends'].'" target="_blank"><i class="fa-brands fa-square-threads"></i></a>';
                    }
                ?>
                <?php
                    if($boutique['instagram']!='')
                    {
                        echo '
                        <a href="'.$boutique['instagram'].'" target="_blank"><i class="fa-brands fa-square-instagram"></i></a>';
                    }
                ?>
                <?php
                    if($boutique['whatsapp']!='')
                    {
                        echo '
                        <a href="'.$boutique['whatsapp'].'" target="_blank"><i class="fa-brands fa-square-whatsapp"></i></a>';
                    }
                ?>
                <?php
                    if($boutique['tiktok']!='')
                    {
                        echo '
                        <a href="'.$boutique['tiktok'].'" target="_blank"><i class="fa-brands fa-tiktok"></i></a>';
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
                        $categories = categorieBoutique ($boutique['id']);
                        $category_ids = array();
                        foreach ($categories as $category) {
                            $detail_category = only_select("categorie", $where = "id = '".$category['id']."'", $order = null, $limit = null);
                            $category = only_select("categorie_article", $where = "categorie = '".$category['id']."'", $order = null, $limit = null);
                            $detail_article = select_bdd($bdd, "image_articles", $where = "article = '".$category['article']."'", $limit = null, $offset = 0, $order = null, $random = true);
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
                                            class="blur-up"
                                            src="'.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                            srcset="
                                                '.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                                '.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                                '.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                                            sizes="(max-width:768px) 90vw, 600px"
                                            loading="lazy"
                                            class="blur-up"
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
        /* si c'est une recherche */
        if(isset($_GET['query']))
        {
            $query =  found($_GET['query'], $limit = null, 0, $order = null, $random = false);
            $donnee = getArticlesFromSearch($query, $limit = 12, 0, $order = null, $random = false);
            foreach($donnee as $data)
            {
                affiche_produit($data);
            }
        }
        else
        {
            $donnee = select_bdd($bdd, "articles", $where = "boutique = '".$boutique['id']."'", $limit = null, $offset = 0, $order = null, $random = true);
            foreach($donnee as $data)
            {
                affiche_produit($data);
            }
        }
    ?>
</div>