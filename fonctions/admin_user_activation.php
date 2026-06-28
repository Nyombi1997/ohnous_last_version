<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');

    ohnous_require_admin_or_redirect('/admin-login');

    $requestId = (int)($_POST['id'] ?? 0);
    $action = trim((string)($_POST['action'] ?? ''));

    if($requestId <= 0 || !in_array($action, ['accepter', 'refuser'], true) || !ohnous_table_exists('user_activation_requests'))
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Demande invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    $request = only_select("user_activation_requests", "id = ".$requestId, null, null);
    if(!$request)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Demande introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    $status = $action === 'accepter' ? 'acceptee' : 'refusee';
    update_bdd($bdd, "user_activation_requests", [
        "statut" => $status,
        "date_traitement" => date('Y-m-d H:i:s')
    ], "id = ".$requestId);

    if($action === 'accepter' && ohnous_column_exists('utilisateur', 'activer'))
    {
        update_bdd($bdd, "utilisateur", [
            "activer" => 1
        ], "id = ".(int)$request['utilisateur_id']);
    }

    echo json_encode([
        "result" => "ok",
        "msg" => $action === 'accepter' ? "Le compte utilisateur est activé." : "La demande a été refusée."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
