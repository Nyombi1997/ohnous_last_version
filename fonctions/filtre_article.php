<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    //header('Content-Type: application/json; charset=utf-8');
    $categorie = html_entity_decode(filter_var($_POST['categorie'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $types = html_entity_decode(filter_var($_POST['types'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $taille = html_entity_decode(filter_var($_POST['taille'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $boutique = html_entity_decode(filter_var($_POST['boutique'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $recherche = html_entity_decode(filter_var($_POST['recherche'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if($recherche!="")
    {

    }
    else
    {
        $filters = [
            'category' => $categorie,
            'type' => $types,
            'taille' => $taille
        ];

        $msg = select_articles_filtre($bdd, $filters, $limit = null, $offset = 0, $order = null, $random = false);

        echo count($msg);
    }

    // Retour en JSON
    //echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>