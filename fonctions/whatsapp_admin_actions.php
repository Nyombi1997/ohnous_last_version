<?php

include_once "../model/bdd.php";
include_once "../model/select.php";
include_once "fonctions.php";
require_once "../services/WhatsAppService.php";

header('Content-Type: application/json; charset=utf-8');

if (!ohnous_is_admin()) {
    echo json_encode([
        'result' => 'error',
        'msg' => "Accès administrateur requis."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$action = trim((string)($_POST['action'] ?? ''));

if ($action === 'send_test') {
    $to = trim((string)($_POST['to'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($to === '' || $message === '') {
        echo json_encode([
            'result' => 'error',
            'msg' => "Veuillez renseigner le numéro et le message."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    try {
        $service = new WhatsAppService($bdd);
        $response = $service->sendText($to, $message);

        echo json_encode([
            'result' => !empty($response['success']) ? 'ok' : 'error',
            'msg' => !empty($response['success']) ? "Message WhatsApp envoyé." : "Échec de l'envoi WhatsApp.",
            'http_code' => $response['http_code'] ?? null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } catch (Throwable $e) {
        WhatsAppService::log('Admin test send error', ['error' => $e->getMessage()]);
        echo json_encode([
            'result' => 'error',
            'msg' => "Erreur lors de l'envoi WhatsApp."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
}

echo json_encode([
    'result' => 'error',
    'msg' => "Action WhatsApp inconnue."
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
