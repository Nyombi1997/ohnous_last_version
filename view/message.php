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
    $selectedConversationType = isset($_GET['admin']) ? 'admin' : 'boutique';
    $selectedClientType = html_entity_decode(filter_var($_GET['client_type'] ?? $currentAccount['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $selectedClientId = (int)($_GET['client'] ?? 0);
    $selectedBoutiqueId = (int)($_GET['boutique'] ?? 0);

    if($selectedConversationType === 'admin' && $currentAccount['type'] !== 'admin')
    {
        $selectedClientType = $currentAccount['type'];
        $selectedClientId = (int)$currentAccount['id'];
        $selectedBoutiqueId = 0;
    }
    elseif($selectedConversationType === 'boutique')
    {
        $selectedClientType = 'utilisateur';
    }

    if($selectedConversationType !== 'admin' && $selectedBoutiqueId > 0 && $selectedClientId <= 0 && $currentAccount['type'] === 'utilisateur')
    {
        $selectedClientId = (int)$currentAccount['id'];
    }

    $selectedMessages = [];
    $selectedConversationTitle = 'Choisissez une conversation';
    $selectedConversationAvatar = ohnous_get_default_profile_image('utilisateur');
    $selectedConversationLink = '#';
    $selectedBackLink = '/message';
    $firstUnreadMessageId = 0;
    $selectedReportHtml = '<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-folder-open"></i></div><p>Aucun article ou lien reçu.</p></div>';
    $hasSelectedConversation = $selectedConversationType === 'admin'
        ? $selectedClientId > 0
        : ($selectedClientId > 0 && $selectedBoutiqueId > 0);

    if($hasSelectedConversation)
    {
        $selectedMessages = ohnous_get_messages_for_conversation($selectedClientId, $selectedBoutiqueId, $selectedConversationType, $selectedClientType);
        $selectedReportHtml = ohnous_get_chat_report_html($selectedMessages, $currentAccount, $selectedBoutiqueId);

        foreach($conversations as $conversation)
        {
            if(
                (string)($conversation['conversation_type'] ?? 'boutique') === $selectedConversationType
                && (string)($conversation['client_type'] ?? 'utilisateur') === $selectedClientType
                && (int)$conversation['client_id'] === $selectedClientId
                && (int)$conversation['boutique_id'] === $selectedBoutiqueId
            )
            {
                $selectedConversationTitle = $conversation['other_name'];
                $selectedConversationAvatar = $conversation['other_profile'];
                $selectedConversationLink = $conversation['other_link'] ?? '#';
                break;
            }
        }

        if($selectedConversationTitle === 'Choisissez une conversation')
        {
            if($selectedConversationType === 'admin')
            {
                if($currentAccount['type'] === 'admin')
                {
                    $target = $selectedClientType === 'boutique'
                        ? only_select("boutiques", "id = ".$selectedClientId, null, null)
                        : only_select("utilisateur", "id = ".$selectedClientId, null, null);
                    $selectedConversationTitle = $target['nom'] ?? 'Compte OhNous';
                    $selectedConversationAvatar = ohnous_get_profile_picture($target['profile'] ?? '', $selectedClientType);
                    $selectedConversationLink = !empty($target['slug'])
                        ? ($selectedClientType === 'boutique' ? '/boutique/'.$target['slug'] : '/utilisateur/'.$target['slug'])
                        : '#';
                }
                else
                {
                    $selectedConversationTitle = 'Admin OhNous';
                    $selectedConversationAvatar = ohnous_get_profile_picture('/asset/images/icons/favicon-1.png', 'admin');
                }
            }
            else
            {
                $fallbackStore = only_select("boutiques", "id = ".$selectedBoutiqueId, null, null);
                $fallbackUser = only_select("utilisateur", "id = ".$selectedClientId, null, null);
                $selectedConversationTitle = $currentAccount['type'] === 'boutique'
                    ? ($fallbackUser['nom'] ?? 'Client OhNous')
                    : ($fallbackStore['nom'] ?? 'Boutique OhNous');
                $selectedConversationAvatar = $currentAccount['type'] === 'boutique'
                    ? ohnous_get_profile_picture($fallbackUser['profile'] ?? '', 'utilisateur')
                    : ohnous_get_profile_picture($fallbackStore['profile'] ?? '', 'boutique');
                $selectedConversationLink = $currentAccount['type'] === 'boutique'
                    ? (!empty($fallbackUser['slug']) ? '/utilisateur/'.$fallbackUser['slug'] : '#')
                    : (!empty($fallbackStore['slug']) ? '/boutique/'.$fallbackStore['slug'] : '/boutique');
            }
        }

        foreach($selectedMessages as $message)
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

        ohnous_mark_conversation_as_read($selectedClientId, $selectedBoutiqueId, $selectedConversationType, $selectedClientType);
    }
?>
<script>
    let home_page = true;
    window.ohnousMessagesConfig = {
        currentAccountType: <?= json_encode($currentAccount['type']) ?>,
        currentAccountId: <?= (int)$currentAccount['id'] ?>,
        selectedConversationType: <?= json_encode($selectedConversationType) ?>,
        selectedClientType: <?= json_encode($selectedClientType) ?>,
        selectedClientId: <?= (int)$selectedClientId ?>,
        selectedBoutiqueId: <?= (int)$selectedBoutiqueId ?>,
        currentAccountName: <?= json_encode($currentAccount['nom'] ?? 'Compte OhNous') ?>,
        currentAccountLink: <?= json_encode($currentAccount['link'] ?? '/compte') ?>,
        currentAccountProfile: <?= json_encode(ohnous_get_profile_picture($currentAccount['profile'] ?? '', $currentAccount['type'] ?? 'utilisateur')) ?>,
        refreshEveryMs: 4000
    };
</script>
<div class="messages-page">
    <section class="messages-shell liquid-panel <?= $hasSelectedConversation ? 'has-selected-conversation' : 'has-no-selected-conversation' ?>">
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
                            $activeClass = (
                                (string)($conversation['conversation_type'] ?? 'boutique') === $selectedConversationType
                                && (string)($conversation['client_type'] ?? 'utilisateur') === $selectedClientType
                                && (int)$conversation['client_id'] === $selectedClientId
                                && (int)$conversation['boutique_id'] === $selectedBoutiqueId
                            ) ? 'is-active' : '';
                            $avatar = '<img src="'.htmlspecialchars(ohnous_get_profile_picture($conversation['other_profile'], $conversation['other_type']), ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars($conversation['other_name'], ENT_QUOTES, 'UTF-8').'">';
                            $conversationUrl = ($conversation['conversation_type'] ?? 'boutique') === 'admin'
                                ? '/message?admin=1&client_type='.urlencode((string)$conversation['client_type']).'&client='.(int)$conversation['client_id']
                                : '/message?client='.(int)$conversation['client_id'].'&boutique='.(int)$conversation['boutique_id'];
                            $lastMessagePreview = mb_strimwidth((string)($conversation['last_message_preview'] ?? $conversation['last_message']), 0, 90, '...');
                            $articleIcon = !empty($conversation['last_message_has_article']) ? '<i class="fa-solid fa-link"></i> ' : '';

                            echo '
                                <a href="'.$conversationUrl.'" class="messages-conversation-card '.$activeClass.'" data-conversation-card
                                    data-conversation-type="'.htmlspecialchars((string)($conversation['conversation_type'] ?? 'boutique'), ENT_QUOTES, 'UTF-8').'"
                                    data-client-type="'.htmlspecialchars((string)($conversation['client_type'] ?? 'utilisateur'), ENT_QUOTES, 'UTF-8').'"
                                    data-client-id="'.(int)$conversation['client_id'].'"
                                    data-boutique-id="'.(int)$conversation['boutique_id'].'"
                                    data-other-name="'.htmlspecialchars($conversation['other_name'], ENT_QUOTES, 'UTF-8').'"
                                    data-other-profile="'.htmlspecialchars($conversation['other_profile'], ENT_QUOTES, 'UTF-8').'"
                                    data-other-link="'.htmlspecialchars($conversation['other_link'], ENT_QUOTES, 'UTF-8').'">
                                    <div class="messages-conversation-card__avatar">'.$avatar.'</div>
                                    <div class="messages-conversation-card__content">
                                        <strong>'.htmlspecialchars($conversation['other_name'], ENT_QUOTES, 'UTF-8').'</strong>
                                        <p>'.$articleIcon.htmlspecialchars($lastMessagePreview, ENT_QUOTES, 'UTF-8').'</p>
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
                <a href="/message" class="messages-back-link <?= !$hasSelectedConversation ? 'is-hidden' : '' ?>" aria-label="Retour aux conversations">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="messages-chat-identity">
                    <a href="<?= htmlspecialchars($selectedConversationLink, ENT_QUOTES, 'UTF-8') ?>" class="messages-chat-identity__avatar <?= !$hasSelectedConversation ? 'is-hidden' : '' ?>" aria-label="Voir le profil">
                        <img src="<?= htmlspecialchars($selectedConversationAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($selectedConversationTitle, ENT_QUOTES, 'UTF-8') ?>">
                    </a>
                    <div>
                        <h2 id="messages_conversation_title"><?= htmlspecialchars($selectedConversationTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($currentAccount['link'] ?? '/compte', ENT_QUOTES, 'UTF-8') ?>" class="messages-current-profile <?= !$hasSelectedConversation ? 'is-hidden' : '' ?>" aria-label="Voir mon compte">
                    <img src="<?= htmlspecialchars(ohnous_get_profile_picture($currentAccount['profile'] ?? '', $currentAccount['type'] ?? 'utilisateur'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($currentAccount['nom'] ?? 'Compte OhNous', ENT_QUOTES, 'UTF-8') ?>">
                </a>
            </div>

            <div class="messages-thread" id="messages_thread" data-client-id="<?= (int)$selectedClientId ?>" data-boutique-id="<?= (int)$selectedBoutiqueId ?>" data-first-unread-id="<?= (int)$firstUnreadMessageId ?>">
                <?php
                    if(!$hasSelectedConversation)
                    {
                        echo '<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-comment-dots"></i></div><p>Aucune conversation n’a été initiée pour le moment.</p></div>';
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
                <input type="hidden" id="message_article_id" value="">
                <div class="messages-composer__field">
                    <div class="message-tagged-article" id="message_tagged_article"></div>
                    <div class="message-article-suggest" id="message_article_suggest"></div>
                    <textarea id="message_text" rows="1" placeholder="votre message ici..."></textarea>
                </div>
                <button type="submit" class="btn_ohnous" <?= !$hasSelectedConversation ? 'disabled' : '' ?>>Envoyer</button>
            </form>
        </section>

        <aside class="messages-report-panel">
            <div class="messages-report-profile">
                <img src="<?= htmlspecialchars($selectedConversationAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($selectedConversationTitle, ENT_QUOTES, 'UTF-8') ?>">
                <div>
                    <strong><?= htmlspecialchars($selectedConversationTitle, ENT_QUOTES, 'UTF-8') ?></strong>
                    <a href="<?= htmlspecialchars($selectedConversationLink, ENT_QUOTES, 'UTF-8') ?>">Voir le compte</a>
                </div>
            </div>

            <div class="messages-report-panel__head">
                <div>
                    <h2>Dernier</h2>
                    <p>Articles et liens reçus.</p>
                </div>
                <button type="button" class="messages-report-close" id="messages_report_close" aria-label="Retour au chat">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="messages-report-list" id="messages_report_list">
                <?= $selectedReportHtml ?>
            </div>
        </aside>
    </section>
</div>

<script src="/asset/js/messages.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/messages.js") ?>" defer></script>
