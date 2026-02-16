<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['email_ohnous_987654321']))
    {
        $unique_id = $_SESSION['email_ohnous_987654321'];
        $_POST['code'] = str_replace(" ","",$_POST['code']);
        // Hachage du code
        $code = password_hash(
            html_entity_decode(filter_var($_POST['code'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
            PASSWORD_DEFAULT
        );
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$unique_id.'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)==0)
        {
            $results = [
                "result" => "error1",
                "msg" => "Une erreur s'est produite."
            ];
        }
        else
        {
            if(password_verify($_POST['code'], $boutique[0]['code_password']))
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
                    "msg" => "Ce code est incorrect."
                ];
            }
        }
    }
    else
    {
        $results = [
            "result" => "error1",
            "msg" => "Une erreur s'est produite."
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>