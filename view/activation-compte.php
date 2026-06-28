<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['user_ohnous_987654321']))
    {
        header("Location:/connexion");
        exit();
    }
    $user = only_select("utilisateur", "unique_id = '".addslashes($_SESSION['user_ohnous_987654321'])."'", null, null);
    if(!$user)
    {
        header("Location:/404");
        exit();
    }
    $isActive = ohnous_is_user_active($user);
    $latestRequest = ohnous_get_latest_user_activation_request((int)$user['id']);
?>
<script>let home_page = true;</script>
<div class="content_page account-page-shell">
    <section class="liquid-panel activation-panel user-activation-panel">
        <div class="activation-panel__icon"><i class="fa-solid fa-user-check"></i></div>
        <div class="activation-panel__content">
            <h1>Activation du compte</h1>
            <?php if($isActive): ?>
                <p>Votre compte est déjà activé.</p>
            <?php else: ?>
                <p>Ajoutez au moins une information de contact pour envoyer votre demande.</p>
                <?php if($latestRequest): ?>
                    <div class="activation-panel__details">
                        <span>Dernière demande : <?= ohnous_get_user_activation_status_label($latestRequest['statut'] ?? 'en_attente') ?></span>
                        <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$latestRequest['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>
                <form id="user_activation_form" class="activation-user-form">
                    <div class="admin-form-grid">
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image" for="activation_whatsapp">WhatsApp</label>
                            <input type="tel" id="activation_whatsapp" class="input_ajout_image checkout-input js-intl-phone" autocomplete="tel">
                        </div>
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image" for="activation_telephone">Numéro d’appel</label>
                            <input type="tel" id="activation_telephone" class="input_ajout_image checkout-input js-intl-phone" autocomplete="tel">
                        </div>
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image" for="activation_instagram">Instagram</label>
                            <input type="text" id="activation_instagram" class="input_ajout_image checkout-input" placeholder="@nom">
                        </div>
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image" for="activation_facebook">Facebook</label>
                            <input type="text" id="activation_facebook" class="input_ajout_image checkout-input" placeholder="nom du compte">
                        </div>
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image" for="activation_tiktok">TikTok</label>
                            <input type="text" id="activation_tiktok" class="input_ajout_image checkout-input" placeholder="@nom">
                        </div>
                    </div>
                    <button type="submit" class="btn_ohnous">Envoyer la demande</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</div>
<link rel="stylesheet" href="/asset/css/intlTelInput.min.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/intlTelInput.min.css") ?>">
<script src="/asset/js/intlTelInputWithUtils.min.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/intlTelInputWithUtils.min.js") ?>" defer></script>
<script src="/asset/js/user_activation.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/user_activation.js") ?>" defer></script>
