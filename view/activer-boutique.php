<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['store_ohnous_987654321']))
    {
        header("Location:/connexion");
        exit();
    }

    $boutique = only_select("boutiques", "unique_id = '".$_SESSION['store_ohnous_987654321']."'", null, null);
    if(!$boutique)
    {
        header("Location:/404");
        exit();
    }

    if(ohnous_is_store_active($boutique))
    {
        header("Location:/boutique");
        exit();
    }

    $latestRequest = ohnous_get_latest_store_activation_request((int)$boutique['id']);
?>
<script>
    let home_page = true;
</script>
<div class="content_page">
    <section class="liquid-panel activation-panel">
        <div class="activation-panel__icon"><i class="fa-solid fa-store"></i></div>
        <div class="activation-panel__content">
            <h1>Activer votre boutique</h1>
            <p>Ajoutez au moins une information de contact pour envoyer votre demande d’activation.</p>
            <div class="activation-panel__details">
                <span><strong>Boutique :</strong> <?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                <span><strong>Email :</strong> <?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php if($latestRequest): ?>
                    <span><strong>Dernière demande :</strong> <?= ohnous_get_store_activation_status_label($latestRequest['statut'] ?? 'en_attente') ?></span>
                <?php endif; ?>
            </div>
            <form id="store_activation_form" class="activation-user-form">
                <?php renderHoneypot('activation_boutique'); ?>
                <div class="admin-form-grid">
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="store_activation_whatsapp">WhatsApp</label>
                        <input type="tel" id="store_activation_whatsapp" class="input_ajout_image checkout-input js-store-intl-phone" autocomplete="tel">
                    </div>
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="store_activation_telephone">Numéro d’appel</label>
                        <input type="tel" id="store_activation_telephone" class="input_ajout_image checkout-input js-store-intl-phone" autocomplete="tel">
                    </div>
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="store_activation_instagram">Instagram</label>
                        <input type="text" id="store_activation_instagram" class="input_ajout_image checkout-input" placeholder="@nom">
                    </div>
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="store_activation_facebook">Facebook</label>
                        <input type="text" id="store_activation_facebook" class="input_ajout_image checkout-input" placeholder="nom du compte">
                    </div>
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="store_activation_tiktok">TikTok</label>
                        <input type="text" id="store_activation_tiktok" class="input_ajout_image checkout-input" placeholder="@nom">
                    </div>
                </div>
                <button type="submit" class="btn_ohnous" id="send_store_activation_request">Envoyer la demande d’activation</button>
            </form>
        </div>
    </section>
</div>

<link rel="stylesheet" href="/asset/css/intlTelInput.min.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/intlTelInput.min.css") ?>">
<link rel="stylesheet" href="/asset/css/intl-tel-input-fix.css?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/css/intl-tel-input-fix.css") ?>">
<script src="/asset/js/intlTelInputWithUtils.min.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/intlTelInputWithUtils.min.js") ?>" defer></script>
<script src="/asset/js/store_activation.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/store_activation.js") ?>" defer></script>
