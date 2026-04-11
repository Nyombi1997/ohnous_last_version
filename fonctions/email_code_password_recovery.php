<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "../fonctions/dependances.php";
    include_once "../fonctions/email.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $verif_email = select_bdd($bdd, "boutiques", $where = "adresse_email = '$email'", $limit = null, $offset = 0, $order = null, $random = false);
    $verif_email_user = select_bdd($bdd, "utilisateur", $where = "adresse_email = '$email'", $limit = null, $offset = 0, $order = null, $random = false);
    if(count($verif_email)==0 && count($verif_email_user)==0)
    {
        $results = [
            "result" => "error",
            "msg" => "Cette adresse email n'exist pas"
        ];
    }
    else
    {
        $nombre_aleatoire = rand(100000, 999999);
        // Hachage du mot de passe
        $nombre_aleatoire_ach = password_hash(
            $nombre_aleatoire,
            PASSWORD_DEFAULT
        );
        $update_data = [
            "code_password" => $nombre_aleatoire_ach
        ];
        if(count($verif_email)>0)
        {
            $_SESSION['email_ohnous_987654321'] = $verif_email[0]['unique_id'];
            update_bdd($bdd, "boutiques", $update_data, $where = "adresse_email = '$email'");
            if (!code_verification($email = $verif_email[0]['adresse_email'], $code = $nombre_aleatoire))
            {
                $results = [
                    "result" => "error",
                    "msg" => ohnous_missing_phpmailer_message()
                ];
                echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }
        }
        else
        {
            $_SESSION['email_ohnous_987654321'] = $verif_email_user[0]['unique_id'];
            update_bdd($bdd, "utilisateur", $update_data, $where = "adresse_email = '$email'");
            if (!code_verification($email = $verif_email_user[0]['adresse_email'], $code = $nombre_aleatoire))
            {
                $results = [
                    "result" => "error",
                    "msg" => ohnous_missing_phpmailer_message()
                ];
                echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }
        }
        $results = [
            "result" => "ok",
            "msg" => ""
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
