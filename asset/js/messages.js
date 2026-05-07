(function($){
    const config = window.ohnousMessagesConfig || null;
    if(!config){
        return;
    }

    const $thread = $('#messages_thread');
    const $composer = $('#messages_composer');
    const $textarea = $('#message_text');
    const $articleInput = $('#message_article_id');
    const $taggedArticle = $('#message_tagged_article');
    const $suggest = $('#message_article_suggest');
    const $shell = $('.messages-shell');
    let searchTimer = null;
    let isFetching = false;

    function escapeHtml(value){
        return String(value || '').replace(/[&<>"']/g, function(char){
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function scrollToBottom(){
        const element = $thread.get(0);
        if(element){
            element.scrollTop = element.scrollHeight;
        }
    }

    function scrollToMessage(messageId){
        const id = Number(messageId || 0);
        const thread = $thread.get(0);
        const target = id > 0 ? $thread.find(`[data-message-id="${id}"]`).get(0) : null;
        if(thread && target){
            thread.scrollTop = Math.max(0, target.offsetTop - thread.offsetTop - 18);
            return true;
        }
        return false;
    }

    function getMessageIds(){
        return $thread.find('[data-message-id]').map(function(){
            return Number($(this).attr('data-message-id') || 0);
        }).get();
    }

    function scrollToFirstNewMessage(previousIds){
        const known = {};
        (previousIds || []).forEach(function(id){
            known[Number(id)] = true;
        });

        const $firstNew = $thread.find('[data-message-id]').filter(function(){
            const id = Number($(this).attr('data-message-id') || 0);
            return id > 0 && !known[id] && $(this).hasClass('is-theirs');
        }).first();

        if($firstNew.length){
            return scrollToMessage($firstNew.attr('data-message-id'));
        }

        return false;
    }

    function updateReport(html){
        const $report = $('#messages_report_list');
        if($report.length && typeof html === 'string'){
            $report.html(html);
        }
    }

    function updateUnreadBadge(count){
        const value = Number(count || 0);
        $('.messages-sidebar__badge').text(value > 9 ? '+9' : value);
    }

    function updateChatChrome(conversation){
        const name = conversation.otherName || 'Compte OhNous';
        const profile = conversation.otherProfile || '/asset/images/profile/default.jpg';
        const link = conversation.otherLink || '#';

        $('#messages_conversation_title').text(name);
        $('.messages-chat-identity__avatar').attr('href', link).removeClass('is-hidden').find('img').attr({
            src: profile,
            alt: name
        });
        $('.messages-current-profile').attr('href', config.currentAccountLink || '/compte').removeClass('is-hidden').find('img').attr({
            src: config.currentAccountProfile || '/asset/images/profile/default.jpg',
            alt: config.currentAccountName || 'Compte OhNous'
        });
        $('.messages-back-link').removeClass('is-hidden');
        $('.messages-report-profile img').attr({
            src: profile,
            alt: name
        });
        $('.messages-report-profile strong').text(name);
        $('.messages-report-profile a').attr('href', link);
    }

    function showConversationList(pushUrl){
        config.selectedConversationType = 'boutique';
        config.selectedClientType = 'utilisateur';
        config.selectedClientId = 0;
        config.selectedBoutiqueId = 0;

        $shell.removeClass('has-selected-conversation show-report-mobile').addClass('has-no-selected-conversation');
        $('[data-conversation-card]').removeClass('is-active is-loading');
        $('.messages-back-link, .messages-chat-identity__avatar, .messages-current-profile').addClass('is-hidden');
        $('#messages_conversation_title').text('Choisissez une conversation');
        $thread.html('<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-comment-dots"></i></div><p>Aucune conversation n’a été initiée pour le moment.</p></div>');
        $thread.attr({
            'data-client-id': 0,
            'data-boutique-id': 0,
            'data-first-unread-id': 0
        });
        $('#messages_composer button[type="submit"]').attr('disabled', 'disabled');
        if(pushUrl){
            window.history.pushState({}, '', '/message');
        }
        fetchConversations();
    }

    function activateConversation(conversation){
        config.selectedConversationType = conversation.conversationType || 'boutique';
        config.selectedClientType = conversation.clientType || 'utilisateur';
        config.selectedClientId = Number(conversation.clientId || 0);
        config.selectedBoutiqueId = Number(conversation.boutiqueId || 0);

        $thread.attr({
            'data-client-id': config.selectedClientId,
            'data-boutique-id': config.selectedBoutiqueId,
            'data-first-unread-id': 0
        });

        $('.messages-shell').removeClass('has-no-selected-conversation').addClass('has-selected-conversation');
        $('#messages_composer button[type="submit"]').removeAttr('disabled');
        updateChatChrome(conversation);
    }

    function refreshConversationList(conversations){
        const $list = $('#messages_conversation_list');
        if(!$list.length || !Array.isArray(conversations)){
            return;
        }

        if(conversations.length === 0){
            $list.html('<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-comments"></i></div><p>Aucune conversation pour le moment.</p></div>');
            return;
        }

        const html = conversations.map(function(conversation){
            const type = conversation.conversation_type || 'boutique';
            const clientType = conversation.client_type || 'utilisateur';
            const isActive = type === config.selectedConversationType && clientType === config.selectedClientType && Number(conversation.client_id) === Number(config.selectedClientId) && Number(conversation.boutique_id) === Number(config.selectedBoutiqueId);
            const avatarUrl = escapeHtml(conversation.other_profile || '/asset/images/profile/default.jpg');
            const otherName = escapeHtml(conversation.other_name || 'Compte OhNous');
            const lastMessage = escapeHtml(conversation.last_message_preview || conversation.last_message || '').replace(/\[\[article:\d+\]\]/g, '').trim();
            const articleIcon = Number(conversation.last_message_has_article || 0) ? '<i class="fa-solid fa-link"></i> ' : '';
            const unread = Number(conversation.unread_count || 0) > 0 ? `<em>${Number(conversation.unread_count) > 9 ? '9+' : Number(conversation.unread_count)}</em>` : '';
            const url = type === 'admin'
                ? `/message?admin=1&client_type=${encodeURIComponent(clientType)}&client=${Number(conversation.client_id)}`
                : `/message?client=${Number(conversation.client_id)}&boutique=${Number(conversation.boutique_id)}`;

            return `
                <a href="${url}" class="messages-conversation-card ${isActive ? 'is-active' : ''}" data-conversation-card
                    data-conversation-type="${escapeHtml(type)}"
                    data-client-type="${escapeHtml(clientType)}"
                    data-client-id="${Number(conversation.client_id)}"
                    data-boutique-id="${Number(conversation.boutique_id)}"
                    data-other-name="${otherName}"
                    data-other-profile="${avatarUrl}"
                    data-other-link="${escapeHtml(conversation.other_link || '#')}">
                    <div class="messages-conversation-card__avatar"><img src="${avatarUrl}" alt="${otherName}"></div>
                    <div class="messages-conversation-card__content">
                        <strong>${otherName}</strong>
                        <p>${articleIcon}${lastMessage || 'Article joint'}</p>
                    </div>
                    <div class="messages-conversation-card__meta">${unread}</div>
                </a>
            `;
        }).join('');

        $list.html(html);
    }

    function fetchConversations(){
        $.post('/fonctions/messages_actions.php', {
            action: 'fetch',
            conversation_type: 'boutique',
            client_type: 'utilisateur',
            client_id: 0,
            boutique_id: 0
        }, function(data){
            if(data.result === 'ok'){
                refreshConversationList(data.conversations);
                updateUnreadBadge(data.unread_count);
            }
        }, 'json');
    }

    function getAtQuery(){
        const input = $textarea.get(0);
        const value = $textarea.val() || '';
        const cursor = input ? input.selectionStart : value.length;
        const beforeCursor = value.slice(0, cursor);
        const match = beforeCursor.match(/(?:^|\s)@([^\s@]{0,40})$/);
        return match ? match[1] : null;
    }

    function clearArticleTag(){
        $articleInput.val('');
        $taggedArticle.empty().removeClass('is-visible');
    }

    function selectArticle(article){
        const input = $textarea.get(0);
        const value = $textarea.val() || '';
        const cursor = input ? input.selectionStart : value.length;
        const beforeCursor = value.slice(0, cursor);
        const afterCursor = value.slice(cursor);
        const cleanedBefore = beforeCursor.replace(/(?:^|\s)@[^\s@]{0,40}$/, function(match){
            return match.charAt(0) === ' ' ? ' ' : '';
        });

        $textarea.val((cleanedBefore + afterCursor).trimStart());
        $articleInput.val(article.id);
        $taggedArticle.html(`
            <div class="message-tagged-article__card">
                <img src="${escapeHtml(article.image || '/asset/images/profile/default.jpg')}" alt="${escapeHtml(article.nom)}">
                <span>
                    <strong>${escapeHtml(article.nom)}</strong>
                    <small>${Number(article.prix || 0).toFixed(2)} USD</small>
                </span>
                <button type="button" aria-label="Retirer l’article" id="message_remove_article"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).addClass('is-visible');
        $suggest.empty().removeClass('is-visible');
    }

    function renderArticleSuggestions(articles){
        if(!Array.isArray(articles) || articles.length === 0){
            $suggest.html('<div class="message-article-suggest__empty">Aucun article trouvé.</div>').addClass('is-visible');
            return;
        }

        const html = articles.map(function(article){
            return `
                <button type="button" class="message-article-suggest__item" data-article="${escapeHtml(JSON.stringify(article))}">
                    <img src="${escapeHtml(article.image || '/asset/images/profile/default.jpg')}" alt="${escapeHtml(article.nom)}">
                    <span>
                        <strong>${escapeHtml(article.nom)}</strong>
                        <small>${Number(article.prix || 0).toFixed(2)} USD</small>
                    </span>
                </button>
            `;
        }).join('');

        $suggest.html(html).addClass('is-visible');
    }

    function resizeComposer(){
        const element = $textarea.get(0);
        if(!element){
            return;
        }

        const lineHeight = 22;
        const maxHeight = (lineHeight * 4) + 28;
        element.style.height = 'auto';
        element.style.height = Math.min(element.scrollHeight, maxHeight) + 'px';
        element.style.overflowY = element.scrollHeight > maxHeight ? 'auto' : 'hidden';
    }

    function searchArticles(){
        const query = getAtQuery();
        if(query === null){
            $suggest.empty().removeClass('is-visible');
            return;
        }

        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function(){
            $.post('/fonctions/messages_actions.php', {
                action: 'search_articles',
                client_id: config.selectedClientId,
                boutique_id: config.selectedBoutiqueId,
                query: query
            }, function(data){
                if(data.result === 'ok'){
                    renderArticleSuggestions(data.articles);
                }
            }, 'json');
        }, 220);
    }

    function fetchMessages(){
        if(isFetching){
            return;
        }

        if(!config.selectedClientId || (config.selectedConversationType !== 'admin' && !config.selectedBoutiqueId)){
            fetchConversations();
            return;
        }

        isFetching = true;
        $.post('/fonctions/messages_actions.php', {
            action: 'fetch',
            conversation_type: config.selectedConversationType,
            client_type: config.selectedClientType,
            client_id: config.selectedClientId,
            boutique_id: config.selectedBoutiqueId
        }, function(data){
            if(data.result !== 'ok'){
                return;
            }

            refreshConversationList(data.conversations);
            updateUnreadBadge(data.unread_count);
            const previousIds = getMessageIds();
            $thread.html(data.messages_html);
            updateReport(data.report_html);
            if(!scrollToFirstNewMessage(previousIds)){
                scrollToBottom();
            }
        }, 'json').always(function(){
            isFetching = false;
        });
    }

    $('.messages-back-link').on('click', function(e){
        e.preventDefault();
        showConversationList(true);
    });

    function openConversationCard($card, pushUrl){
        const conversation = {
            conversationType: $card.attr('data-conversation-type') || 'boutique',
            clientType: $card.attr('data-client-type') || 'utilisateur',
            clientId: $card.attr('data-client-id') || 0,
            boutiqueId: $card.attr('data-boutique-id') || 0,
            otherName: $card.attr('data-other-name') || 'Compte OhNous',
            otherProfile: $card.attr('data-other-profile') || '/asset/images/profile/default.jpg',
            otherLink: $card.attr('data-other-link') || '#'
        };

        activateConversation(conversation);
        $('[data-conversation-card]').removeClass('is-active');
        $card.addClass('is-active is-loading');

        $.post('/fonctions/messages_actions.php', {
            action: 'fetch',
            conversation_type: config.selectedConversationType,
            client_type: config.selectedClientType,
            client_id: config.selectedClientId,
            boutique_id: config.selectedBoutiqueId
        }, function(data){
            if(data.result !== 'ok'){
                return;
            }

            refreshConversationList(data.conversations);
            updateUnreadBadge(data.unread_count);
            $thread.html(data.messages_html);
            updateReport(data.report_html);
            $thread.attr('data-first-unread-id', Number(data.first_unread_message_id || 0));
            scrollToMessage(data.first_unread_message_id) || scrollToBottom();
            if(pushUrl){
                window.history.pushState({}, '', $card.attr('href'));
            }
        }, 'json').always(function(){
            $card.removeClass('is-loading');
        });
    }

    $(document).on('click', '[data-conversation-card]', function(e){
        e.preventDefault();
        openConversationCard($(this), true);
    });

    window.addEventListener('popstate', function(){
        const currentUrl = window.location.pathname + window.location.search;
        if(window.location.pathname === '/message' && window.location.search === ''){
            showConversationList(false);
            return;
        }

        const $card = $('[data-conversation-card]').filter(function(){
            return $(this).attr('href') === currentUrl;
        }).first();

        if($card.length){
            openConversationCard($card, false);
        }
    });

    $composer.on('submit', function(e){
        e.preventDefault();

        const message = ($textarea.val() || '').trim();
        if(message === '' && !$articleInput.val()){
            Swal.fire({
                icon: 'error',
                title: 'Écrivez un message',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        const $button = $composer.find('button[type="submit"]');
        const tempText = $button.html();
        const pendingId = 'pending-' + Date.now();
        let sentOk = false;
        $button.attr('disabled', 'disabled').html('<i class="fa-solid fa-circle-notch rotate"></i>');
        $thread.append(`
            <article class="message-bubble is-mine is-pending" id="${pendingId}">
                <div class="message-bubble__content">${escapeHtml(message || 'Article joint')}</div>
                <span class="message-bubble__date"><i class="fa-solid fa-circle-notch rotate"></i> Envoi en cours...</span>
            </article>
        `);
        scrollToBottom();

        $.post('/fonctions/messages_actions.php', {
            action: 'send',
            conversation_type: config.selectedConversationType,
            client_type: config.selectedClientType,
            client_id: config.selectedClientId,
            boutique_id: config.selectedBoutiqueId,
            message: message,
            article_id: $articleInput.val()
        }, function(data){
            if(data.result !== 'ok'){
                $('#' + pendingId).addClass('is-error').find('.message-bubble__date').html('<i class="fa-solid fa-triangle-exclamation"></i> Non envoyé');
                Swal.fire({
                    icon: 'error',
                    title: data.msg || "Impossible d'envoyer le message.",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            sentOk = true;
            $textarea.val('');
            resizeComposer();
            clearArticleTag();
            refreshConversationList(data.conversations);
            updateUnreadBadge(data.unread_count);
            $thread.html(data.messages_html);
            updateReport(data.report_html);
            scrollToBottom();
        }, 'json').always(function(){
            if(sentOk){
                $('#' + pendingId).remove();
            }
            $button.removeAttr('disabled').html(tempText);
        });
    });

    $textarea.on('keyup click', searchArticles);
    $textarea.on('input', resizeComposer);

    $('#messages_report_close').on('click', function(){
        $shell.removeClass('show-report-mobile');
    });

    $suggest.on('click', '.message-article-suggest__item', function(){
        const raw = $(this).attr('data-article') || '{}';
        try {
            selectArticle(JSON.parse(raw));
        } catch(e) {
            $suggest.empty().removeClass('is-visible');
        }
    });

    $taggedArticle.on('click', '#message_remove_article', clearArticleTag);

    if(!scrollToMessage($thread.attr('data-first-unread-id'))){
        scrollToBottom();
    }
    resizeComposer();
    window.setInterval(fetchMessages, Number(config.refreshEveryMs || 4000));
})(jQuery);
