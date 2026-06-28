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
?>
<script>let home_page = true;</script>
<div class="intro-hero plus">
    <div class="blob-bg"><span id="new_boutique"></span></div>
    <div class="container_login_page account-edit-shell">
        <?php $storeNavCurrent = 'security'; include VIEW.'store-account-nav.php'; ?>
        <div class="div_login_page">
            <div class="div_detail_login_page">
                <div class="div_icone_login_page">
                    <div class="icone_login_page"><i class="fa-solid fa-lock"></i></div>
                </div>
                <div class="titre_login_page">Sécurité boutique</div>
                <form action="" method="post" id="form_password" class="div_form_ohnous form_edit_boutique">
                    <div class="form_ohnous password">
                        <i class="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                        <input type="password" name="" id="password" autocomplete="current-password" placeholder="Ancien mot de passe" required>
                    </div>
                    <div class="form_ohnous password">
                        <i class="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                        <input type="password" name="" id="password" autocomplete="new-password" placeholder="Nouveau mot de passe" required>
                    </div>
                    <div class="form_ohnous password">
                        <i class="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                        <input type="password" name="" id="password" autocomplete="new-password" placeholder="Confirmer nouveau mot de passe" required>
                    </div>
                    <div class="form_ohnous submit">
                        <button type="submit" class="btn_ohnous" id="valid_password">Modifier</button>
                    </div>
                    <div class="form_ohnous word">
                        <a href="/changer-mot-de-passe">Mot de passe oublié ?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/asset/js/edit_boutique.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/edit_boutique.js") ?>" defer></script>
