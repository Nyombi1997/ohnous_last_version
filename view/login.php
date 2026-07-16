
<script>
    let home_page = true;
</script>
	<!-- intro -->
	<div class="intro-hero plus">
		<div class="blob-bg"></div>
        <!-- container login page -->
        <div class="container_login_page">
            <div class="div_login_page">
                <div class="div_detail_login_page">
                    <div class="div_icone_login_page">
                        <div class="icone_login_page">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Connexion
                    </div>
                    <form method="POST" action="" id="form" class="div_form_ohnous">
                        <?php renderHoneypot('connexion'); ?>
                        <div class="form_ohnous">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email_ohnous" id="email" placeholder="email">
                        </div>
                        <div class="form_ohnous password">
                            <i class="fa-solid fa-lock"></i>
                            <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                            <input type="password" name="password_ohnous" id="password" placeholder="Mot de passe">
                        </div>
                        <div class="form_ohnous word">
                            <a href="/changer-mot-de-passe">Mot de passe oublié ?</a>
                        </div>
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous">Se connecter</button>
                        </div>
                        <div class="form_ohnous word">
                            <!-- <a href="/admin-login" class="link">Accéder à l’espace admin</a> -->
                        </div>
                        <div class="form_ohnous word">
                            <a href="/choix-compte" class="link">Vous n'avez pas encore de compte ohnous ?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	</div>

<!-- script signin -->
<script src="/asset/js/login_check.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/login_check.js") ?>" defer></script> 
