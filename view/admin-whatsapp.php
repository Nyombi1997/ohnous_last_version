<?php
    ohnous_require_admin_or_redirect();

    require_once $_SERVER['DOCUMENT_ROOT'] . '/services/WhatsAppService.php';
    WhatsAppService::ensureTables($bdd);
?>
<link rel="stylesheet" href="/asset/css/admin_whatsapp.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/admin_whatsapp.css") ?>">
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>WhatsApp</h1>
            <p>Messagerie clients WhatsApp.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('whatsapp') ?>

    <section class="whatsapp-admin-shell liquid-panel">
        <aside class="whatsapp-admin-sidebar">
            <div class="whatsapp-admin-sidebar__head">
                <h2>Conversations</h2>
                <button type="button" class="whatsapp-admin-icon-btn" id="whatsapp_refresh_btn" title="Actualiser">
                    <i class="fa-solid fa-rotate"></i>
                </button>
            </div>
            <div class="whatsapp-conversation-list" id="whatsapp_conversation_list"></div>
        </aside>

        <div class="whatsapp-admin-chat">
            <div class="whatsapp-chat-head" id="whatsapp_chat_head">
                <div>
                    <strong>Sélectionnez une conversation</strong>
                    <span>Aucun message ouvert</span>
                </div>
            </div>

            <div class="whatsapp-customer-panel" id="whatsapp_customer_panel"></div>

            <div class="whatsapp-message-list" id="whatsapp_message_list">
                <div class="empty-liquid-state">
                    <div class="empty-liquid-state__icon"><i class="fa-brands fa-whatsapp"></i></div>
                    <p>Aucune conversation sélectionnée.</p>
                </div>
            </div>

            <form class="whatsapp-reply-form" id="whatsapp_reply_form">
                <input type="hidden" name="conversation_id" id="whatsapp_conversation_id" value="">
                <textarea name="message" id="whatsapp_reply_message" class="checkout-input" rows="1" placeholder="Écrire un message..." disabled></textarea>
                <button type="submit" class="btn_ohnous" disabled>
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Envoyer</span>
                </button>
            </form>
        </div>
    </section>

    <script src="/asset/js/admin_whatsapp.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_whatsapp.js") ?>" defer></script>
</div>
