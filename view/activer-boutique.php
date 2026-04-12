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
?>
<script>
    let home_page = true;
</script>
<div class="content_page">
    <section class="liquid-panel activation-panel">
        <div class="activation-panel__icon"><i class="fa-solid fa-store"></i></div>
        <div class="activation-panel__content">
            <h1>Activer votre boutique</h1>
            <p>Votre boutique sera visible sur le site, dans les filtres et dans les articles uniquement après validation.</p>
            <div class="activation-panel__details">
                <span><strong>Boutique :</strong> <?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                <span><strong>Email :</strong> <?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <button type="button" class="btn_ohnous" id="send_store_activation_request">Envoyer la demande d’activation</button>
        </div>
    </section>
</div>

<script src="/asset/js/store_activation.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/store_activation.js") ?>" defer></script>
