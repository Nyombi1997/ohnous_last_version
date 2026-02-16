<?php
    // reset l'email adresse qui était utiliser avant
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['email_ohnous_987654321']))
    {
        unset($_SESSION['email_ohnous_987654321']);
    }
?>
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
                            <i class="fa-solid fa-key"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Mot de passe oublier
                    </div>
                    <div class="text_login_page">
                        Entrez votre adresse email
                    </div>
                    <form method="POST" action="" id="form" class="div_form_ohnous">
                        <div class="form_ohnous">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email_ohnous" id="email" placeholder="email">
                        </div>
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous">Envoyer</button>
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
<script src="/asset/js/change_password_email_check.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/change_password_email_check.js") ?>" defer></script> 