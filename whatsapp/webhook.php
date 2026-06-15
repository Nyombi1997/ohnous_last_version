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

    WhatsAppService::ensureTables($bdd);

    $payload = json_decode((string)$rawPayload, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Payload JSON invalide.');
    }

    foreach (($payload['entry'] ?? []) as $entry) {
        foreach (($entry['changes'] ?? []) as $change) {
            $value = $change['value'] ?? [];
            $contacts = [];

            foreach (($value['contacts'] ?? []) as $contact) {
                $contactWaId = WhatsAppService::normalizePhoneNumber($contact['wa_id'] ?? '');
                if ($contactWaId !== '') {
                    $contacts[$contactWaId] = $contact;
                }
            }

            foreach (($value['messages'] ?? []) as $message) {
                save_whatsapp_incoming_message($bdd, $message, $contacts, (string)$rawPayload);
            }
        }
    }
} catch (Throwable $e) {
    WhatsAppService::log('Webhook processing error', ['error' => $e->getMessage()]);
}

echo json_encode(['status' => 'ok']);

function save_whatsapp_incoming_message(PDO $bdd, array $message, array $contacts, $rawPayload)
{
    $waId = WhatsAppService::normalizePhoneNumber($message['from'] ?? '');
    if ($waId === '') {
        return;
    }

    $waMessageId = $message['id'] ?? null;
    if ($waMessageId) {
        $check = $bdd->prepare("SELECT id FROM whatsapp_messages WHERE wa_message_id = :wa_message_id LIMIT 1");
        $check->execute([':wa_message_id' => $waMessageId]);
        if ($check->fetchColumn()) {
            return;
        }
    }

    $contact = $contacts[$waId] ?? [];
    $contactName = $contact['profile']['name'] ?? null;
    $type = $message['type'] ?? 'unknown';
    $createdAt = !empty($message['timestamp'])
        ? date('Y-m-d H:i:s', (int)$message['timestamp'])
        : date('Y-m-d H:i:s');

    $conversationId = WhatsAppService::upsertConversation($bdd, $waId, $message['from'] ?? $waId, $contactName, true, $createdAt);

    $stmt = $bdd->prepare("
        INSERT INTO whatsapp_messages
            (conversation_id, wa_message_id, direction, message_type, message_body, status, raw_payload, created_at)
        VALUES
            (:conversation_id, :wa_message_id, 'in', :message_type, :message_body, NULL, :raw_payload, :created_at)
    ");
    $stmt->execute([
        ':conversation_id' => $conversationId,
        ':wa_message_id' => $waMessageId,
        ':message_type' => $type,
        ':message_body' => extract_whatsapp_message_body($message),
        ':raw_payload' => $rawPayload,
        ':created_at' => $createdAt
    ]);

    maybe_send_whatsapp_auto_reply($bdd, $waId, $message);
}

function extract_whatsapp_message_body(array $message)
{
    $type = $message['type'] ?? '';

    if ($type === 'text') {
        return $message['text']['body'] ?? null;
    }

    if ($type === 'button') {
        return '[Bouton] ' . ($message['button']['text'] ?? $message['button']['payload'] ?? '');
    }

    if ($type === 'interactive') {
        $interactive = $message['interactive'] ?? [];
        if (($interactive['type'] ?? '') === 'button_reply') {
            return '[Réponse interactive] ' . ($interactive['button_reply']['title'] ?? '');
        }
        if (($interactive['type'] ?? '') === 'list_reply') {
            return '[Réponse liste] ' . ($interactive['list_reply']['title'] ?? '');
        }
        return '[Message interactif]';
    }

    $labels = [
        'image' => '[Image reçue]',
        'document' => '[Document reçu]',
        'audio' => '[Audio reçu]',
        'video' => '[Vidéo reçue]',
    ];

    if (isset($labels[$type])) {
        $caption = $message[$type]['caption'] ?? '';
        return trim($labels[$type] . ' ' . $caption);
    }

    return $type !== '' ? '[' . $type . ' reçu]' : null;
}

function maybe_send_whatsapp_auto_reply(PDO $bdd, $waId, array $message)
{
    if (!defined('WHATSAPP_AUTO_REPLY_ENABLED') || WHATSAPP_AUTO_REPLY_ENABLED !== true) {
        return;
    }

    if (($message['type'] ?? '') !== 'text') {
        return;
    }

    $body = mb_strtolower(trim((string)($message['text']['body'] ?? '')), 'UTF-8');
    $replies = [
        'commande' => 'Bonjour 👋 Veuillez saisir votre numéro de commande.',
        'livraison' => 'Veuillez saisir votre numéro de commande afin de vérifier son statut de livraison.',
        'humain' => 'Un agent va vous répondre dans quelques instants.',
    ];

    if (!isset($replies[$body])) {
        return;
    }

    try {
        $service = new WhatsAppService($bdd);
        $service->sendText($waId, $replies[$body]);
    } catch (Throwable $e) {
        WhatsAppService::log('Auto reply error', ['error' => $e->getMessage()]);
    }
}
