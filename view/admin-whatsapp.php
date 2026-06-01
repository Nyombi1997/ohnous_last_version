<?php
    ohnous_require_admin_or_redirect();

    require_once $_SERVER['DOCUMENT_ROOT'] . '/services/WhatsAppService.php';
    WhatsAppService::ensureMessagesTable($bdd);

    $stmt = $bdd->query("
        SELECT *
        FROM whatsapp_messages
        ORDER BY created_at DESC, id DESC
        LIMIT 100
    ");
    $whatsappMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>WhatsApp</h1>
            <p>Messages entrants et tests d'envoi via WhatsApp Cloud API.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('whatsapp') ?>

    <section class="liquid-panel admin-account-form-panel">
        <h2>Envoyer un message test</h2>
        <form id="admin_whatsapp_test_form" class="delivery-zone-form">
            <div class="form_group_ajout_image">
                <label class="label_ajout_image" for="whatsapp_test_to">Numéro WhatsApp</label>
                <input type="text" id="whatsapp_test_to" name="to" class="input_ajout_image checkout-input" placeholder="243..." required>
            </div>

            <div class="form_group_ajout_image">
                <label class="label_ajout_image" for="whatsapp_test_message">Message</label>
                <textarea id="whatsapp_test_message" name="message" class="input_ajout_image checkout-input" rows="4" required>Test WhatsApp OhNous</textarea>
            </div>

            <button type="submit" class="btn_ohnous">Envoyer</button>
        </form>
    </section>

    <section class="liquid-panel admin-account-list-panel">
        <div class="delivery-zone-list-panel__head">
            <h2>Messages WhatsApp</h2>
            <span><?= count($whatsappMessages) ?> message(s)</span>
        </div>

        <?php if(empty($whatsappMessages)): ?>
            <div class="empty-liquid-state">
                <div class="empty-liquid-state__icon"><i class="fa-brands fa-whatsapp"></i></div>
                <p>Aucun message WhatsApp pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-scroll">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Nom</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Direction</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($whatsappMessages as $message): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$message['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$message['contact_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= nl2br(htmlspecialchars((string)$message['message_body'], ENT_QUOTES, 'UTF-8')) ?></td>
                                <td><?= htmlspecialchars((string)$message['message_type'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$message['direction'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$message['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$message['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <script src="/asset/js/admin_whatsapp.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_whatsapp.js") ?>" defer></script>
</div>
