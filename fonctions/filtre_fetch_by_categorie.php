<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');

    $categorie_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$categorie_id) {
        echo json_encode([
            "result" => "error",
            "msg" => "Categorie d'article invalide"
        ]);
        exit;
    }
    /* trouver les types */
    $sql = "
        SELECT 
            t.id,
            t.nom,
            t.slug,
            COUNT(DISTINCT a.id) AS total
        FROM types t
        INNER JOIN types_article ta ON ta.types = t.id
        INNER JOIN articles a ON a.id = ta.article
        INNER JOIN boutiques bo ON bo.id = a.boutique AND bo.activer = 1
        INNER JOIN categorie_article ca ON ca.article = a.id
        WHERE ca.categorie = :categorie_id
        GROUP BY t.id
        HAVING total > 0
        ORDER BY t.nom ASC
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':categorie_id', $categorie_id, PDO::PARAM_INT);
    $stmt->execute();

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html_type = "";

    if ($types) {
        foreach ($types as $type) {
            $html_type .= '
                <div class="detail_liste_filtre_produit 
                            js_detail_liste_filtre_produit_types 
                            js_detail_liste_filtre_produit_types'.$type['id'].'"
                    onclick=\'filtre_types('.(int)$type['id'].','.json_encode($type['nom']).','.json_encode($type['slug']).')\'>
                    <div class="nom">'.$type['nom'].'</div>
                    <div class="nombre">'.$type['total'].'</div>
                </div>';
        }
    }
    /* afficher les categories */
    $categories = select_bdd($bdd, "categorie", $where = null, $limit = null, $offset = 0, $order = "nom", $random = false);
    $html_categorie = "";
    $in_filtre = false;
    foreach ($categories as $category) {
        $active = '';
        if($category['id'] == $categorie_id)
        {
            $in_filtre = true;
            $active = 'active';
        }
        $categories_nombre = 0;
        $categoryArticles = select_bdd($bdd, "categorie_article", $where = "categorie = '".$category['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
        foreach($categoryArticles as $categoryArticle)
        {
            $detailArticle = only_select("articles", "id = '".(int)$categoryArticle['article']."'", null, null);
            if($detailArticle && ohnous_is_article_visible($detailArticle))
            {
                $categories_nombre++;
            }
        }
        if($categories_nombre == 0)
        {
            continue;
        }
        $html_categorie .= '
        <div class="detail_liste_filtre_produit '.$active.' js_detail_liste_filtre_produit js_detail_liste_filtre_produit_'.$category['id'].'" onclick=\'filtre_categorie('.(int)$category['id'].','.json_encode($category['nom']).','.json_encode($category['slug']).')\'>
            <div class="nom">'.$category['nom'].'</div> <div class="nombre">'.$categories_nombre.'</div>
        </div>';
    }
    if($in_filtre==false)
    {
        $categories[0] = select_bdd($bdd, "categorie", $where = "id = '$categorie_id'", $limit = null, $offset = 0, $order = "nom", $random = false);
        $html_categorie = '
        <div class="detail_liste_filtre_produit active js_detail_liste_filtre_produit js_detail_liste_filtre_produit_'.$categories[0]['id'].'" onclick=\'filtre_categorie('.(int)$categories['id'].','.json_encode($categories['nom']).','.json_encode($categories['slug']).')\'>
            <div class="nom">'.$categories[0]['nom'].'</div> <div class="nombre">'.count($categories).'</div>
        </div>';
    }

    echo json_encode([
        "result" => "ok",
        "msg" => $html_type,
        "msg2" => $html_categorie,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
