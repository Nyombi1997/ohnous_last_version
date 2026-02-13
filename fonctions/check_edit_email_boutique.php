<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* SI ON EST CONNECTER */
    if(isset($_SESSION['store_ohnous_987654321']))
    {
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['store_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)!=0)
        {
            $boutique = $boutique[0];

            $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $verif_email = select_bdd($bdd, "boutiques", $where = "adresse_email = '$email'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($verif_email)==0)
            {
                $update_data = [
                    "adresse_email" => $email,
                ];
                update_bdd($bdd, "boutiques", $update_data, "id = '".$boutique['id']."'");
                $results = [
                    "result" => "ok",
                    "msg" => ""
                ];
            }
            elseif($verif_email[0]['unique_id']==$_SESSION['store_ohnous_987654321'])
            {
                $results = [
                    "result" => "ok",
                    "msg" => $email
                ];
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "Cette adresse email est déjà utiliser"
                ];
            }
        }
        else
        {
            $results = [
                "result" => "error",
                "msg" => "Vous n'êtes plus connecter"
            ];
        }
    }
    else
    {
        $results = [
            "result" => "error",
            "msg" => "Vous n'êtes plus connecter"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>