<?php
    // reset l'email adresse qui était utiliser avant
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['email_ohnous_987654321']))
    {
        header("location:/changer-mot-de-passe");
    }
    else
    {
        $unique_id = $_SESSION['email_ohnous_987654321'];
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$unique_id.'"', $limit = null, $offset = 0, $order = null, $random = false);
        $utilisateur = select_bdd($bdd, "utilisateur", $where = 'unique_id = "'.$unique_id.'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)>0)
        {
            if($boutique[0]['code_password']!=null)
            {
                header("location:/changer-mot-de-passe");
            }            
        }
        elseif(count($utilisateur)>0)
        {
            if($utilisateur[0]['code_password']!=null)
            {
                header("location:/changer-mot-de-passe");
            }            
        }
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
                        Nouveau mot de passe
                    </div>
                    <form method="POST" action="" class="div_form_ohnous" id="form">
                        <?php renderHoneypot('nouveau_mot_de_passe'); ?>
                        <div class="form_ohnous password">
                            <i class="fa-solid fa-lock"></i>
                            <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                            <input type="password" name="" id="password" autocomplete="new-password" placeholder="Nouveau mot de passe" required>
                        </div>
                        <div class="form_ohnous password">
                            <i class="fa-solid fa-lock"></i>
                            <i class="fa-solid fa-eye-slash vu_password_form_ohnous"></i>
                            <input type="password" name="" id="password" autocomplete="new-password" placeholder="Confirmer mot de passe" required>
                        </div>
                        <div class="form_ohnous submit">
                            <button type="submit" class="btn_ohnous">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
	</div>

    <!-- script signin -->
	<script src="/asset/js/new_password.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/new_password.js") ?>" defer></script>
