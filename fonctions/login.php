<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    // Hachage du mot de passe
    $mdp = password_hash(
        html_entity_decode(filter_var($_POST['mdp'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
        PASSWORD_DEFAULT
    );
    $boutique = select_bdd($bdd, "boutiques", $where = 'adresse_email = "'.$email.'"', $limit = null, $offset = 0, $order = null, $random = false);
    $user = select_bdd($bdd, "utilisateur", $where = 'adresse_email = "'.$email.'"', $limit = null, $offset = 0, $order = null, $random = false);
    if(count($boutique)==0 && count($user)==0)
    {
        $results = [
            "result" => "error",
            "msg" => "L'adresse email ou le mot de passe est incorrect."
        ];
    }
    else
    {
        if(count($boutique)>0)
        {
            if(password_verify($_POST['mdp'], $boutique[0]['mdp']))
            {
                $results = [
                    "result" => "ok",
                    "msg" => "boutique"
                ];
                $_SESSION['store_ohnous_987654321'] = $boutique[0]['unique_id'];
                ohnous_send_welcome_email_once($boutique[0], 'boutique');
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "L'adresse email ou le mot de passe est incorrect."
                ];
            }
        }
        else if(count($user)>0)
        {
            if(password_verify($_POST['mdp'], $user[0]['mdp']))
            {
                $results = [
                    "result" => "ok",
                    "msg" => "compte"
                ];
                $_SESSION['user_ohnous_987654321'] = $user[0]['unique_id'];
                ohnous_send_welcome_email_once($user[0], 'utilisateur');
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "L'adresse email ou le mot de passe est incorrect."
                ];
            }
        }
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
