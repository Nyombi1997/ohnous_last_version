<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $action = html_entity_decode(filter_var($_POST['action'] ?? 'fetch', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $currentAccount = ohnous_get_current_account();

    if(!ohnous_table_exists('messages'))
    {
        createTable('messages', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'client_id INT NOT NULL',
            'boutique_id INT NOT NULL',
            'from_id INT NOT NULL',
            'messages TEXT NOT NULL',
            'lu INT NOT NULL DEFAULT 0',
            'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    if(!$currentAccount['connected'])
    {
        echo json_encode([
            'result' => 'auth_required',
            'msg' => "Connectez-vous pour accéder aux messages."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $clientId = (int)html_entity_decode(filter_var($_POST['client_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $boutiqueId = (int)html_entity_decode(filter_var($_POST['boutique_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if($action === 'send')
    {
        $messageText = trim((string)html_entity_decode(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

        if($boutiqueId <= 0)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Boutique introuvable."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if($currentAccount['type'] === 'utilisateur')
        {
            $clientId = (int)$currentAccount['id'];
        }
        elseif($currentAccount['type'] === 'boutique')
        {
            $boutiqueId = (int)$currentAccount['id'];
        }

        if($clientId <= 0 || $boutiqueId <= 0)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Conversation invalide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if($messageText === '')
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Votre message est vide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if(mb_strlen($messageText) > 3000)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Votre message est trop long."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $fromId = (int)$currentAccount['id'];
        insert_bdd($bdd, "messages", [
            'client_id' => $clientId,
            'boutique_id' => $boutiqueId,
            'from_id' => $fromId,
            'messages' => $messageText,
            'lu' => 0
        ]);

        $boutique = only_select("boutiques", "id = ".$boutiqueId, null, null);
        $utilisateur = only_select("utilisateur", "id = ".$clientId, null, null);

        if($currentAccount['type'] === 'boutique' && !empty($utilisateur['adresse_email']))
        {
            ohnous_send_message_notification_email(
                $utilisateur['adresse_email'],
                $boutique['nom'] ?? 'Une boutique OhNous',
                'https://ohnous.store/message?client='.$clientId.'&boutique='.$boutiqueId,
                $messageText
            );
        }
        elseif($currentAccount['type'] === 'utilisateur' && !empty($boutique['adresse_email']))
        {
            ohnous_send_message_notification_email(
                $boutique['adresse_email'],
                $utilisateur['nom'] ?? 'Un client OhNous',
                'https://ohnous.store/message?client='.$clientId.'&boutique='.$boutiqueId,
                $messageText
            );
        }
    }

    if($clientId > 0 && $boutiqueId > 0)
    {
        ohnous_mark_conversation_as_read($clientId, $boutiqueId);
    }

    $messages = ($clientId > 0 && $boutiqueId > 0)
        ? ohnous_get_messages_for_conversation($clientId, $boutiqueId)
        : [];
    $conversations = ohnous_get_conversations_for_current_account();

    $messagesHtml = '';
    foreach($messages as $message)
    {
        $messagesHtml .= ohnous_render_message_bubble($message, $currentAccount);
    }

    echo json_encode([
        'result' => 'ok',
        'conversations' => $conversations,
        'messages_html' => $messagesHtml,
        'message_count' => count($messages),
        'unread_count' => ohnous_get_unread_messages_count($currentAccount)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
