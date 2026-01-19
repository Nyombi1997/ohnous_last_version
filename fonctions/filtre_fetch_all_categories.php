<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    
    /* afficher les categories */
    $categories = select_bdd($bdd, "categorie", $where = null, $limit = null, $offset = 0, $order = "nom", $random = false);
    $html_categorie = "";
    $categorie_id = 0;
    $found = false;
    if(isset($_POST['categorie']))
    {
        $categorie_id = filter_input(INPUT_POST, 'categorie', FILTER_VALIDATE_INT);
    }
    foreach ($categories as $category) {
        $categories_nombre = select_bdd($bdd, "categorie_article", $where = "categorie = '".$category['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
        if(count($categories_nombre) == 0)
        {
            continue;
        }
        $active = "";
        if($category['id'] == $categorie_id)
        {
            $active = "active";
        }
        $html_categorie .= '
        <div class="detail_liste_filtre_produit '.$active.' js_detail_liste_filtre_produit js_detail_liste_filtre_produit_'.$category['id'].'" onclick=\'filtre_categorie('.(int)$category['id'].','.json_encode($category['nom']).','.json_encode($category['slug']).')\'>
            <div class="nom">'.$category['nom'].'</div> <div class="nombre">'.count($categories_nombre).'</div>
        </div>';
    }

    echo json_encode([
        "result" => "ok",
        "msg" => $html_categorie,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>