<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $verif_email = select_bdd($bdd, "boutiques", $where = "adresse_email = '$email'", $limit = null, $offset = 0, $order = null, $random = false);
    $verif_email_user = select_bdd($bdd, "utilisateur", $where = "adresse_email = '$email'", $limit = null, $offset = 0, $order = null, $random = false);
    if(count($verif_email)==0 && count($verif_email_user)==0)
    {
        $results = [
            "result" => "ok",
            "msg" => ""
        ];
    }
    else
    {
        $results = [
            "result" => "error",
            "msg" => "Cette adresse email est déjà utiliser"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>