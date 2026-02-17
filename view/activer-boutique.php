<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* SI ON EST CONNECTER */
    if(isset($_SESSION['store_ohnous_987654321']))
    {
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['store_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)!=0)
        {
            $boutique = $boutique[0];
            $backgrounds = "";
            if($boutique['backgrounds']!='')
            {
                $backgrounds = 'style="background : '.$boutique['backgrounds'].';"';
            }
            $profile = '<img src="'.ASSET.'images/profile/default.jpg" alt="" srcset="">';
            if($boutique['profile']!='')
            {
                $profile = '
                            <img 
                                class="blur-up"
                                src="'.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                srcset="
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                                sizes="(max-width:768px) 90vw, 600px"
                                loading="lazy"
                                class="blur-up"
                            />';
            }
        }
        else
        {
            // Rediriger vers une page d'erreur ou afficher un message
            header("Location:/404");
            exit();
        }
    }
    else
    {
        // Rediriger vers une page d'erreur ou afficher un message
        header("Location:/404");
        exit();
    }
?>
<script>
    let home_page = true;
</script>
	<!-- intro -->
	<div class="intro-hero plus">
		<div class="blob-bg">
            <span id="new_boutique"></span>
        </div>
        <!-- container login page -->
        <div class="container_login_page">
            <div class="div_login_page">
                <div class="div_detail_login_page">
                    <div class="div_icone_login_page">
                        <div class="icone_login_page">
                            <i class="fa-regular fa-money-bill-1"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Activer boutique
                    </div>
                    <div class="div_form_ohnous" id="form">
                        <!-- details -->
                        <form action="" method="post" id="form_nom" class="form_edit_boutique">
                            <div class="form_ohnous">
                                <i class="fa-solid fa-box-open"></i>
                                <select name="" id="">
                                    <option value="0">---Choisissez un abonnement---</option>
                                </select>
                            </div>
                            <div class="form_ohnous submit">
                                <button type="submit" class="btn_ohnous" id="valide_nom">Confirmer</button>
                            </div>
                            <div class="choix_form_ohnous null" id="choix_form_ohnous">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
	</div>

    <!-- script edit boutique -->
	<script src="/asset/js/edit_boutique.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/edit_boutique.js") ?>" defer></script> 