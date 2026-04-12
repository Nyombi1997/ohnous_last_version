(function($){
    const config = window.ohnousMessagesConfig || null;
    if(!config){
        return;
    }

    const $thread = $('#messages_thread');
    const $composer = $('#messages_composer');
    const $textarea = $('#message_text');

    function scrollToBottom(){
        const element = $thread.get(0);
        if(element){
            element.scrollTop = element.scrollHeight;
        }
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
            const isActive = Number(conversation.client_id) === Number(config.selectedClientId) && Number(conversation.boutique_id) === Number(config.selectedBoutiqueId);
            const avatarUrl = conversation.other_profile || '/asset/images/profile/default.jpg';
            const avatar = `<img src="${avatarUrl}" alt="${conversation.other_name}">`;
            const unread = Number(conversation.unread_count || 0) > 0 ? `<em>${Number(conversation.unread_count) > 9 ? '9+' : Number(conversation.unread_count)}</em>` : '';

            return `
                <a href="/message?client=${conversation.client_id}&boutique=${conversation.boutique_id}" class="messages-conversation-card ${isActive ? 'is-active' : ''}" data-conversation-card>
                    <div class="messages-conversation-card__avatar">${avatar}</div>
                    <div class="messages-conversation-card__content">
                        <strong>${conversation.other_name}</strong>
                        <p>${conversation.last_message || ''}</p>
                    </div>
                    <div class="messages-conversation-card__meta">${unread}</div>
                </a>
            `;
        }).join('');

        $list.html(html);
    }

    function fetchMessages(silent){
        if(!config.selectedClientId || !config.selectedBoutiqueId){
            return;
        }

        $.post('/fonctions/messages_actions.php', {
            action: 'fetch',
            client_id: config.selectedClientId,
            boutique_id: config.selectedBoutiqueId
        }, function(data){
            if(data.result !== 'ok'){
                return;
            }

            refreshConversationList(data.conversations);
            $thread.html(data.messages_html);
            scrollToBottom();
        }, 'json');
    }

    $composer.on('submit', function(e){
        e.preventDefault();

        const message = ($textarea.val() || '').trim();
        if(message === ''){
            Swal.fire({
                icon: 'error',
                title: 'Écrivez un message',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        const $button = $composer.find('button[type="submit"]');
        const tempText = $button.html();
        $button.attr('disabled', 'disabled').html('<i class="fa-solid fa-circle-notch rotate"></i>');

        $.post('/fonctions/messages_actions.php', {
            action: 'send',
            client_id: config.selectedClientId,
            boutique_id: config.selectedBoutiqueId,
            message: message
        }, function(data){
            if(data.result !== 'ok'){
                Swal.fire({
                    icon: 'error',
                    title: data.msg || "Impossible d'envoyer le message.",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            $textarea.val('');
            refreshConversationList(data.conversations);
            $thread.html(data.messages_html);
            scrollToBottom();
        }, 'json').always(function(){
            $button.removeAttr('disabled').html(tempText);
        });
    });

    scrollToBottom();
    window.setInterval(function(){
        fetchMessages(true);
    }, Number(config.refreshEveryMs || 4000));
})(jQuery);
