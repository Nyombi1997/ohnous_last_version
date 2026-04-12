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

    if(!ohnous_table_exists('boutique_activation_requests'))
    {
        createTable('boutique_activation_requests', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'boutique_id INT NOT NULL',
            'token TEXT NULL',
            'statut VARCHAR(30) NOT NULL DEFAULT "en_attente"',
            'duree_jours INT NOT NULL DEFAULT 0',
            'date_traitement DATETIME NULL',
            'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    if(ohnous_is_store_active($boutique))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Votre boutique est déjà active."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $existing = only_select(
        "boutique_activation_requests",
        "boutique_id = ".(int)$boutique['id']." AND statut = 'en_attente'",
        "id DESC",
        1
    );

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
        'statut' => 'en_attente'
    ];

    insert_bdd($bdd, "boutique_activation_requests", $request);
    ohnous_send_store_activation_request_email($boutique, $request);

    echo json_encode([
        'result' => 'ok',
        'msg' => "La demande d’activation a été envoyée à contact@ohnous.store."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
