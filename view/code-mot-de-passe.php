<?php
    // reset l'email adresse qui était utiliser avant
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['email_ohnous_987654321']))
    {
        header("location:/changer-mot-de-passe");
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
                        Code de vérification
                    </div>
                    <div class="text_login_page">
                        Entrez le code à 6 chiffres envoyer via email
                    </div>
                    <form method="POST" action="" id="form" class="div_form_ohnous">
                        <?php renderHoneypot('verification_code'); ?>
                        <div class="form_ohnous">
                            <i class="fa-solid fa-key"></i>
                            <input type="number" name="code_ohnous" id="code" placeholder="Code de vérification">
                        </div>
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	</div>

<!-- script signin -->
<script src="/asset/js/verification-code.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/verification-code.js") ?>" defer></script>
