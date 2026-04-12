<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";

    header('Content-Type: application/json; charset=utf-8');

    if(!ohnous_is_admin())
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Accès administrateur requis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    createTable('admin_boutique_messages', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'boutique_id INT NOT NULL',
        'from_type VARCHAR(30) NOT NULL',
        'message TEXT NOT NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);

    $action = trim((string)html_entity_decode(filter_var($_POST['action'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $storeId = (int)html_entity_decode(filter_var($_POST['store_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $boutique = $storeId > 0 ? only_select('boutiques', 'id = '.$storeId, null, null) : null;

    if($storeId > 0 && !$boutique)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Boutique introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'toggle_store')
    {
        $activate = (int)html_entity_decode(filter_var($_POST['activate'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if(ohnous_is_test_store($boutique) && $activate === 0)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Une boutique test reste active tant qu’elle n’a pas d’adresse email."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $updateData = [
            'activer' => $activate
        ];

        if($activate === 1)
        {
            $updateData['date_activation_debut'] = date('Y-m-d');
            $updateData['date_activation_fin'] = !empty($boutique['date_activation_fin'])
                ? $boutique['date_activation_fin']
                : (new DateTime('+30 days'))->format('Y-m-d');
        }

        update_bdd($bdd, 'boutiques', $updateData, "id = '".(int)$storeId."'");

        echo json_encode([
            'result' => 'ok',
            'msg' => $activate === 1 ? "La boutique a été activée." : "La boutique a été désactivée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'send_message')
    {
        $message = trim((string)html_entity_decode(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

        if($message === '')
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Écrivez un message avant l’envoi."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        insert_bdd($bdd, 'admin_boutique_messages', [
            'boutique_id' => (int)$storeId,
            'from_type' => 'admin',
            'message' => $message
        ]);

        if(!empty($boutique['adresse_email']))
        {
            ohnous_send_admin_store_contact_email(
                $boutique['adresse_email'],
                $boutique['nom'] ?? 'Boutique OhNous',
                $message,
                'https://ohnous.store/admin-boutique?id='.(int)$storeId
            );
        }

        $messages = ohnous_get_admin_store_messages($storeId);
        $html = '';

        foreach($messages as $row)
        {
            $row['profile'] = $boutique['profile'] ?? '';
            $html .= ohnous_render_admin_store_message_bubble($row);
        }

        echo json_encode([
            'result' => 'ok',
            'msg' => "Le message a été envoyé à la boutique.",
            'thread_html' => $html
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'fetch_thread')
    {
        $messages = ohnous_get_admin_store_messages($storeId);
        $html = '';

        foreach($messages as $row)
        {
            $row['profile'] = $boutique['profile'] ?? '';
            $html .= ohnous_render_admin_store_message_bubble($row);
        }

        echo json_encode([
            'result' => 'ok',
            'thread_html' => $html
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'error',
        'msg' => "Action admin boutique inconnue."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
