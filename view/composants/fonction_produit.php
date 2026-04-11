<?php
    /* affiche produit */
    function affiche_produit($donnee=null , $return = false) {
        global $bdd;
        /* si une donnée est envoyé */
        if($donnee)
        {
            $img = select_bdd($bdd, "image_articles", $where = "article = '".$donnee['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
            $liquid_image = ohnous_prepare_liquid_image($img[0]['img']);
            $imgId = 'img_produit_'.$img[0]['id'];
            $divImgId = 'div_img_produit_'.$img[0]['id'];
            $imgBackground = $img[0]['background'];
            $imgStyles = $img[0]['styles'];
            /* badge */
            $difference_date = difference_date($donnee['date_ajout'], date("Y-m-d H:i:s"));
            $badge = '';
            if($difference_date<1)
            {
                $badge = '
                    <!-- info -->
                    <span class="info_affiche_produit new">Nouveau</span>';
            }
            /* tailles */
            $tailles = fetch_tailles($donnee['id']);
            if(empty($tailles))
            {
                $tailles = "";
            }
            /* si panier */
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $key = cartKey($donnee['id'], $tailles);
            $panier = '';
            $icone = 'icon-panier_plus';
            if (isset($_SESSION['cart-ohnous-123456789'][$key])) {
                $panier = 'active'; 
                $icone = 'icon-panier_moins';               
            }
            /* retrouver la boutique */ 
            $boutique = select_bdd($bdd, "boutiques", $where = "id = '".$donnee['boutique']."'", $limit = null, $offset = 0, $order = null, $random = false);
            if($boutique)
            {
                $boutique_nom = $boutique[0]['nom'];
                $boutique_slug = $boutique[0]['slug'];
            }
            else
            {
                $boutique_nom = "OhNous";
                $boutique_slug = "/accueil";
            }

            $article = '
                <div class="div_affiche_produit">
                    <div class="affiche_produit">
                        <!-- image -->				
                        <div class="div_img_affiche_produit" id="'.$divImgId.'" style="background: '.$imgBackground.';">
                            <a href="/article/'.$donnee['slug'].'">
                                <img 
                                    crossorigin="anonymous"
                                    src="'.$liquid_image['placeholder'].'"
                                    alt="'.$donnee['slug'].'" 
                                    class="img_affiche blur-up js-liquid-image"
                                    data-img ="'.$img[0]['img'].'"
                                    data-image-base="'.$liquid_image['base'].'"
                                    data-image-fallback="'.$liquid_image['fallback'].'"
                                    data-image-high="'.$liquid_image['high'].'"
                                    data-image-srcset="'.$liquid_image['srcset'].'"
                                    data-image-sizes="'.$liquid_image['sizes'].'"
                                    id="'.$imgId.'"
                                    style="'.$imgStyles.'"
                                    loading="lazy"
                                >
                            </a>
                            '.$badge.'
                            <!-- like -->
                            <button type="button" class="like_affiche_produit">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>
                        <!-- details -->
                        <div class="div_details_affiche_produit">
                            <div class="nom">'.$donnee['nom'].'</div>
                            <!-- panier prix tailles -->
                            <div class="details_affiche_produit">
                                <a href="/boutique/'.$boutique_slug.'" class="boutique"><i class="fa-solid fa-store"></i> '.$boutique_nom.'</a>
                                <div class="prix_taille">
                                    <div class="taille">'.$tailles.'</div>
                                    <div class="prix">$ '.$donnee['prix'].'</div>
                                </div>
                                <div class="boutton_panier_affiche_produit">
                                    <button type="button" class="'.$panier.'" id="btn_panier_'.$donnee['id'].'" onclick=\'ajouterAuPanier('.json_encode($img[0]['img']).','.(int)$donnee['id'].','.json_encode($donnee['nom']).','.json_encode($donnee['slug']).','.json_encode($tailles).','.(int)$donnee['prix'].','.json_encode($imgStyles).','.json_encode($imgBackground).')\'><span class="'.$icone.'"></span></button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>             
                ';

            /* echo s'il faut pas return */
            if(!$return)
            {
                echo $article;
            }
            else
            {
                return $article;
            }
        }
    }
?>
