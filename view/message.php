<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $currentAccount = ohnous_get_current_account();
    if(!$currentAccount['connected'])
    {
        header("Location:/connexion");
        exit();
    }

    $conversations = ohnous_get_conversations_for_current_account();
    $selectedClientId = (int)($_GET['client'] ?? 0);
    $selectedBoutiqueId = (int)($_GET['boutique'] ?? 0);

    if($selectedClientId <= 0 || $selectedBoutiqueId <= 0)
    {
        if(!empty($conversations))
        {
            $selectedClientId = (int)$conversations[0]['client_id'];
            $selectedBoutiqueId = (int)$conversations[0]['boutique_id'];
        }
        elseif($currentAccount['type'] === 'utilisateur' && (int)($_GET['boutique'] ?? 0) > 0)
        {
            $selectedClientId = (int)$currentAccount['id'];
            $selectedBoutiqueId = (int)$_GET['boutique'];
        }
    }

    $selectedMessages = [];
    $selectedConversationTitle = 'Choisissez une conversation';
    if($selectedClientId > 0 && $selectedBoutiqueId > 0)
    {
        ohnous_mark_conversation_as_read($selectedClientId, $selectedBoutiqueId);
        $selectedMessages = ohnous_get_messages_for_conversation($selectedClientId, $selectedBoutiqueId);

        foreach($conversations as $conversation)
        {
            if((int)$conversation['client_id'] === $selectedClientId && (int)$conversation['boutique_id'] === $selectedBoutiqueId)
            {
                $selectedConversationTitle = $conversation['other_name'];
                break;
            }
        }

        if($selectedConversationTitle === 'Choisissez une conversation')
        {
            $fallbackStore = only_select("boutiques", "id = ".$selectedBoutiqueId, null, null);
            $fallbackUser = only_select("utilisateur", "id = ".$selectedClientId, null, null);
            $selectedConversationTitle = $currentAccount['type'] === 'boutique'
                ? ($fallbackUser['nom'] ?? 'Client OhNous')
                : ($fallbackStore['nom'] ?? 'Boutique OhNous');
        }
    }
?>
<script>
    let home_page = true;
    window.ohnousMessagesConfig = {
        currentAccountType: <?= json_encode($currentAccount['type']) ?>,
        currentAccountId: <?= (int)$currentAccount['id'] ?>,
        selectedClientId: <?= (int)$selectedClientId ?>,
        selectedBoutiqueId: <?= (int)$selectedBoutiqueId ?>,
        refreshEveryMs: 4000
    };
</script>
<div class="messages-page">
    <section class="messages-shell liquid-panel">
        <aside class="messages-sidebar">
            <div class="messages-sidebar__head">
                <div>
                    <h1>Messages</h1>
                    <p>Retrouvez vos conversations en cours.</p>
                </div>
                <span class="messages-sidebar__badge"><?= gestion_9_plus(ohnous_get_unread_messages_count($currentAccount)) ?></span>
            </div>

            <div class="messages-conversation-list" id="messages_conversation_list">
                <?php
                    if(empty($conversations))
                    {
                        echo '<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-comments"></i></div><p>Aucune conversation pour le moment.</p></div>';
                    }
                    else
                    {
                        foreach($conversations as $conversation)
                        {
                            $activeClass = ((int)$conversation['client_id'] === $selectedClientId && (int)$conversation['boutique_id'] === $selectedBoutiqueId) ? 'is-active' : '';
                            $avatar = '<img src="'.htmlspecialchars(ohnous_get_profile_picture($conversation['other_profile'], $conversation['other_type']), ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars($conversation['other_name'], ENT_QUOTES, 'UTF-8').'">';

                            echo '
                                <a href="/message?client='.(int)$conversation['client_id'].'&boutique='.(int)$conversation['boutique_id'].'" class="messages-conversation-card '.$activeClass.'" data-conversation-card>
                                    <div class="messages-conversation-card__avatar">'.$avatar.'</div>
                                    <div class="messages-conversation-card__content">
                                        <strong>'.htmlspecialchars($conversation['other_name'], ENT_QUOTES, 'UTF-8').'</strong>
                                        <p>'.htmlspecialchars(mb_strimwidth((string)$conversation['last_message'], 0, 90, '...'), ENT_QUOTES, 'UTF-8').'</p>
                                    </div>
                                    <div class="messages-conversation-card__meta">
                                        <span>'.($conversation['last_message_date'] !== '' ? ohnous_format_review_date($conversation['last_message_date']) : '').'</span>
                                        '.($conversation['unread_count'] > 0 ? '<em>'.gestion_9_plus($conversation['unread_count']).'</em>' : '').'
                                    </div>
                                </a>';
                        }
                    }
                ?>
            </div>
        </aside>

        <section class="messages-chat-panel">
            <div class="messages-chat-panel__head">
                <div>
                    <h2 id="messages_conversation_title"><?= htmlspecialchars($selectedConversationTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p>Les nouveaux messages apparaissent automatiquement.</p>
                </div>
            </div>

            <div class="messages-thread" id="messages_thread" data-client-id="<?= (int)$selectedClientId ?>" data-boutique-id="<?= (int)$selectedBoutiqueId ?>">
                <?php
                    if($selectedClientId <= 0 || $selectedBoutiqueId <= 0)
                    {
                        echo '<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-comment-dots"></i></div><p>Choisissez une conversation pour commencer.</p></div>';
                    }
                    else
                    {
                        foreach($selectedMessages as $message)
                        {
                            echo ohnous_render_message_bubble($message, $currentAccount);
                        }
                    }
                ?>
            </div>

            <form class="messages-composer" id="messages_composer">
                <textarea id="message_text" placeholder="Écrivez votre message ici..." <?= ($selectedClientId <= 0 || $selectedBoutiqueId <= 0) ? 'disabled' : '' ?>></textarea>
                <button type="submit" class="btn_ohnous" <?= ($selectedClientId <= 0 || $selectedBoutiqueId <= 0) ? 'disabled' : '' ?>>Envoyer</button>
            </form>
        </section>
    </section>
</div>

<script src="/asset/js/messages.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/messages.js") ?>" defer></script>
