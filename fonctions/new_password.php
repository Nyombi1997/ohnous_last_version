<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');
    // reset l'email adresse qui était utiliser avant
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!validateHoneypot('nouveau_mot_de_passe')) {
        ohnous_honeypot_neutral_json();
    }
    if(isset($_SESSION['email_ohnous_987654321']))
    {
        // Hachage du mot de passe
        $mdp = password_hash(
            html_entity_decode(filter_var($_POST['mdp'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
            PASSWORD_DEFAULT
        );
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['email_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        $utilisateur = select_bdd($bdd, "utilisateur", $where = 'unique_id = "'.$_SESSION['email_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        
        if(count($boutique)==0 && count($utilisateur)==0)
        {
            $results = [
                "result" => "error1",
                "msg" => "Une erreur s'est produite"
            ];
        }
        else
        {
            $update_data = 
            [
                "mdp" => $mdp,
            ];
            if(count($boutique)>0)
            {
                $_SESSION['store_ohnous_987654321'] = $boutique[0]['unique_id'];      
                update_bdd($bdd, "boutiques", $update_data, $where);     
                $results = [
                    "result" => "ok",
                    "msg" => "boutique"
                ];     
            }
            else
            {
                $_SESSION['user_ohnous_987654321'] = $utilisateur[0]['unique_id'];      
                update_bdd($bdd, "utilisateur", $update_data, $where);     
                $results = [
                    "result" => "ok",
                    "msg" => "compte"
                ];     
            }
            unset($_SESSION['email_ohnous_987654321']);
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
