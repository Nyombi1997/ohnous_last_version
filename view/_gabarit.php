<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_page; ?></title>
    <!-- fav icone -->
    <link rel="icon" type="image/png" href="<?php echo ASSET; ?>images/icons/favicon-1.png"/>
    <!-- fontawesome -->
    <link rel="stylesheet" href="<?= ASSET ?>css/fontawesome/css/all.min.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/fontawesome/css/all.min.css") ?>">
    <!-- fontedo -->
    <link rel="stylesheet" href="<?= ASSET ?>css/fontedo/style.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/fontedo/style.css") ?>">
    <!-- css -->
    <link rel="stylesheet" href="<?= ASSET ?>css/style.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/style.css") ?>">
    <link rel="stylesheet" href="<?= ASSET ?>css/responsive.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/responsive.css") ?>">
    <!-- swiper -->
    <link rel="stylesheet" href="<?= ASSET ?>css/swiper.min.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/swiper.min.css") ?>">
    <script src="<?= ASSET ?>js/swiper-bundle.min.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/swiper-bundle.min.js") ?>"></script>
    <script src="https://unpkg.com/@imagekit/javascript@5.0.0/dist/imagekit.min.js"></script>
    <!-- jquery -->
    <script src="<?= ASSET ?>js/jquery-2.2.4.min.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/jquery-2.2.4.min.js") ?>"></script>
    <!-- sweat alert -->
    <link rel="stylesheet" href="<?= ASSET ?>css/sweetalert2.min.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/sweetalert2.min.css") ?>">
    <script src="<?= ASSET ?>js/sweetalert2.all.min.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/sweetalert2.all.min.js") ?>"></script>
    <script src="<?= ASSET ?>js/image_loader.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/image_loader.js") ?>" defer></script>
    <script src="<?= ASSET ?>js/article_swipers.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/article_swipers.js") ?>" defer></script>
    <script src="<?= ASSET ?>js/account_interactions.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/account_interactions.js") ?>" defer></script>
    <!-- script panier -->
	<script src="/asset/js/main_panier_produit.js?<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/asset/js/main_panier_produit.js') ?>" defer></script>
    <!-- script search bar -->
	<script src="/asset/js/script_search_bar.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/script_search_bar.js") ?>" defer></script> 
    <!-- script filtre produit -->
	<script src="/asset/js/filtre_produit.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/filtre_produit.js") ?>" defer></script> 
    <!-- fournir la route -->
    <script>
        const root_site = '<?=  $_SERVER['DOCUMENT_ROOT']  ?>';
        window.ohnousSession = <?= json_encode(ohnous_get_current_account(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
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
                        $cartItems = ohnous_get_cart_items();
                        if(!empty($cartItems))
                        {
                            foreach ($cartItems as $itemKey => $item) {
                                $itemDomId = 'detail_panier_'.md5((string)$itemKey);
                                $liquid_image = ohnous_prepare_liquid_image($item['image'], '(max-width: 768px) 35vw, 180px');

                                $articleUrl = '/article/'.rawurlencode((string)$item['slug']);

                                echo '
                                    <div class="detail_panier" id="'.$itemDomId.'" data-cart-key="'.htmlspecialchars((string)$itemKey, ENT_QUOTES, 'UTF-8').'">
                                        <div class="div_img_detail_panier" style="background: '.$item['background'].'">
                                            <img
                                                class="blur-up js-liquid-image"
                                                src="'.$liquid_image['placeholder'].'"
                                                data-image-base="'.$liquid_image['base'].'"
                                                data-image-fallback="'.$liquid_image['fallback'].'"
                                                data-image-high="'.$liquid_image['high'].'"
                                                data-image-srcset="'.$liquid_image['srcset'].'"
                                                data-image-sizes="'.$liquid_image['sizes'].'"
                                                loading="lazy"
                                                style="'.$item['style'].'"
                                                alt="article/'.$item['slug'].'"
                                            />
                                            <button
                                                type="button"
                                                class="div_supp_produit_panier js-remove-cart-item"
                                                data-cart-key="'.htmlspecialchars((string)$itemKey, ENT_QUOTES, 'UTF-8').'"
                                                data-product-id="'.(int)$item['id'].'"
                                                data-product-size="'.htmlspecialchars((string)$item['size'], ENT_QUOTES, 'UTF-8').'"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="infos_detail_panier">
                                            <a href="'.$articleUrl.'" class="titre_produit_detail_panier_link">'.$item['name'].'</a>
                                            <p class="prix_produit_detail_panier">$ <span class="prix-panier">'.number_format(((float)$item['price']) * max(1, (int)$item['qty']), 2, '.', ' ').'</span></p>
                                            <p class="taille_produit_detail_panier">'.($item['size'] !== '' ? $item['size'] : 'Taille non pr&eacute;cis&eacute;e').'</p>
                                            <p class="taille_produit_detail_panier">Quantit&eacute; : '.max(1, (int)$item['qty']).'</p>
                                        </div>
                                    </div>';
                            }
                            $nombre_article = count($cartItems);
                            $total = ohnous_get_items_total($cartItems);
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
                            <a href="/checkout?mode=cart">Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header -->
    <header class=" <?php if(isset($GLOBALS['categorie'])){ echo 'sans_categorie';}else if(isset($GLOBALS['others'])){ echo 'sans_categorie';}  ?>">
        <!-- logo -->
        <div class="logo">
                <a href="/accueil"><img src="<?php echo ASSET; ?>images/icons/logo-2.png" loading="lazy" alt="Logo OhNous"></a>
            <!-- menu avec panier -->
            <div class="menu_banniere_droit">
                <?php
                    $headerAccount = ohnous_get_current_account();
                    echo '
                        <a href="'.$headerAccount['link'].'" class="menu_banniere_link" id="menu_account_link" data-account-type="'.htmlspecialchars((string)$headerAccount['type'], ENT_QUOTES, 'UTF-8').'">
                            '.$headerAccount['icon_html'].'
                        </a>';
                ?>
                <a href="#" class="menu_banniere_link" id="afficher_panier"><i class="fa fa-shopping-bag"></i><span id="nombre_total_panier"><?= $nombre_article ?></span></a>
            </div>
        </div>
        <!-- categories -->
        <div class="banniere <?php if(isset($GLOBALS['categorie'])){ echo 'null';}else if(isset($GLOBALS['others'])){ echo 'null';}  ?>">
            <div class="categories">
                <!-- Swiper -->
                <div class="swiper categories_swiper">
                    <div class="swiper-wrapper">
                        <?php
                            $all_categories = array();
                            /* afficher les categories */
                            $categories = select_bdd($bdd, "categorie_article", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
                            $category_ids = array();
                            foreach ($categories as $category) {
                                $detail_category = only_select("categorie", $where = "id = '".$category['categorie']."'", $order = null, $limit = null);
                                $category_article = only_select("articles", "id = '".(int)$category['article']."'", null, null);
                                if(!$detail_category || !$category_article || !ohnous_is_article_visible($category_article)) {
                                    continue;
                                }
                                if(in_array($detail_category['id'], $category_ids)) {
                                    continue; // Passer à l'itération suivante si l'ID de catégorie a déjà été traité.
                                }
                                echo '
                                    <a href="/shop?categorie='.rawurlencode((string)$detail_category['slug']).'" class="swiper-slide">'.$detail_category['nom'].'</a>';
                                $category_ids[] = $detail_category['id'];
                                $all_categories[] = $detail_category['nom'];
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
    <?php if(isset($GLOBALS['categorie'])){ 
                echo '
                    <!-- intro -->
                    <div class="intro-hero sans_categorie">
                        <div class="blob-bg"></div>
                        <div class="intro-text">
                            <h1><span id="changing-word-container"><span id="changing-word">'.$categorie['nom'].'</span></span></h1>
                        </div>
                    </div>';
            }else if(isset($GLOBALS['others']) && !isset($GLOBALS['no_filtre'])){ 
                echo '
                    <!-- intro -->
                    <div class="intro-hero sans_categorie">
                        <div class="blob-bg"></div>
                        <div class="intro-text">
                            <h1><span id="changing-word-container"><span id="changing-word">Articles</span></span></h1>
                        </div>
                    </div>';
            }
    ?>
    <!-- afficher le contenue -->
    <?php echo $contentPage; ?>
	<!-- barre de recherche -->
	<div class="div_search_bar all <?php if(isset($GLOBALS['categorie'])){ echo 'sans_categorie';}else if(isset($GLOBALS['others'])){ echo 'sans_categorie';}  ?>" id="div_search_bar_all">
		<div class="search_bar">
			<form action="/shop" method="GET" onsubmit="return handleShopSearchSubmit(event)">
				<input type="text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="input_search_bar" id="input_search_bar_2" name="query" placeholder="Rechercher un article..." required oninput="rechercheArticles(this.value)" onfocus="rechercheArticles(this.value)" value=<?php if(isset($_GET['query'])){ echo json_encode($_GET['query']); } ?>>
				<button type="submit" class="button_search_bar"><i class="fa fa-search"></i></button>
			</form>
            <!-- div des données de recherche -->
            <div class="donnee_de_recherche null" id="donnee_de_recherche">

            </div>
		</div>
	</div>
</body>
</html>

<!-- création des tables slugs -->
<?php
    /* ajouter dans types */
    $table = "types";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE types
            ADD slug TEXT NULL AFTER nom
        ");
    }
    /* ajouter dans tailles */
    $table = "tailles";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE tailles
            ADD slug TEXT NULL AFTER nom
        ");
    }
    /* ajouter dans categorie */
    $table = "categorie";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE categorie
            ADD slug TEXT NULL AFTER nom
        ");
    }
    /* ajouter dans boutiques */
    $table = "boutiques";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD slug TEXT NULL AFTER nom
        ");
    }
?>

<!-- ajouter des slugs -->
<?php
    //types
    $types = select_bdd($bdd, "types", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
    foreach($types as $type)
    {
        if($type['slug'] == '' || $type['slug'] == NULL)
        {
            $slug = generateSlug($type['nom'],$separator = '-');
            $update_data = [
                "slug" => $slug
            ];
            update_bdd($bdd, "types", $update_data, "id = '".$type['id']."'");
        }
    }
    //tailles
    $tailles = select_bdd($bdd, "tailles", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
    foreach($tailles as $taille)
    {
        if($taille['slug'] == '' || $taille['slug'] == NULL)
        {
            $slug = generateSlug($taille['nom'],$separator = '-');
            $update_data = [
                "slug" => $slug
            ];
            update_bdd($bdd, "tailles", $update_data, "id = '".$taille['id']."'");
        }
    }
    //categorie
    $categorie = select_bdd($bdd, "categorie", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
    foreach($categorie as $categories)
    {
        if($categories['slug'] == '' || $categories['slug'] == NULL)
        {
            $slug = generateSlug($categories['nom'],$separator = '-');
            $update_data = [
                "slug" => $slug
            ];
            update_bdd($bdd, "categorie", $update_data, "id = '".$categories['id']."'");
        }
    }
    //boutiques
    $boutiques = select_bdd($bdd, "boutiques", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
    foreach($boutiques as $boutique)
    {
        if($boutique['slug'] == '' || $boutique['slug'] == NULL)
        {
            $slug = generateSlug($boutique['nom'],$separator = '-');
            $update_data = [
                "slug" => $slug
            ];
            update_bdd($bdd, "boutiques", $update_data, "id = '".$boutique['id']."'");
        }
    }
?>

<!-- créer la table note article si nécessaire -->
<?php
    createTable('notes_article', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'client_id INT NOT NULL',
        'article_id INT NOT NULL',
        'note DOUBLE NOT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
    createTable('messages', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'client_id INT NOT NULL',
        'boutique_id INT NOT NULL',
        'from_id INT NOT NULL',
        'messages TEXT NOT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
    createTable('bienvenue_email', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'client_unique_id TEXT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
    createTable('admins', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'email TEXT NULL',
        'mdp TEXT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
    createTable('article_likes', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'article_id INT NOT NULL',
        'account_id INT NOT NULL',
        'account_type VARCHAR(30) NOT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
    createTable('boutique_activation_requests', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'boutique_id INT NOT NULL',
        'token TEXT NULL',
        'statut VARCHAR(30) NOT NULL DEFAULT "en_attente"',
        'duree_jours INT NOT NULL DEFAULT 0',
        'date_traitement DATETIME NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
    createTable('utilisateur', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'unique_id TEXT NULL',
        'nom TEXT NULL',
        'adresse_email TEXT NULL',
        'mdp TEXT NULL',
        'code_password TEXT NULL',
        'description TEXT NULL',
        'slug TEXT NULL',
        'profile TEXT NULL',
        'field TEXT NULL',
        'backgrounds TEXT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);
?>

<!-- création des tables slugs, background -->
<?php
    /* ajouter dans types */
    $table = "types";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE types
            ADD slug TEXT NULL AFTER nom
        ");
    }
    /* ajouter dans tailles */
    $table = "tailles";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE tailles
            ADD slug TEXT NULL AFTER nom
        ");
    }
    /* ajouter dans categorie */
    $table = "categorie";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE categorie
            ADD slug TEXT NULL AFTER nom
        ");
    }
    /* ajouter dans boutiques */
    $table = "boutiques";
    $column = "slug";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD slug TEXT NULL AFTER nom
        ");
    }

    /* ajouter backgrounds dans boutiques */
    $table = "boutiques";
    $column = "backgrounds";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD backgrounds TEXT NULL AFTER slug
        ");
    }

    /* ajouter facebook dans boutiques */
    $table = "boutiques";
    $column = "backgrounds";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD facebook TEXT NULL AFTER slug
        ");
    }

    /* ajouter twitter dans boutiques */
    $table = "boutiques";
    $column = "twitter";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD twitter TEXT NULL AFTER slug
        ");
    }

    /* ajouter trends dans boutiques */
    $table = "boutiques";
    $column = "trends";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD trends TEXT NULL AFTER slug
        ");
    }

    /* ajouter instagram dans boutiques */
    $table = "boutiques";
    $column = "instagram";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD instagram TEXT NULL AFTER slug
        ");
    }

    /* ajouter whatsapp dans boutiques */
    $table = "boutiques";
    $column = "whatsapp";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD whatsapp TEXT NULL AFTER slug
        ");
    }

    /* ajouter tiktok dans boutiques */
    $table = "boutiques";
    $column = "tiktok";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD tiktok TEXT NULL AFTER slug
        ");
    }

    /* ajouter profile dans boutiques */
    $table = "boutiques";
    $column = "profile";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD profile TEXT NULL AFTER slug
        ");
    }

    /* ajouter unique_id dans boutiques */
    $table = "boutiques";
    $column = "unique_id";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD unique_id TEXT NULL AFTER id
        ");
    }

    /* ajouter lu dans messages */
    $table = "messages";
    $column = "lu";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE messages
            ADD lu INT NOT NULL AFTER messages
        ");
    }

    /* ajouter fileId dans boutiques */
    $table = "boutiques";
    $column = "fileId";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD fileId TEXT NULL AFTER profile
        ");
    }

    /* ajouter fileId dans image_articles */
    $table = "image_articles";
    $column = "fileId";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE image_articles
            ADD fileId TEXT NULL AFTER img
        ");
    }

    /* ajouter code_password dans boutiques */
    $table = "boutiques";
    $column = "code_password";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD code_password TEXT NULL AFTER mdp
        ");
    }

    /* ajouter activer dans boutiques */
    $table = "boutiques";
    $column = "activer";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD activer INT NOT NULL AFTER backgrounds
        ");
    }

    /* ajouter date_activation_debut dans boutiques */
    $table = "boutiques";
    $column = "date_activation_debut";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD date_activation_debut DATE NULL AFTER activer
        ");
    }

    /* ajouter date_activation_fin dans boutiques */
    $table = "boutiques";
    $column = "date_activation_fin";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD date_activation_fin DATE NULL AFTER date_activation_debut
        ");
    }

    /* ajouter telephone_whatsapp dans boutiques */
    $table = "boutiques";
    $column = "telephone_whatsapp";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            ADD telephone_whatsapp TEXT NULL AFTER whatsapp
        ");
    }

    /* ajouter fileId dans utilisateur */
    $table = "utilisateur";
    $column = "fileId";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();
    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE utilisateur
            ADD fileId TEXT NULL AFTER profile
        ");
    }

    /* ajouter commentaire dans notes_article */
    $table = "notes_article";
    $column = "commentaire";

    $sql = "
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE notes_article
            ADD commentaire TEXT NULL AFTER note
        ");
    }

    /* ajouter client_type dans notes_article */
    $table = "notes_article";
    $column = "client_type";

    $sql = "
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        $bdd->exec("
            ALTER TABLE notes_article
            ADD client_type VARCHAR(30) NULL AFTER client_id
        ");
    }
?>

<?php
    /* retirer nom_utilisateur dans boutiques */
    $table = "boutiques";
    $column = "nom_utilisateur";

    $sql = "
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = :table
        AND COLUMN_NAME = :column
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        ':table'  => $table,
        ':column' => $column
    ]);

    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        $bdd->exec("
            ALTER TABLE boutiques
            DROP COLUMN nom_utilisateur
        ");
    }

?>

