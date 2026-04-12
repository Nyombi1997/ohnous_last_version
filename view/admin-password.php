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
                        <i class="fa-solid fa-key"></i>
                    </div>
                </div>
                <div class="titre_login_page">
                    Réinitialisation admin
                </div>
                <div class="text_login_page">
                    Entrez l’email présent dans votre table <strong>admins</strong>.
                </div>
                <form method="POST" action="" id="admin_password_request_form" class="div_form_ohnous">
                    <div class="form_ohnous">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="admin_password_email" placeholder="Email admin">
                    </div>
                    <div class="form_ohnous submit">
                        <button type="submit" class="btn_ohnous">Envoyer l’email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/asset/js/admin_password.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_password.js") ?>" defer></script>
