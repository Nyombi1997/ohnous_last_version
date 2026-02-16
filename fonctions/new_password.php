<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    // reset l'email adresse qui était utiliser avant
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['email_ohnous_987654321']))
    {
        // Hachage du mot de passe
        $mdp = password_hash(
            html_entity_decode(filter_var($_POST['mdp'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
            PASSWORD_DEFAULT
        );
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['email_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        
        if(count($boutique)==0)
        {
            $results = [
                "result" => "error1",
                "msg" => "Une erreur s'est produite"
            ];
        }
        else
        {
                $_SESSION['store_ohnous_987654321'] = $boutique[0]['unique_id'];
                $update_data = 
                [
                    "mdp" => $mdp,
                ];
                update_bdd($bdd, "boutiques", $update_data, $where);
                unset($_SESSION['email_ohnous_987654321']);
                $results = [
                    "result" => "ok",
                    "msg" => "boutique"
                ];
        }
    }
    else
    {
        $results = [
            "result" => "error1",
            "msg" => "Une erreur s'est produite"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>