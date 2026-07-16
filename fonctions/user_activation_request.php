<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!validateHoneypot('activation_compte')) {
        ohnous_honeypot_neutral_json();
    }

    if(!isset($_SESSION['user_ohnous_987654321']))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Connectez-vous pour envoyer une demande d’activation."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    if(!ohnous_table_exists('user_activation_requests'))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "La table des demandes d’activation n’est pas encore installée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    $user = only_select("utilisateur", "unique_id = '".addslashes($_SESSION['user_ohnous_987654321'])."'", null, null);
    if(!$user)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Compte introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    if(ohnous_is_user_active($user))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Votre compte est déjà activé."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    if(ohnous_get_user_pending_activation_request((int)$user['id']))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Une demande d’activation est déjà en attente."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    $whatsapp = ohnous_clean_international_phone($_POST['whatsapp'] ?? '');
    $telephone = ohnous_clean_international_phone($_POST['telephone'] ?? '');
    $instagram = ohnous_clean_social_account($_POST['instagram'] ?? '');
    $facebook = ohnous_clean_social_account($_POST['facebook'] ?? '');
    $tiktok = ohnous_clean_social_account($_POST['tiktok'] ?? '');

    if(trim((string)($_POST['whatsapp'] ?? '')) !== '' && $whatsapp === '')
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Le numéro WhatsApp est invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    if(trim((string)($_POST['telephone'] ?? '')) !== '' && $telephone === '')
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Le numéro d’appel est invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    if($whatsapp === '' && $telephone === '' && $instagram === '' && $facebook === '' && $tiktok === '')
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Renseignez au moins une information de contact."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    $request = [
        "utilisateur_id" => (int)$user['id'],
        "whatsapp" => $whatsapp,
        "telephone" => $telephone,
        "instagram" => $instagram,
        "facebook" => $facebook,
        "tiktok" => $tiktok,
        "statut" => "en_attente"
    ];

    insert_bdd($bdd, "user_activation_requests", $request);
    ohnous_send_user_activation_request_email($user, $request);

    echo json_encode([
        "result" => "ok",
        "msg" => "Votre demande d’activation a été envoyée."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
