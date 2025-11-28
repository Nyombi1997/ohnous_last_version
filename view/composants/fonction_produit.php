<?php
    /* affiche produit */
    function affiche_produit($donnee=null) {
        $bdd = Database::getConnection();
        if( !$donnee ) {
            return false;
            exit;
        }
        $id = $donnee['id'];
        $sortie = '';

        $img = only_select("image_articles", "article = '".$donnee['id']."'", $order = null, $limit = null);

        if ($img) {
            $img_version = select_bdd($bdd, "image_versions", "image_id = '".$img['unique_id']."'", $limit = null, $offset = 0, $order = null, $random = false);

            $image = $img['img'];
            $slug = $donnee['slug'];
            $sortie .= '
                    <div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item women">
                        <!-- Block2 -->
                        <div class="block2">
                            <div class="block2-pic hov-img0 label-new" data-label="Nouveau">
                                <a href="'.$slug.'">
                                    <img src="'.ASSET.'images/articles/'.$id.'/'.$image.'" alt="IMG-PRODUCT" loading="lazy">
                                </a>	
                                <a href="#" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1">
                                    Details rapide
                                </a>
                            </div> 

                            <div class="block2-txt flex-w flex-t p-t-14">
                                <div class="block2-txt-child1 flex-col-l ">
                                    <a href="product-detail.html" class="stext-104 cl1 hov-cl1 trans-04 p-b-6">
                                        <i class="fa-solid fa-store"></i> Boutique la fleure
                                    </a>
                                    <a href="product-detail.html" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                        Esprit Ruffle Shirt
                                    </a>

                                    <span class="stext-105 cl3">
                                        $16.64
                                    </span>
                                </div>

                                <div class="block2-txt-child2 flex-r p-t-3">
                                    <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2">
                                        <img class="icon-heart1 dis-block trans-04" src="'.ASSET.'images/icons/cart-in.png" alt="ICON">
                                        <img class="icon-heart2 dis-block trans-04 ab-t-l" src="'.ASSET.'images/icons/cart-out.png" alt="ICON">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>';
        }
        return $sortie;
    }
?>