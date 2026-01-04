<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=  $title_page ?></title>
    <!-- fav icone -->
    <link rel="icon" type="image/png" href="<?php echo ASSET; ?>images/icons/favicon-1.png"/>
    <!-- fontawesome -->
    <link rel="stylesheet" href="<?= ASSET ?>css/fontawesome/css/all.min.css?<?= filemtime("./asset/css/fontawesome/css/all.min.css") ?>">
    <!-- fontedo -->
    <link rel="stylesheet" href="<?= ASSET ?>css/fontedo/style.css?<?= filemtime("./asset/css/fontedo/style.css") ?>">
    <!-- css -->
    <link rel="stylesheet" href="<?= ASSET ?>css/style.css?<?= filemtime("./asset/css/style.css") ?>">
    <link rel="stylesheet" href="<?= ASSET ?>css/responsive.css?<?= filemtime("./asset/css/responsive.css") ?>">
    <!-- swiper -->
    <link rel="stylesheet" href="<?= ASSET ?>css/swiper.min.css?<?= filemtime("./asset/css/swiper.min.css") ?>">
    <script src="<?= ASSET ?>js/swiper.min.js?<?= filemtime("./asset/js/swiper.min.js") ?>"></script>
    <script src="https://unpkg.com/@imagekit/javascript@5.0.0/dist/imagekit.min.js"></script>​​
    <!-- jquery -->
    <script src="<?= ASSET ?>js/jquery-2.2.4.min.js?<?= filemtime("./asset/js/jquery-2.2.4.min.js") ?>"></script>
    <!-- sweat alert -->
    <link rel="stylesheet" href="<?= ASSET ?>css/sweetalert2.min.css?<?= filemtime("./asset/css/sweetalert2.min.css") ?>">
    <script src="<?= ASSET ?>js/sweetalert2.all.min.js?<?= filemtime("./asset/js/sweetalert2.all.min.js") ?>"></script>
</head>
<body>
    <!-- slide panier -->
    <div class="div_slide_panier" id="div_slide_panier">
        <div class="background" id="sortie_panier"></div>
        <div class="slide_panier">
            <div class="contenu_slide_panier" id="contenu_slide_panier">
                <!-- div sortie -->
                <div class="div_sortie_slide_panier" id="sortie_panier">
                    <button class="button_sortie_slide_panier btn_ohnous"><i class="fa fa-close"></i></button>
                </div>
                <!-- corps detail panier -->                
                <div class="corps_detail_panier" id="corps_detail_panier">
                    <!-- <h2 class="titre_panier">Votre panier est vide</h2> -->
                    <?php
                        $nombre_article = 0;
                        $total = 0;
                        if(isset($_SESSION['cart-ohnous-123456789']))
                        {
                            //session_destroy();
                            function displayCart() {
                                if (empty($_SESSION['cart-ohnous-123456789'])) {
                                    echo '<h2 class="titre_panier">Votre panier est vide</h2>';
                                    return;
                                }

                                $total = 0;

                                foreach ($_SESSION['cart-ohnous-123456789'] as $item) {
                                    $subtotal = $item['price'] * $item['qty'];
                                    $total += $subtotal;

                                    echo '
                                        <!-- images -->
                                        <div class="detail_panier" id="detail_panier_'.$item['id'].'">
                                            <div class="div_img_detail_panier" style="background: '.$item['background'].'">
                                                <img
                                                    class="blur-up"
                                                    src="'.$item['image'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                                    srcset="
                                                        '.$item['image'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                                        '.$item['image'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                                        '.$item['image'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                                                    sizes="(max-width:768px) 90vw, 600px"
                                                    loading="lazy"
                                                    style="'.$item['style'].'"
                                                    alt="'.$item['slug'].'"
                                                />
                                                <div class="div_supp_produit_panier" onclick="ajouterAuPanier(\''.$item['image'].'\',\''.$item['id'].'\',\''.$item['name'].'\',\''.$item['slug'].'\',\''.$item['size'].'\',\''.$item['price'].'\',\''.$item['style'].'\',\''.$item['background'].'\')">
                                                    <i class="fa fa-trash"></i>
                                                </div>
                                            </div>
                                            <!-- details -->
                                            <div class="infos_detail_panier">
                                                <p class="titre_produit_detail_panier">'.$item['name'].'</p>
                                                <p class="prix_produit_detail_panier">$ <span class="prix-panier">'.$item['price'].'</span></p>
                                                <p class="taille_produit_detail_panier">'.$item['size'].'</p>
                                            </div>
                                        </div>';
                                }
                                return $total;
                            }
                            $nombre_article = count($_SESSION['cart-ohnous-123456789']);
                            $total = displayCart();
                        }
                        else
                        {
                            echo '<h2 class="titre_panier">Votre panier est vide</h2>';
                        }
                    ?>
                </div>
                <!-- compte -->
                <div class="div_compte_panier">
                    <div class="div_total_panier">
                        <div class="total_panier btn_ohnous second">
                            <p>Total : </p>
                            <p class="prix_total_panier"><span id="prix_total_panier"><?= $total == 0 ? '0.00' : number_format($total, 2, '.', ' '); ?></span> <span>$</span></p>
                        </div>
                    </div>
                    <div class="div_total_panier">
                        <div class="total_panier btn_ohnous">
                            <a href="">Valider</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header -->
    <header>
        <!-- logo -->
        <div class="logo">
            <a href="accueil"><img src="<?php echo ASSET; ?>images/icons/logo-2.png" loading="lazy" alt="Logo OhNous"></a>
            <!-- menu avec panier -->
            <div class="menu_banniere_droit">
                <a href="" class="menu_banniere_link"><i class="fa fa-user"></i></a>
                <a href="#" class="menu_banniere_link" id="afficher_panier"><i class="fa fa-shopping-bag"></i><span id="nombre_total_panier"><?= $nombre_article ?></span></a>
            </div>
        </div>
        <!-- categories -->
        <div class="banniere">
            <div class="categories">
                <!-- Swiper -->
                <div class="swiper categories_swiper">
                    <div class="swiper-wrapper">
                        <?php
                            /* afficher les categories */
                            $categories = select_bdd($bdd, "categorie_article", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
                            $category_ids = array();
                            foreach ($categories as $category) {
                                $detail_category = only_select("categorie", $where = "id = '".$category['categorie']."'", $order = null, $limit = null);
                                if(in_array($detail_category['id'], $category_ids)) {
                                    continue; // Passer à l'itération suivante si l'ID de catégorie a déjà été traité
                                }
                                echo '
                                    <a href="'.$detail_category['slug'].'" class="swiper-slide">'.$detail_category['nom'].'</a>';
                                $category_ids[] = $detail_category['id'];
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var swiper = new Swiper('.categories_swiper', {
                slidesPerView: "auto",
                spaceBetween: 10,
                freeMode: true,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: true,
                }
            });
        </script>
    </header>
    <?php echo $contentPage; ?>   
    <!-- script panier -->
	<script src="./asset/js/main_panier_produit.js?<?= filemtime("./asset/js/main_panier_produit.js") ?>"></script>
    <!-- script search bar -->
	<script src="./asset/js/script_search_bar.js?<?= filemtime("./asset/js/script_search_bar.js") ?>"></script> 
</body>
</html>