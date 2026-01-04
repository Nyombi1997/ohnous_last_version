<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');

    $product_name = html_entity_decode(filter_var($_POST['product_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_price = html_entity_decode(filter_var($_POST['product_price'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_category = html_entity_decode(filter_var($_POST['product_category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_types = html_entity_decode(filter_var($_POST['product_types'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_tailles = html_entity_decode(filter_var($_POST['product_tailles'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_boutique = html_entity_decode(filter_var($_POST['product_boutique'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_description = html_entity_decode(filter_var($_POST['product_description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $product_image_url = html_entity_decode(filter_var($_POST['product_image_url'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $style = html_entity_decode(filter_var($_POST['style'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $background = html_entity_decode(filter_var($_POST['background'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $slug = generateSlug($product_name);
    $unique_id = uniqid("article_");
    /* ajouter l'article */
    $insert_data = [
        "nom" => $product_name,
        "unique_id" => $unique_id,
        "slug" => $slug,
        "prix" => $product_price,
        "description" => $product_description,
        "boutique" => $product_boutique,
    ];
    insert_bdd($bdd, "articles", $insert_data);
    /* ajouter les tailles */
    $id = only_select("articles", $where = "unique_id = '$unique_id'", $order = null, $limit = null);
    if($product_tailles == "")
    {
    }
    else
    {
        $tailles = explode(',', $product_tailles);
        foreach ($tailles as $taille)
        {
            $insert = [
                "article" => $id['id'],
                "taille" => $taille,
            ];
            insert_bdd($bdd, "taille_articles", $insert);
        }
    }
    /* ajouter image */
    $insert = [
        "article" => $id['id'],
        "img" => $product_image_url,
        "alt_text" => $slug,
        "background" => $background,
        "styles" => $style,
    ];
    insert_bdd($bdd, "image_articles", $insert);
    
    $insert = [
        "article" => $id['id'],
        "types" => $product_types,
    ];
    insert_bdd($bdd, "types_article", $insert);
    
    $insert = [
        "article" => $id['id'],
        "categorie" => $product_category,
    ];
    insert_bdd($bdd, "categorie_article", $insert);

    $results = [
        "result" => "ok",
        "msg" => $style
    ];

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>