<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    include_once __DIR__ . "/../view/composants/fonction_produit.php";
    include_once __DIR__ . "/fonctions.php";
    $categorie = html_entity_decode(filter_var($_POST['categorie'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $types = html_entity_decode(filter_var($_POST['types'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $taille = html_entity_decode(filter_var($_POST['taille'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $boutique = html_entity_decode(filter_var($_POST['boutique'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $promotion = html_entity_decode(filter_var($_POST['promotion'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $recherche = html_entity_decode(filter_var($_POST['recherche'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $order = html_entity_decode(filter_var($_POST['order'] ?? 'date_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $offset = $_POST['offset'];
    header('Content-Type: application/json; charset=utf-8');

    $donnee = "";
    $nombre = 0;

    if($recherche!="")
    {
        $query =  found($recherche, 60, 0, $order, $random = false);
        $donnees = getArticlesFromSearch($query, $limit = 12, $offset, $order, $random = false);
        if((int)$promotion === 1)
        {
            $donnees = array_values(array_filter($donnees, function($article){
                return ohnous_is_article_on_promo($article);
            }));
        }
        foreach($donnees as $data)
        {
            $donnee .= affiche_produit($data, true);
        }
        $nombre = count($donnees);
    }
    else
    {
        $filters = [
            'category' => $categorie,
            'type' => $types,
            'taille' => $taille,
            'boutique' => $boutique,
            'promotion' => $promotion
        ];
        /* si y'a au moins une demande */
        if($categorie!=0 || $types!=0 || $taille!=0 || $boutique!=0 || $promotion!=0)
        {
            $msg = select_articles_filtre($bdd, $filters, $limit = 12, $offset, $order, $random = false);
            foreach($msg as $msg_)
            {
                $donnee .= affiche_produit($msg_ , true);
            }
            $nombre = count($msg);
        }
        else
        {
            $defaultOrder = $order === 'prix_desc' || $order === 'plus_chers'
                ? "prix DESC, id DESC"
                : ($order === 'prix_asc' ? "prix ASC, id DESC" : "date_ajout DESC, id DESC");
            $msg = ohnous_get_visible_articles(12, (int)$offset, $defaultOrder, false);
            foreach($msg as $msg_)
            {
                $donnee .= affiche_produit($msg_ , true);
            }
            $nombre = count($msg);
        }
    }
    $result = [
        "result" => "ok",
        "msg" => $donnee,
        "nombre" => $nombre,
        "offset" => $offset,
    ];
    echo json_encode($result , JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
