<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* SI ON EST CONNECTER */
    if(isset($_SESSION['user_ohnous_987654321']))
    {
        $user = select_bdd($bdd, "utilisateur", $where = 'unique_id = "'.$_SESSION['user_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($user)!=0)
        {
            $user = $user[0];
            $backgrounds = "";
            if($user['backgrounds']!='')
            {
                $backgrounds = 'style="background : '.$user['backgrounds'].';"';
            }
            $profile = '<img src="'.ASSET.'images/profile/default.jpg" alt="" srcset="">';
            if($user['profile']!='')
            {
                $profile = '
                            <img 
                                class="blur-up"
                                src="'.$user['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                srcset="
                                    '.$user['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                    '.$user['profile'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                    '.$user['profile'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
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
            <?php $accountNavCurrent = 'infos'; include VIEW.'account-nav.php'; ?>
            <div class="div_login_page">
                <div class="div_detail_login_page">
                    <div class="div_icone_login_page">
                        <div class="icone_login_page">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Modifier utilisateur
                    </div>
                    <div class="div_form_ohnous" id="form">
                        <!-- details -->
                        <div class="form_edit_boutique">
                            <div class="div_edit_profil_boutique">
                                <div class="edit_profil_boutique">
                                    <?= $profile ?>
                                </div>
                            </div>
                            <div class="form_ohnous submit">
                                <button type="submit" class="btn_ohnous" id="edit_profil">Modifier</button>
                            </div>
                        </div>
                        <!-- details -->
                        <form action="" method="post" id="form_nom" class="form_edit_boutique">
                            <div class="form_ohnous">
                                <i class="fa-solid fa-store"></i>
                                <input type="text" name="" id="nom" autocomplete="off" placeholder="Nom boutique" required value='<?= $user['nom'] ?>'>
                            </div>
                            <div class="form_ohnous submit">
                                <button type="submit" class="btn_ohnous" id="valide_nom">Modifier</button>
                            </div>
                            <div class="choix_form_ohnous null" id="choix_form_ohnous">
                            </div>
                        </form>
                        <!-- details -->
                        <form action="" method="post" id="form_email" class="form_edit_boutique">
                            <div class="form_ohnous">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" name="" id="email" autocomplete="off" placeholder="email" required value='<?= $user['adresse_email'] ?>'>
                            </div>
                            <div class="form_ohnous submit">
                                <button type="submit" class="btn_ohnous" id="valide_email">Modifier</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
	</div>

    <!-- script edit utilisateur -->
	<script src="/asset/js/edit_user.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/edit_user.js") ?>" defer></script> 
