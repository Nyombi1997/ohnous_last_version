<?php
    $token = html_entity_decode(filter_var($_GET['token'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $request = null;
    if($token !== '' && ohnous_table_exists('boutique_activation_requests'))
    {
        $request = only_select("boutique_activation_requests", "token = '".$token."'", null, null);
    }
    $boutique = $request ? only_select("boutiques", "id = ".(int)$request['boutique_id'], null, null) : null;
?>
<script>
    let home_page = true;
</script>
<div class="content_page">
    <section class="liquid-panel activation-panel activation-panel--admin">
        <div class="activation-panel__icon"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="activation-panel__content">
            <h1>Activation boutique</h1>
            <?php if(!$request || !$boutique): ?>
                <p>Le lien d’activation est invalide ou expiré.</p>
            <?php else: ?>
                <p>Définissez la durée d’activation de la boutique.</p>
                <div class="activation-panel__details vertical">
                    <span><strong>Boutique :</strong> <?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span><strong>Email :</strong> <?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span><strong>Description :</strong> <?= nl2br(htmlspecialchars((string)$boutique['description'], ENT_QUOTES, 'UTF-8')) ?></span>
                    <span><strong>Statut demande :</strong> <?= htmlspecialchars((string)$request['statut'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <form id="admin_store_activation_form" class="activation-admin-form">
                    <input type="hidden" id="activation_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form_ohnous">
                        <i class="fa-regular fa-calendar"></i>
                        <input type="number" min="0" id="activation_months" placeholder="Nombre de mois">
                    </div>
                    <div class="form_ohnous">
                        <i class="fa-regular fa-clock"></i>
                        <input type="number" min="0" id="activation_days" placeholder="Nombre de jours">
                    </div>
                    <button type="submit" class="btn_ohnous">Activer la boutique</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if($request && $boutique): ?>
    <script src="/asset/js/admin_store_activation.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_store_activation.js") ?>" defer></script>
<?php endif; ?>
