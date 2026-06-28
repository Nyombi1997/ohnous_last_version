<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['store_ohnous_987654321']))
    {
        header("Location:/404");
        exit();
    }

    $boutique = only_select("boutiques", "unique_id = '".$_SESSION['store_ohnous_987654321']."'", null, null);
    if(!$boutique)
    {
        header("Location:/404");
        exit();
    }

    $latestRequest = ohnous_get_latest_store_activation_request((int)$boutique['id']);
?>
<script>let home_page = true;</script>
<div class="intro-hero plus">
    <div class="blob-bg"><span id="new_boutique"></span></div>
    <div class="container_login_page account-edit-shell">
        <?php $storeNavCurrent = 'contact'; include VIEW.'store-account-nav.php'; ?>
        <div class="div_login_page">
            <div class="div_detail_login_page">
                <div class="div_icone_login_page">
                    <div class="icone_login_page"><i class="fa-solid fa-address-book"></i></div>
                </div>
                <div class="titre_login_page">Contact et activation</div>
                <p class="account-edit-muted">
                    <?= ohnous_is_store_active($boutique) ? 'Gérez les liens de contact visibles sur votre boutique.' : 'Votre boutique doit être activée avant d’ajouter ses liens publics.' ?>
                </p>

                <?php if($latestRequest): ?>
                    <div class="activation-panel__details">
                        <span>Dernière demande : <?= ohnous_get_store_activation_status_label($latestRequest['statut'] ?? 'en_attente') ?></span>
                        <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$latestRequest['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <?php if(!ohnous_is_store_active($boutique)): ?>
                    <a href="/activer-boutique" class="btn_ohnous">Demander l’activation</a>
                <?php else: ?>
                    <form action="" method="post" id="form_socials" class="div_form_ohnous form_edit_boutique">
                        <div class="form_ohnous">
                            <i class="fa-brands fa-facebook"></i>
                            <input type="text" id="facebook" autocomplete="off" placeholder="Lien Facebook" value="<?= htmlspecialchars((string)($boutique['facebook'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form_ohnous">
                            <i class="fa-brands fa-instagram"></i>
                            <input type="text" id="instagram" autocomplete="off" placeholder="Lien Instagram" value="<?= htmlspecialchars((string)($boutique['instagram'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form_ohnous">
                            <i class="fa-brands fa-x-twitter"></i>
                            <input type="text" id="twitter" autocomplete="off" placeholder="Lien X / Twitter" value="<?= htmlspecialchars((string)($boutique['twitter'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form_ohnous">
                            <i class="fa-brands fa-threads"></i>
                            <input type="text" id="trends" autocomplete="off" placeholder="Lien Threads" value="<?= htmlspecialchars((string)($boutique['trends'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form_ohnous">
                            <i class="fa-brands fa-tiktok"></i>
                            <input type="text" id="tiktok" autocomplete="off" placeholder="Lien TikTok" value="<?= htmlspecialchars((string)($boutique['tiktok'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="store-socials-errors" id="store_socials_errors"></div>
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous" id="valid_socials">Enregistrer les liens</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="/asset/js/edit_boutique.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/edit_boutique.js") ?>" defer></script>
