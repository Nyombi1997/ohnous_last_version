<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    include_once __DIR__ . "/fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    $offset = isset($_POST['offset']) ? max(0, (int)$_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? max(1, (int)$_POST['limit']) : 12;

    $stores = ohnous_get_visible_stores($limit, $offset);
    $html = '';

    foreach($stores as $boutique)
    {
        $html .= ohnous_render_public_store_card($boutique, true);
    }

    echo json_encode([
        'result' => 'ok',
        'msg' => $html,
        'nombre' => count($stores),
        'offset' => $offset,
        'limit' => $limit,
        'has_more' => count($stores) === $limit,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
