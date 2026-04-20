<?php
    /* affiche produit */
    function affiche_produit($donnee=null , $return = false, $options = []) {
        global $bdd;
        /* si une donnÃ©e est envoyÃ© */
        if($donnee)
        {
            $allowHiddenForOwner = !empty($options['allow_hidden_for_owner']) && ohnous_can_manage_article($donnee);
            $showOwnerActions = !empty($options['show_owner_actions']);
            if(!ohnous_is_article_visible($donnee) && !$allowHiddenForOwner)
            {
                return '';
            }

            $img = ohnous_get_article_images((int)$donnee['id']);
            if(empty($img))
            {
                return '';
            }
            $imgBackground = $img[0]['background'];
            $imgStyles = $img[0]['styles'];
            $pricing = ohnous_get_article_pricing($donnee);
            /* badge */
            $difference_date = difference_date($donnee['date_ajout'], date("Y-m-d H:i:s"));
            $badge = '';
            if($difference_date<1)
            {
                $badge = '
                    <!-- info -->
                    <span class="info_affiche_produit new">Nouveau</span>';
            }
            if($pricing['promo_actif'])
            {
                $badge .= '
                    <span class="info_affiche_produit promo">Promotion -'.$pricing['reduction'].'%</span>';
            }
            if($allowHiddenForOwner && (int)($donnee['reserve'] ?? 1) !== 1)
            {
                $badge .= '
                    <span class="info_affiche_produit reserve">Masqué</span>';
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
                        <div class="div_img_affiche_produit">
                            '.ohnous_render_article_gallery((int)$donnee['id'], (string)$donnee['slug'], 'card', '/article/'.$donnee['slug'], $img).'
                            '.$badge.'
                            '.ohnous_render_article_admin_edit_link($donnee['id'], 'card').'
                            '.($showOwnerActions ? ohnous_render_store_article_manage_actions($donnee, 'card') : '').'
                            <!-- like -->
                            '.ohnous_render_like_button($donnee['id'], 'card').'
                        </div>
                        <!-- details -->
                        <div class="div_details_affiche_produit">
                            <div class="nom">'.$donnee['nom'].'</div>
                            '.ohnous_render_article_rating_summary($donnee['id'], 'card').'
                            <!-- panier prix tailles -->
                            <div class="details_affiche_produit">
                                <a href="/boutique/'.$boutique_slug.'" class="boutique"><i class="fa-solid fa-store"></i> '.$boutique_nom.'</a>
                                <div class="prix_taille">
                                    <div class="taille">'.$tailles.'</div>
                                    <div class="prix '.($pricing['promo_actif'] ? 'promo' : '').'">
                                        '.($pricing['promo_actif']
                                            ? '<span class="old-price">$ '.number_format($pricing['prix_initial'], 2, '.', ' ').'</span><span class="new-price">$ '.number_format($pricing['prix_final'], 2, '.', ' ').'</span>'
                                            : '$ '.number_format($pricing['prix_final'], 2, '.', ' ')
                                        ).'
                                    </div>
                                </div>
                                <div class="boutton_panier_affiche_produit">
                                    <button type="button" class="'.$panier.'" id="btn_panier_'.$donnee['id'].'" onclick="ajouterAuPanier('.ohnous_js_html_arg($img[0]['img']).','.(int)$donnee['id'].','.ohnous_js_html_arg($donnee['nom']).','.ohnous_js_html_arg($donnee['slug']).','.ohnous_js_html_arg($tailles).','.ohnous_js_html_arg((string)$pricing['prix_final']).','.ohnous_js_html_arg($imgStyles).','.ohnous_js_html_arg($imgBackground).')"><span class="'.$icone.'"></span></button>
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
