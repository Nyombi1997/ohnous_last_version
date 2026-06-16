<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

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

    if(!ohnous_is_store_active($boutique))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Activez d'abord la boutique pour ajouter vos liens de contact."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $fields = [
        'facebook' => trim((string)($_POST['facebook'] ?? '')),
        'instagram' => trim((string)($_POST['instagram'] ?? '')),
        'twitter' => trim((string)($_POST['twitter'] ?? '')),
        'trends' => trim((string)($_POST['trends'] ?? '')),
        'tiktok' => trim((string)($_POST['tiktok'] ?? ''))
    ];

    update_bdd($bdd, "boutiques", $fields, "id = '".(int)$boutique['id']."'");

    echo json_encode([
        'result' => 'ok',
        'msg' => "Les liens de contact ont été enregistrés."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
