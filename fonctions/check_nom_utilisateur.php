<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $nom = html_entity_decode(filter_var($_POST['nom'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $verif_nom = select_bdd($bdd, "boutiques", $where = "nom = '$nom'", $limit = null, $offset = 0, $order = null, $random = false);
    $verif_nom_user = select_bdd($bdd, "utilisateur", $where = "nom = '$nom'", $limit = null, $offset = 0, $order = null, $random = false);
    if(count($verif_nom)==0 && count($verif_nom_user)==0)
    {
        $results = [
            "result" => "ok",
            "msg" => ""
        ];
    }
    else
    {
        for($i=0;count($verif_nom)>0;$i++)
        {
            if($i<=9)
            {
                $nombre = '0'.$i;
            }
            else
            {
                $nombre = $i." cool";
            }
            $nom = $nom.' '.$nombre;
            $sortie = '<a href="#" onclick=\'changeName('.json_encode($nom).')\'>'.$nom.'</a>';
            $verif_nom = select_bdd($bdd, "boutiques", $where = "nom = '$nom'", $limit = null, $offset = 0, $order = null, $random = false);
        }
        $results = [
            "result" => "error",
            "msg" => $sortie
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>