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
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
                <div class="titre_login_page">
                    Connexion admin
                </div>
                <div class="text_login_page">
                    Connectez-vous à l’espace d’administration OhNous.
                </div>
                <form method="POST" action="" id="admin_login_form" class="div_form_ohnous">
                    <?php renderHoneypot('connexion_admin'); ?>
                    <div class="form_ohnous">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="admin_email" placeholder="Email admin">
                    </div>
                    <div class="form_ohnous password">
                        <i class="fa-solid fa-lock"></i>
                        <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                        <input type="password" id="admin_password" placeholder="Mot de passe admin">
                    </div>
                    <div class="form_ohnous word">
                        <a href="/admin-mot-de-passe">Mot de passe oublié ?</a>
                    </div>
                    <div class="form_ohnous submit">
                        <button type="submit" class="btn_ohnous">Accéder à l’admin</button>
                    </div>
                    <div class="form_ohnous word">
                        <a href="/connexion" class="link">Revenir à la connexion classique</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/asset/js/admin_login.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_login.js") ?>" defer></script>
