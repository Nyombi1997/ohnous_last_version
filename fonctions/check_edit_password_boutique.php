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
            // Hachage du mot de passe
            $mdp = password_hash(
                $_POST['mdp1'],
                PASSWORD_DEFAULT
            );
            if(password_verify($_POST['mdp'], $boutique['mdp']))
            {
                $update_data = [
                    "mdp" => $mdp,
                ];
                update_bdd($bdd, "boutiques", $update_data, "id = '".$boutique['id']."'");
                $results = [
                    "result" => "ok",
                    "msg" => ""
                ];
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "Votre ancien mot de passe est incorrect"
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