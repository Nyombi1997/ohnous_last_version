<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['store_ohnous_987654321']))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Vous n'êtes plus connecté."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $boutique = only_select("boutiques", "unique_id = '".$_SESSION['store_ohnous_987654321']."'", null, null);
    if(!$boutique)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Boutique introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    ohnous_ensure_store_activation_request_schema();

    if(ohnous_is_store_active($boutique))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Votre boutique est déjà active."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $whatsapp = ohnous_clean_international_phone($_POST['whatsapp'] ?? '');
    $telephone = ohnous_clean_international_phone($_POST['telephone'] ?? '');
    $instagram = ohnous_clean_social_account($_POST['instagram'] ?? '');
    $facebook = ohnous_clean_social_account($_POST['facebook'] ?? '');
    $tiktok = ohnous_clean_social_account($_POST['tiktok'] ?? '');

    if(trim((string)($_POST['whatsapp'] ?? '')) !== '' && $whatsapp === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Le numéro WhatsApp est invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(trim((string)($_POST['telephone'] ?? '')) !== '' && $telephone === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Le numéro d’appel est invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($whatsapp === '' && $telephone === '' && $instagram === '' && $facebook === '' && $tiktok === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Renseignez au moins une information de contact."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $existing = ohnous_get_store_pending_activation_request((int)$boutique['id']);

    if($existing)
    {
        echo json_encode([
            'result' => 'ok',
            'msg' => "Votre demande est déjà en attente. L’équipe OhNous a reçu les informations."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $token = bin2hex(random_bytes(24));
    $request = [
        'boutique_id' => (int)$boutique['id'],
        'token' => $token,
        'whatsapp' => $whatsapp,
        'telephone' => $telephone,
        'instagram' => $instagram,
        'facebook' => $facebook,
        'tiktok' => $tiktok,
        'statut' => 'en_attente'
    ];

    insert_bdd($bdd, "boutique_activation_requests", $request);
    ohnous_send_store_activation_request_email($boutique, $request);

    echo json_encode([
        'result' => 'ok',
        'msg' => "Votre demande d’activation a été envoyée."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
