<?php
    /* affiche produit */
    function affiche_produit($donnee=null) {
        global $bdd;
        if($donnee)
        {
            $img = select_bdd($bdd, "image_articles", $where = "article = '".$donnee['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
            echo '
                <div class="div_affiche_produit">
                    <div class="affiche_produit">
                        <!-- image -->				
                        <div class="div_img_affiche_produit">
                            <a href="">
                                <img 
                                    src="'.$img[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                    alt="" 
                                    class="img_affiche blur-up" 
                                    id="img_produit_'.$img[0]['id'].'"
                                    data-src="'.$img[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10"
                                >
                            </a>
                            <!-- info -->
                            <span class="info_affiche_produit new">New</span>
                            <!-- like -->
                            <button type="button" class="like_affiche_produit">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>
                        <!-- details -->
                        <div class="div_details_affiche_produit">
                            <div class="nom">petard</div>
                            <!-- panier prix tailles -->
                            <div class="details_affiche_produit">
                                <div class="prix_taille">
                                    <div class="taille">xl, xxl</div>
                                    <div class="prix">$200</div>
                                </div>
                                <div class="boutton_panier_affiche_produit">
                                    <button type="button"><span class="icon-panier_plus"></span></button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <script>
                    let img_'.$img[0]['id'].' = document.getElementById("img_produit_'.$img[0]['id'].'");
                    img_'.$img[0]['id'].'.onload = () => img_'.$img[0]['id'].'.classList.add(\'blur-up-loaded\');
                    /* recalculer la taille de l\'image pour lui donner un style */
                    function recalculateImageStyle(imgUrl) {
                        return new Promise((resolve) => {
                            const img = new Image();
                            img.src = imgUrl;

                            img.onload = () => {
                                if (img.width > img.height) {
                                    resolve(\'width: 100%; height: auto;\');
                                } else if (img.width < img.height) {
                                    resolve(\'width: auto; height: 100%;\');
                                } else {
                                    resolve(\'width: 100%; height: 100%;\');
                                }
                            };
                        });
                    }
                </script>                
                ';
        }
    }
?>