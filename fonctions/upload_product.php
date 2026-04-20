<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['store_ohnous_987654321']))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Vous n'êtes plus connecté."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $boutique = only_select("boutiques", "unique_id = '".$_SESSION['store_ohnous_987654321']."'", null, null);
    if(!$boutique)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Boutique introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!ohnous_is_store_active($boutique))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Votre boutique doit être active pour publier des articles."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $product_name = html_entity_decode(filter_var($_POST['product_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_price = html_entity_decode(filter_var($_POST['product_price'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_category = html_entity_decode(filter_var($_POST['product_category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_types = html_entity_decode(filter_var($_POST['product_types'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_tailles = html_entity_decode(filter_var($_POST['product_tailles'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_description = html_entity_decode(filter_var($_POST['product_description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $promo_actif = (int)html_entity_decode(filter_var($_POST['promo_actif'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $promo_prix = html_entity_decode(filter_var($_POST['promo_prix'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_images_json = $_POST['product_images'] ?? '';

    if(trim($product_name) === '' || trim($product_price) === '' || (int)$product_category <= 0)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Le nom, le prix et la catégorie sont obligatoires."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($promo_actif === 1 && trim((string)$promo_prix) === '')
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Entrez le prix promotionnel de l'article."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($promo_actif === 1 && trim((string)$promo_prix) !== '' && (float)$promo_prix >= (float)$product_price)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Le prix promotionnel doit être inférieur au prix normal."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $productImages = json_decode($product_images_json, true);
    if(!is_array($productImages) || empty($productImages))
    {
        $fallbackUrl = html_entity_decode(filter_var($_POST['product_image_url'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        if($fallbackUrl !== '')
        {
            $productImages = [[
                'url' => $fallbackUrl,
                'style' => html_entity_decode(filter_var($_POST['style'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
                'background' => html_entity_decode(filter_var($_POST['background'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
                'fileId' => html_entity_decode(filter_var($_POST['fileId'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS))
            ]];
        }
    }

    if(empty($productImages))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Ajoutez au moins une image."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $slug = generateSlug($product_name);
    $unique_id = uniqid("article_");

    $insert_data = [
        "nom" => $product_name,
        "unique_id" => $unique_id,
        "slug" => $slug,
        "prix" => $product_price,
        "description" => $product_description,
        "reserve" => 1,
        "boutique" => (int)$boutique['id'],
    ];

    /* garder le flux compatible avec une base déjà en production */
    if(ohnous_column_exists('articles', 'promo_actif'))
    {
        $insert_data['promo_actif'] = $promo_actif === 1 ? 1 : 0;
    }

    if(ohnous_column_exists('articles', 'promo_prix'))
    {
        $insert_data['promo_prix'] = $promo_actif === 1 ? $promo_prix : null;
    }

    insert_bdd($bdd, "articles", $insert_data);

    $article = only_select("articles", "unique_id = '".$unique_id."'", null, null);
    if(!$article)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Impossible de créer l'article."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(trim($product_tailles) !== "")
    {
        $tailles = explode(',', $product_tailles);
        foreach ($tailles as $taille)
        {
            insert_bdd($bdd, "taille_articles", [
                "article" => (int)$article['id'],
                "taille" => (int)$taille,
            ]);
        }
    }

    if((int)$product_types > 0)
    {
        insert_bdd($bdd, "types_article", [
            "article" => (int)$article['id'],
            "types" => (int)$product_types,
        ]);
    }

    insert_bdd($bdd, "categorie_article", [
        "article" => (int)$article['id'],
        "categorie" => (int)$product_category,
    ]);

    foreach($productImages as $index => $image)
    {
        if(empty($image['url']))
        {
            continue;
        }

        $insert = [
            "article" => (int)$article['id'],
            "img" => $image['url'],
            "alt_text" => $slug,
            "background" => $image['background'] ?? '',
            "styles" => $image['style'] ?? '',
        ];

        if(ohnous_column_exists('image_articles', 'fileId'))
        {
            $insert['fileId'] = $image['fileId'] ?? '';
        }

        if(ohnous_column_exists('image_articles', 'display_order'))
        {
            $insert['display_order'] = $index + 1;
        }

        if(ohnous_column_exists('image_articles', 'is_primary'))
        {
            $insert['is_primary'] = $index === 0 ? 1 : 0;
        }

        insert_bdd($bdd, "image_articles", $insert);
    }

    echo json_encode([
        "result" => "ok",
        "msg" => "Article ajouté avec succès.",
        "slug" => $slug
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
