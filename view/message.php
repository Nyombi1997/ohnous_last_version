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
                            <i class="fa-solid fa-comments"></i>
                        </div>
                    </div>
                    <div class="titre_login_page">
                        Message(s)
                    </div>
                </div>
                <div class="div_choix_chat">
                    <a href="" class="choix_chat"><i class="fa-solid fa-user"></i> <p>Beni <span>2</span></p></a>
                    <a href="" class="choix_chat"><i class="fa-solid fa-store"></i> <p>Beni <span>2</span></p></a>
                </div>
            </div>
        </div>
	</div>