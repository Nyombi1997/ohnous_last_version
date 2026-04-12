<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    $token = html_entity_decode(filter_var($_POST['token'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $days = (int)html_entity_decode(filter_var($_POST['days'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $months = (int)html_entity_decode(filter_var($_POST['months'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

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

    if($token === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Jeton d’activation invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $request = only_select("boutique_activation_requests", "token = '".$token."'", null, null);
    if(!$request)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Demande introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $durationDays = 0;
    if($months > 0)
    {
        $durationDays += $months * 30;
    }
    if($days > 0)
    {
        $durationDays += $days;
    }

    if($durationDays <= 0)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Ajoutez un nombre de jours ou de mois."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $endDate = (new DateTime())->modify('+'.$durationDays.' days')->format('Y-m-d');

    update_bdd($bdd, "boutiques", [
        'activer' => 1,
        'date_activation_debut' => date('Y-m-d'),
        'date_activation_fin' => $endDate
    ], "id = '".(int)$request['boutique_id']."'");

    update_bdd($bdd, "boutique_activation_requests", [
        'statut' => 'traitee',
        'duree_jours' => $durationDays,
        'date_traitement' => date('Y-m-d H:i:s')
    ], "id = '".(int)$request['id']."'");

    echo json_encode([
        'result' => 'ok',
        'msg' => "La boutique est maintenant active jusqu’au ".$endDate.'.'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
