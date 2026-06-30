<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if(!ohnous_is_admin())
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Accès administrateur requis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $enabled = (int)($_POST['enabled'] ?? 0) === 1;

    if(!ohnous_set_payment_enabled($enabled))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "La configuration du paiement n'a pas pu être enregistrée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'ok',
        'enabled' => $enabled ? 1 : 0,
        'msg' => $enabled ? "Le paiement a été activé." : "Le paiement a été désactivé."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
