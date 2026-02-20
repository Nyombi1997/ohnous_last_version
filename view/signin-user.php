
<script>
    let home_page = true;
</script>
	<!-- intro -->
	<div class="intro-hero plus">
        <!-- container login page -->
        <div class="container_login_page">
            <div class="div_login_page">
                <div class="div_detail_login_page">
                    <div class="div_icone_login_page">
                        <div class="icone_login_page">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Inscription
                    </div>
                    <form method="POST" action="" class="div_form_ohnous" id="form">
                        <div class="form_ohnous">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="" id="nom" autocomplete="off" placeholder="Nom utilisateur" required>
                        </div>
                        <div class="choix_form_ohnous null" id="choix_form_ohnous">
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
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous">S'inscrire</button>
                        </div>
                        <div class="form_ohnous word">
                            <a href="/connexion" class="link">Vous avez déjà un compte ohnous ?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	</div>

    <!-- script signin -->
	<script src="/asset/js/signin_check_user.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/signin_check_user.js") ?>" defer></script> 