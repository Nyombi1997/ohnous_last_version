<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    require_once '../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
    include_once "../fonctions/email.php";
    header('Content-Type: application/json; charset=utf-8');

    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $verif_email = select_bdd($bdd, "boutiques", $where = "adresse_email = '$email'", $limit = null, $offset = 0, $order = null, $random = false);
    if(count($verif_email)==0)
    {
        $results = [
            "result" => "error",
            "msg" => "Cette adresse email n'exist pas"
        ];
    }
    else
    {
        $_SESSION['email_ohnous_987654321'] = $verif_email[0]['unique_id'];
        $nombre_aleatoire = rand(100000, 999999);
        // Hachage du mot de passe
        $nombre_aleatoire_ach = password_hash(
            $nombre_aleatoire,
            PASSWORD_DEFAULT
        );
        $update_data = [
            "code_password" => $nombre_aleatoire_ach
        ];
        update_bdd($bdd, "boutiques", $update_data, $where = "adresse_email = '$email'");
        code_verification($email = $verif_email[0]['adresse_email'], $code = $nombre_aleatoire);
        $results = [
            "result" => "ok",
            "msg" => ""
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>