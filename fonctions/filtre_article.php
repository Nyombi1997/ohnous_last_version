<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "../view/composants/fonction_produit.php";
    include_once "fonctions.php";
    $categorie = html_entity_decode(filter_var($_POST['categorie'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $types = html_entity_decode(filter_var($_POST['types'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $taille = html_entity_decode(filter_var($_POST['taille'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $boutique = html_entity_decode(filter_var($_POST['boutique'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $recherche = html_entity_decode(filter_var($_POST['recherche'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $offset = $_POST['offset'];
    header('Content-Type: application/json; charset=utf-8');

    $donnee = "";
    $nombre = 0;

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

        $msg = select_articles_filtre($bdd, $filters, $limit = 12, $offset, $order = null, $random = false);
        foreach($msg as $msg_)
        {
            $donnee .= affiche_produit($msg_ , true);
        }
        $nombre = count($msg);
    }
    $result = [
        "result" => "ok",
        "msg" => $donnee,
        "nombre" => $nombre,
    ];
    echo json_encode($result , JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>