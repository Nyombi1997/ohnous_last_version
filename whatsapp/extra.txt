<?php

require_once dirname(__DIR__) . '/config/whatsapp.php';
require_once dirname(__DIR__) . '/model/bdd.php';
require_once dirname(__DIR__) . '/services/WhatsAppService.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? '';

    if ($mode === 'subscribe' && hash_equals(WHATSAPP_VERIFY_TOKEN, (string)$token)) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $challenge;
        exit;
    }

    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');

$rawPayload = file_get_contents('php://input');
WhatsAppService::log('Webhook payload', ['payload' => $rawPayload]);

try {
    if (!$bdd instanceof PDO) {
        throw new RuntimeException('Connexion PDO introuvable.');
    }

    WhatsAppService::ensureMessagesTable($bdd);

    $payload = json_decode((string)$rawPayload, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Payload JSON invalide.');
    }

    $stmt = $bdd->prepare("
        INSERT INTO whatsapp_messages
            (wa_message_id, wa_id, phone, contact_name, message_type, message_body, raw_payload, direction, status, created_at)
        VALUES
            (:wa_message_id, :wa_id, :phone, :contact_name, :message_type, :message_body, :raw_payload, 'in', NULL, :created_at)
        ON DUPLICATE KEY UPDATE raw_payload = VALUES(raw_payload)
    ");

    foreach (($payload['entry'] ?? []) as $entry) {
        foreach (($entry['changes'] ?? []) as $change) {
            $value = $change['value'] ?? [];
            $contacts = [];

            foreach (($value['contacts'] ?? []) as $contact) {
                $contacts[$contact['wa_id'] ?? ''] = $contact;
            }

            foreach (($value['messages'] ?? []) as $message) {
                $waId = $message['from'] ?? null;
                $contact = $contacts[$waId] ?? [];
                $type = $message['type'] ?? null;
                $createdAt = !empty($message['timestamp'])
                    ? date('Y-m-d H:i:s', (int)$message['timestamp'])
                    : date('Y-m-d H:i:s');

                $stmt->execute([
                    ':wa_message_id' => $message['id'] ?? null,
                    ':wa_id' => $waId,
                    ':phone' => $waId,
                    ':contact_name' => $contact['profile']['name'] ?? null,
                    ':message_type' => $type,
                    ':message_body' => extract_whatsapp_message_body($message),
                    ':raw_payload' => $rawPayload,
                    ':created_at' => $createdAt
                ]);
            }
        }
    }
} catch (Throwable $e) {
    WhatsAppService::log('Webhook processing error', ['error' => $e->getMessage()]);
}

echo json_encode(['status' => 'ok']);

function extract_whatsapp_message_body(array $message)
{
    $type = $message['type'] ?? '';

    if ($type === 'text') {
        return $message['text']['body'] ?? null;
    }

    if (isset($message[$type]) && is_array($message[$type])) {
        return json_encode($message[$type], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    return null;
}
