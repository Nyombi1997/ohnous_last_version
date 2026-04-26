<?php
    include_once "../model/bdd.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    $fileId = trim((string)html_entity_decode(filter_var($_POST['fileId'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $delete = ohnous_delete_imagekit_file($fileId);

    echo json_encode([
        'success' => !empty($delete['success']),
        'result' => !empty($delete['success']) ? 'ok' : 'error',
        'msg' => !empty($delete['success']) ? 'Fichier supprimé.' : ($delete['error'] ?? 'Suppression impossible.'),
        'status' => $delete['status'] ?? 0
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
