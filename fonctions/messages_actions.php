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
    $conversationType = html_entity_decode(filter_var($_POST['conversation_type'] ?? 'boutique', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $clientType = html_entity_decode(filter_var($_POST['client_type'] ?? 'utilisateur', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if($conversationType !== 'admin')
    {
        $conversationType = 'boutique';
    }

    if(!in_array($clientType, ['utilisateur', 'boutique'], true))
    {
        $clientType = 'utilisateur';
    }

    if($action === 'search_recipients')
    {
        $query = trim((string)html_entity_decode(filter_var($_POST['query'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
        echo json_encode([
            'result' => 'ok',
            'recipients' => ohnous_search_chat_recipients($query)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

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
    ohnous_ensure_messages_chat_columns();

    if($action === 'search_articles')
    {
        $query = trim((string)html_entity_decode(filter_var($_POST['query'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
        echo json_encode([
            'result' => 'ok',
            'articles' => ohnous_search_chat_articles($query, $boutiqueId)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'send')
    {
        $messageText = trim((string)html_entity_decode(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
        $articleId = (int)html_entity_decode(filter_var($_POST['article_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if($conversationType === 'admin')
        {
            if($currentAccount['type'] === 'admin')
            {
                $boutiqueId = 0;
            }
            else
            {
                $clientType = $currentAccount['type'];
                $clientId = (int)$currentAccount['id'];
                $boutiqueId = 0;
            }
        }

        if($conversationType !== 'admin' && $boutiqueId <= 0)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Boutique introuvable."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if($conversationType !== 'admin' && $currentAccount['type'] === 'utilisateur')
        {
            $clientId = (int)$currentAccount['id'];
        }
        elseif($conversationType !== 'admin' && $currentAccount['type'] === 'boutique')
        {
            $boutiqueId = (int)$currentAccount['id'];
        }

        if($clientId <= 0 || ($conversationType !== 'admin' && $boutiqueId <= 0))
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Conversation invalide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if($messageText === '' && $articleId <= 0)
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

        if($articleId > 0)
        {
            $article = only_select("articles", "id = ".$articleId, null, null);
            if(!$article)
            {
                echo json_encode([
                    'result' => 'error',
                    'msg' => "Article introuvable."
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            if($currentAccount['type'] === 'boutique' && (int)$article['boutique'] !== (int)$currentAccount['id'])
            {
                echo json_encode([
                    'result' => 'error',
                    'msg' => "Vous ne pouvez joindre que vos articles."
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            if($currentAccount['type'] === 'utilisateur' && $conversationType !== 'admin' && (int)$article['boutique'] !== (int)$boutiqueId)
            {
                echo json_encode([
                    'result' => 'error',
                    'msg' => "Cet article ne correspond pas à cette boutique."
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            $messageText .= "\n[[article:".$articleId."]]";
        }

        $fromId = (int)$currentAccount['id'];
        insert_bdd($bdd, "messages", [
            'client_id' => $clientId,
            'boutique_id' => $boutiqueId,
            'from_id' => $fromId,
            'conversation_type' => $conversationType,
            'client_type' => $clientType,
            'from_type' => $currentAccount['type'],
            'messages' => $messageText,
            'lu' => 0
        ]);

        if($conversationType === 'boutique')
        {
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
    }

    $firstUnreadMessageId = 0;
    if($clientId > 0 && ($conversationType === 'admin' || $boutiqueId > 0))
    {
        $messagesBeforeRead = ohnous_get_messages_for_conversation($clientId, $boutiqueId, $conversationType, $clientType);
        foreach($messagesBeforeRead as $message)
        {
            $isUnreadForCurrent = (int)($message['lu'] ?? 0) === 0
                && (
                    ($currentAccount['type'] === 'admin' && ($message['from_type'] ?? '') !== 'admin')
                    || ($currentAccount['type'] !== 'admin' && ($message['from_type'] ?? '') !== $currentAccount['type'])
                );
            if($isUnreadForCurrent)
            {
                $firstUnreadMessageId = (int)$message['id'];
                break;
            }
        }
        ohnous_mark_conversation_as_read($clientId, $boutiqueId, $conversationType, $clientType);
    }

    $messages = ($clientId > 0 && ($conversationType === 'admin' || $boutiqueId > 0))
        ? ohnous_get_messages_for_conversation($clientId, $boutiqueId, $conversationType, $clientType)
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
        'report_html' => ohnous_get_chat_report_html($messages, $currentAccount, $boutiqueId),
        'first_unread_message_id' => $firstUnreadMessageId,
        'message_count' => count($messages),
        'unread_count' => ohnous_get_unread_messages_count($currentAccount)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
