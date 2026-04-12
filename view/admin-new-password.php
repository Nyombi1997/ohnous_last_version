<?php
    $token = trim((string)($_GET['token'] ?? ''));
?>
<script>
    let home_page = true;
</script>
<div class="intro-hero plus">
    <div class="blob-bg"></div>
    <div class="container_login_page">
        <div class="div_login_page">
            <div class="div_detail_login_page">
                <div class="div_icone_login_page">
                    <div class="icone_login_page">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>
                <div class="titre_login_page">
                    Nouveau mot de passe admin
                </div>
                <form method="POST" action="" id="admin_new_password_form" class="div_form_ohnous" data-token="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form_ohnous password">
                        <i class="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                        <input type="password" id="admin_new_password" placeholder="Nouveau mot de passe">
                    </div>
                    <div class="form_ohnous password">
                        <i class="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                        <input type="password" id="admin_confirm_password" placeholder="Confirmer le mot de passe">
                    </div>
                    <div class="form_ohnous submit">
                        <button type="submit" class="btn_ohnous">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/asset/js/admin_new_password.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_new_password.js") ?>" defer></script>
