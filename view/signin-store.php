
<script>
    let home_page = true;
</script>
	<!-- intro -->
	<div class="intro-hero plus">
		<div class="blob-bg"><span id="new_boutique"></span></div>
        <!-- container login page -->
        <div class="container_login_page">
            <div class="div_login_page">
                <div class="div_detail_login_page">
                    <div class="div_icone_login_page">
                        <div class="icone_login_page">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Inscription
                    </div>
                    <form method="POST" action="" class="div_form_ohnous" id="form">
                        <div class="form_ohnous">
                            <i class="fa-solid fa-store"></i>
                            <input type="text" name="" id="nom_boutique" autocomplete="off" placeholder="Nom boutique" required>
                        </div>
                        <div class="form_ohnous">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="" id="email" autocomplete="off" placeholder="email" required>
                        </div>
                        <div class="form_ohnous password">
                            <i class="fa-solid fa-lock"></i>
                            <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                            <input type="password" name="" id="password" autocomplete="new-password" placeholder="Mot de passe" required>
                        </div>
                        <div class="form_ohnous password">
                            <i class="fa-solid fa-lock"></i>
                            <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                            <input type="password" name="" id="password" autocomplete="new-password" placeholder="Confirmer mot de passe" required>
                        </div>
                        <div class="form_ohnous word">
                            <a href="/changer-mot-de-passe">Mot de passe oublié ?</a>
                        </div>
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous">S'inscrire</button>
                        </div>
                        <div class="form_ohnous word">
                            <a href="/choix-compte" class="link">Vous avez déjà un compte ohnous ?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	</div>

    <!-- script signin -->
	<script src="/asset/js/signin_check.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/signin_check.js") ?>" defer></script> 