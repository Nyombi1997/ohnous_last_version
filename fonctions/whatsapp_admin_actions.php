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

try {
    WhatsAppService::ensureTables($bdd);
    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'conversations') {
        echo json_encode([
            'result' => 'ok',
            'conversations' => ohnous_whatsapp_get_conversations($bdd)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if ($action === 'messages') {
        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        ohnous_whatsapp_mark_read($bdd, $conversationId);
        echo json_encode([
            'result' => 'ok',
            'messages' => ohnous_whatsapp_get_messages($bdd, $conversationId),
            'conversation' => ohnous_whatsapp_get_conversation($bdd, $conversationId),
            'customer' => ohnous_whatsapp_get_customer_info($bdd, $conversationId)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if ($action === 'mark_read') {
        ohnous_whatsapp_mark_read($bdd, (int)($_POST['conversation_id'] ?? 0));
        echo json_encode(['result' => 'ok'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if ($action === 'send_reply') {
        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? ''));

        if ($conversationId <= 0 || $message === '') {
            echo json_encode([
                'result' => 'error',
                'msg' => "Message invalide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $conversation = ohnous_whatsapp_get_conversation($bdd, $conversationId);
        if (!$conversation) {
            echo json_encode([
                'result' => 'error',
                'msg' => "Conversation introuvable."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $service = new WhatsAppService($bdd);
        $response = $service->sendText($conversation['wa_id'], $message);

        echo json_encode([
            'result' => !empty($response['success']) ? 'ok' : 'error',
            'msg' => !empty($response['success']) ? "Message envoyé." : ohnous_whatsapp_get_meta_error($response),
            'http_code' => $response['http_code'] ?? null,
            'meta_error' => $response['error_message'] ?? null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'error',
        'msg' => "Action WhatsApp inconnue."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    WhatsAppService::log('Admin WhatsApp action error', ['error' => $e->getMessage()]);
    echo json_encode([
        'result' => 'error',
        'msg' => "Erreur WhatsApp admin."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function ohnous_whatsapp_get_conversations(PDO $bdd)
{
    $stmt = $bdd->query("
        SELECT
            c.*,
            m.message_body AS last_message,
            m.direction AS last_direction,
            m.message_type AS last_message_type
        FROM whatsapp_conversations c
        LEFT JOIN whatsapp_messages m ON m.id = (
            SELECT wm.id
            FROM whatsapp_messages wm
            WHERE wm.conversation_id = c.id
            ORDER BY wm.created_at DESC, wm.id DESC
            LIMIT 1
        )
        ORDER BY c.last_message_at DESC, c.updated_at DESC, c.id DESC
        LIMIT 200
    ");

    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function ohnous_whatsapp_get_conversation(PDO $bdd, $conversationId)
{
    $stmt = $bdd->prepare("SELECT * FROM whatsapp_conversations WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$conversationId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function ohnous_whatsapp_get_messages(PDO $bdd, $conversationId)
{
    $stmt = $bdd->prepare("
        SELECT *
        FROM whatsapp_messages
        WHERE conversation_id = :conversation_id
        ORDER BY created_at ASC, id ASC
        LIMIT 500
    ");
    $stmt->execute([':conversation_id' => (int)$conversationId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function ohnous_whatsapp_mark_read(PDO $bdd, $conversationId)
{
    if ((int)$conversationId <= 0) {
        return;
    }

    $stmt = $bdd->prepare("UPDATE whatsapp_conversations SET unread_count = 0 WHERE id = :id");
    $stmt->execute([':id' => (int)$conversationId]);
}

function ohnous_whatsapp_get_customer_info(PDO $bdd, $conversationId)
{
    $conversation = ohnous_whatsapp_get_conversation($bdd, $conversationId);
    if (!$conversation || empty($conversation['customer_id'])) {
        return null;
    }

    $customerId = (int)$conversation['customer_id'];
    $customer = null;
    $customerTable = null;

    foreach (['clients', 'customers', 'utilisateur'] as $table) {
        if (!ohnous_table_exists($table)) {
            continue;
        }

        $columns = ['id'];
        foreach (['nom', 'name', 'full_name', 'adresse_email', 'email', 'phone', 'telephone', 'profile'] as $column) {
            if (ohnous_column_exists($table, $column)) {
                $columns[] = $column;
            }
        }

        $stmt = $bdd->prepare("SELECT " . implode(', ', array_map(function ($column) {
            return "`" . $column . "`";
        }, $columns)) . " FROM `$table` WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            $customerTable = $table;
            break;
        }
    }

    if (!$customer) {
        return null;
    }

    $info = [
        'id' => (int)$customer['id'],
        'nom' => $customer['nom'] ?? $customer['name'] ?? $customer['full_name'] ?? 'Client OhNous',
        'telephone' => $customer['telephone'] ?? $customer['phone'] ?? $conversation['phone'] ?? $conversation['wa_id'],
        'email' => $customer['adresse_email'] ?? $customer['email'] ?? '',
        'profile' => ohnous_get_profile_picture($customer['profile'] ?? '', 'utilisateur'),
        'orders_count' => null,
        'last_order' => null
    ];

    if ($customerTable === 'utilisateur' && ohnous_table_exists('commandes')) {
        $orders = $bdd->prepare("
            SELECT COUNT(*) AS total, MAX(date_ajout) AS last_order
            FROM commandes
            WHERE client_type = 'utilisateur'
            AND client_id = :client_id
        ");
        $orders->execute([':client_id' => $customerId]);
        $row = $orders->fetch(PDO::FETCH_ASSOC);
        $info['orders_count'] = (int)($row['total'] ?? 0);
        $info['last_order'] = $row['last_order'] ?? null;
    }

    return $info;
}

function ohnous_whatsapp_get_meta_error(array $response)
{
    if (!empty($response['error_message'])) {
        return (string)$response['error_message'];
    }

    if (!empty($response['data']['error']['message'])) {
        return (string)$response['data']['error']['message'];
    }

    if (!empty($response['data']['error']['error_user_msg'])) {
        return (string)$response['data']['error']['error_user_msg'];
    }

    if (!empty($response['error'])) {
        return (string)$response['error'];
    }

    return "Échec de l'envoi WhatsApp.";
}
