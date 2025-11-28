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
    <!-- css -->
    <link rel="stylesheet" href="<?= ASSET ?>css/style.css?<?= filemtime("./asset/css/style.css") ?>">
    <link rel="stylesheet" href="<?= ASSET ?>css/responsive.css?<?= filemtime("./asset/css/responsive.css") ?>">
    <!-- swiper -->
    <link rel="stylesheet" href="<?= ASSET ?>css/swiper.min.css?<?= filemtime("./asset/css/swiper.min.css") ?>">
    <script src="<?= ASSET ?>js/swiper.min.js?<?= filemtime("./asset/js/swiper.min.js") ?>"></script>
</head>
<body>
    <!-- slide panier -->
    <div class="div_slide_panier">
        <div class="background"></div>
        <div class="slide_panier">
            <div class="contenu_slide_panier">
                <!-- div sortie -->
                <div class="div_sortie_slide_panier">
                    <button class="button_sortie_slide_panier"><i class="fa fa-close"></i></button>
                </div>
                <h2>Votre panier est vide</h2>                
            </div>
        </div>
    </div>
    <header>
        <!-- logo -->
        <div class="logo">
            <a href=""><img src="<?php echo ASSET; ?>images/icons/logo-2.png" loading="lazy" alt="Logo OhNous"></a>
            <!-- menu avec panier -->
            <div class="menu_banniere_droit">
                <a href="" class="menu_banniere_link"><i class="fa fa-user"></i></a>
                <a href="#" class="menu_banniere_link"><i class="fa fa-shopping-bag"></i><span>0</span></a>
            </div>
        </div>
        <!-- categories -->
        <div class="banniere">
            <div class="categories">
                <!-- Swiper -->
                <div class="swiper categories_swiper">
                    <div class="swiper-wrapper">
                        <a href="" class="swiper-slide">Slide 1</a>
                        <a href="" class="swiper-slide">Slide 2</a>
                        <a href="" class="swiper-slide">Slide 3</a>
                        <a href="" class="swiper-slide">Slide 4</a>
                        <a href="" class="swiper-slide">Slide 5</a>
                        <a href="" class="swiper-slide">Slide 6</a>
                        <a href="" class="swiper-slide">Slide 7</a>
                        <a href="" class="swiper-slide">Slide 8</a>
                        <a href="" class="swiper-slide">Slide 9</a>
                        <a href="" class="swiper-slide">Slide 10</a>
                        <a href="" class="swiper-slide">Slide 11</a>
                        <a href="" class="swiper-slide">Slide 12</a>
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
</body>
</html>