<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!ohnous_is_admin())
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Accès administrateur requis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $storeId = (int)html_entity_decode(filter_var($_POST['store_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $boutique = $storeId > 0 ? only_select("boutiques", "id = ".$storeId, null, null) : null;

    if(!$boutique)
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Boutique introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $product_image_url = html_entity_decode(filter_var($_POST['product_image_url'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $fileId = html_entity_decode(filter_var($_POST['fileId'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $background = html_entity_decode(filter_var($_POST['background'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if($product_image_url === '')
    {
        echo json_encode([
            "result" => "error",
            "msg" => "Aucune image n'a été reçue."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $update_data = [
        "profile" => $product_image_url,
        "backgrounds" => $background,
    ];

    if(ohnous_column_exists('boutiques', 'fileId'))
    {
        $update_data["fileId"] = $fileId;
    }

    update_bdd($bdd, "boutiques", $update_data, "id = '".(int)$storeId."'");

    echo json_encode([
        "result" => "ok",
        "msg" => "La photo de profil a été mise à jour."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
