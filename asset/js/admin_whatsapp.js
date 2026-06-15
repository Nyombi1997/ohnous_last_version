(function ($) {
    var selectedConversationId = null;
    var pollingDelay = 4000;
    var pollingTimer = null;

    var $shell = $('.whatsapp-admin-shell');
    var $conversationList = $('#whatsapp_conversation_list');
    var $messageList = $('#whatsapp_message_list');
    var $chatHead = $('#whatsapp_chat_head');
    var $customerPanel = $('#whatsapp_customer_panel');
    var $form = $('#whatsapp_reply_form');
    var $conversationInput = $('#whatsapp_conversation_id');
    var $messageInput = $('#whatsapp_reply_message');
    var $submitButton = $form.find('button[type="submit"]');

    if (!$conversationList.length) {
        return;
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        var date = new Date(String(value).replace(' ', 'T'));
        if (isNaN(date.getTime())) {
            return escapeHtml(value);
        }

        return date.toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function post(action, payload, callback) {
        payload = payload || {};
        payload.action = action;
        return $.post('/fonctions/whatsapp_admin_actions.php', payload, callback, 'json');
    }

    function loadConversations(keepSelection) {
        post('conversations', {}, function (data) {
            if (!data || data.result !== 'ok') {
                return;
            }

            renderConversations(data.conversations || []);

            if (selectedConversationId) {
                markActiveConversation();
            }
        });
    }

    function renderConversations(conversations) {
        if (!conversations.length) {
            $conversationList.html(
                '<div class="empty-liquid-state whatsapp-empty-small">' +
                    '<div class="empty-liquid-state__icon"><i class="fa-brands fa-whatsapp"></i></div>' +
                    '<p>Aucune conversation.</p>' +
                '</div>'
            );
            return;
        }

        var html = conversations.map(function (conversation) {
            var title = conversation.contact_name || conversation.phone || conversation.wa_id || 'Client WhatsApp';
            var unread = parseInt(conversation.unread_count || 0, 10);
            var lastMessage = conversation.last_message || ('[' + (conversation.last_message_type || 'message') + ']');

            return '' +
                '<button type="button" class="whatsapp-conversation-item" data-id="' + escapeHtml(conversation.id) + '">' +
                    '<span class="whatsapp-conversation-item__avatar"><i class="fa-brands fa-whatsapp"></i></span>' +
                    '<span class="whatsapp-conversation-item__body">' +
                        '<span class="whatsapp-conversation-item__top">' +
                            '<strong>' + escapeHtml(title) + '</strong>' +
                            '<small>' + formatDate(conversation.last_message_at || conversation.updated_at) + '</small>' +
                        '</span>' +
                        '<span class="whatsapp-conversation-item__bottom">' +
                            '<span>' + escapeHtml(lastMessage) + '</span>' +
                            (unread > 0 ? '<em>' + unread + '</em>' : '') +
                        '</span>' +
                        '<small>' + escapeHtml(conversation.phone || conversation.wa_id || '') + '</small>' +
                    '</span>' +
                '</button>';
        }).join('');

        $conversationList.html(html);
        markActiveConversation();
    }

    function markActiveConversation() {
        $conversationList.find('.whatsapp-conversation-item').removeClass('is-active');
        if (selectedConversationId) {
            $conversationList.find('[data-id="' + selectedConversationId + '"]').addClass('is-active');
        }
    }

    function openConversation(conversationId) {
        selectedConversationId = parseInt(conversationId, 10);
        $conversationInput.val(selectedConversationId);
        $messageInput.prop('disabled', false);
        $submitButton.prop('disabled', false);
        $shell.addClass('has-open-chat');
        markActiveConversation();

        post('messages', { conversation_id: selectedConversationId }, function (data) {
            if (!data || data.result !== 'ok') {
                return;
            }

            renderMessages(data.messages || [], data.conversation || null, data.customer || null);
            renderCustomer(data.customer || null);
            loadConversations(true);
        });
    }

    function renderMessages(messages, conversation, customer) {
        renderChatHead(conversation, customer);

        if (!messages.length) {
            $messageList.html('<div class="empty-liquid-state"><p>Aucun message.</p></div>');
            return;
        }

        var html = messages.map(function (message) {
            var outgoing = message.direction === 'out';
            return '' +
                '<article class="whatsapp-message-bubble ' + (outgoing ? 'is-out' : 'is-in') + '">' +
                    '<div class="whatsapp-message-bubble__text">' + escapeHtml(message.message_body || '[' + (message.message_type || 'message') + ']').replace(/\n/g, '<br>') + '</div>' +
                    '<div class="whatsapp-message-bubble__meta">' +
                        '<span>' + escapeHtml(message.message_type || '') + '</span>' +
                        '<span>' + formatDate(message.created_at) + '</span>' +
                        (message.status ? '<span>' + escapeHtml(message.status) + '</span>' : '') +
                    '</div>' +
                '</article>';
        }).join('');

        $messageList.html(html);
        $messageList.scrollTop($messageList[0].scrollHeight);
    }

    function renderChatHead(conversation, customer) {
        var title = (customer && customer.nom) || (conversation && (conversation.contact_name || conversation.phone || conversation.wa_id)) || 'Client WhatsApp';
        var phone = (customer && customer.telephone) || (conversation && (conversation.phone || conversation.wa_id)) || '';
        var extra = '';

        if (customer) {
            if (customer.email) {
                extra += ' · ' + customer.email;
            }
            if (customer.orders_count != null) {
                extra += ' · ' + customer.orders_count + ' commande' + (parseInt(customer.orders_count, 10) > 1 ? 's' : '');
            }
        }

        $chatHead.html(
            '<button type="button" class="whatsapp-admin-icon-btn whatsapp-chat-back" id="whatsapp_chat_back" title="Retour aux conversations">' +
                '<i class="fa-solid fa-arrow-left"></i>' +
            '</button>' +
            '<div>' +
                '<strong>' + escapeHtml(title) + '</strong>' +
                '<span>' + escapeHtml(phone + extra) + '</span>' +
            '</div>'
        );
    }

    function renderCustomer(customer) {
        if (!customer) {
            $customerPanel.empty();
            return;
        }

        $customerPanel.html(
            '<div class="whatsapp-customer-card">' +
                '<img src="' + escapeHtml(customer.profile || '/asset/images/profile/default.jpg') + '" alt="' + escapeHtml(customer.nom || 'Client') + '">' +
                '<div>' +
                    '<strong>' + escapeHtml(customer.nom || 'Client OhNous') + '</strong>' +
                    '<span>' + escapeHtml(customer.telephone || '') + '</span>' +
                    '<span>' + escapeHtml(customer.email || '') + '</span>' +
                '</div>' +
                '<div>' +
                    '<small>Commandes</small>' +
                    '<strong>' + escapeHtml(customer.orders_count == null ? '-' : customer.orders_count) + '</strong>' +
                    '<span>' + (customer.last_order ? formatDate(customer.last_order) : '') + '</span>' +
                '</div>' +
            '</div>'
        );
    }

    $conversationList.on('click', '.whatsapp-conversation-item', function () {
        openConversation($(this).data('id'));
    });

    $(document).on('click', '#whatsapp_chat_back', function () {
        $shell.removeClass('has-open-chat');
    });

    $('#whatsapp_refresh_btn').on('click', function () {
        loadConversations(true);
        if (selectedConversationId) {
            openConversation(selectedConversationId);
        }
    });

    $form.on('submit', function (e) {
        e.preventDefault();

        var message = $.trim($messageInput.val());
        if (!selectedConversationId || message === '') {
            return;
        }

        $submitButton.prop('disabled', true);
        post('send_reply', {
            conversation_id: selectedConversationId,
            message: message
        }, function (data) {
            if (!data || data.result !== 'ok') {
                Swal.fire({
                    icon: 'error',
                    title: data && data.msg ? data.msg : 'Échec de l’envoi.',
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            $messageInput.val('');
            openConversation(selectedConversationId);
        }).always(function () {
            $submitButton.prop('disabled', false);
        });
    });

    function startPolling() {
        clearInterval(pollingTimer);
        pollingTimer = setInterval(function () {
            loadConversations(true);
            if (selectedConversationId) {
                openConversation(selectedConversationId);
            }
        }, pollingDelay);
    }

    loadConversations(false);
    startPolling();
})(jQuery);
