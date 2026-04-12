<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* si user */
    if(isset($GLOBALS['user']))
    {
        $user = $GLOBALS['user'];
        /* si il n'y a pas encore de session */
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $user = select_bdd($bdd, "utilisateur", $where = 'id = "'.$user['id'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($user)>0){
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
    else if(isset($_SESSION['user_ohnous_987654321']))
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
            /* verifier si l'utilisateur a déjà reçu l'email de bienvenue */
            $verif_welcome_email = select_bdd($bdd, "bienvenue_email", $where = 'client_unique_id = "'.$user['unique_id'].'"', $limit = null, $offset = 0, $order = null, $random = false);
            if(count($verif_welcome_email)==0)
            {
                welcome($email = $user['adresse_email']);
                $insert_data = [
                    "client_unique_id" => $user['unique_id']
                ];
                insert_bdd($bdd, "bienvenue_email", $insert_data);
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


<!-- baniere -->
<div class="banniere_boutique">
    <!-- container profile -->
    <div class="container_profile_boutique">
        <div class="background_baniere_boutique" <?= $backgrounds; ?>></div>

        <!-- logout -->
        <?php
            if(isset($_SESSION['user_ohnous_987654321']))
            {
                echo '<a href="/deconnexion" class="deconnexion_boutique">Deconnexion</a>';
            }
        ?>

        <!-- profile -->
        <div class="div_profile_boutique user">
            <div class="profile_boutique user">
                <?= $profile; ?>
            </div>
        </div>  

        <!-- div nom -->
        <div class="div_nom_boutique">
            <h1 class="nom_boutique"><?= $user['nom'] ?></h1>
        </div>

        <!-- bouton editer laisser une message et social media -->
        <div class="container_message_edit_social_media_boutique">
            <div class="div_edit_message_boutique">
                <?php
                    if(isset($_SESSION['user_ohnous_987654321']))
                    {
                        echo '
                        <a href="/editer-user" class="editer_boutique message">Editer</a>
                        <a href="/articles-aimes" class="editer_boutique message">Articles aimés</a>';
                    }
                ?>
                <a href="/message" class="editer_boutique message">Message <?php
                    if(isset($_SESSION['user_ohnous_987654321']))
                    {
                        $messages = gestion_9_plus(ohnous_get_unread_messages_count());
                        echo '
                        <span>'.$messages.'</span>';
                    }
                ?></a>
            </div>
        </div>



        <br>
    </div>
</div>

<?php
    $likedArticles = ohnous_get_liked_articles_for_current_account();
?>
<div class="container_affiche_produit liked-account-section" id="liked_account_section">
    <div class="titre">Articles aimés</div>
    <?php
        if(!empty($likedArticles))
        {
            foreach(array_slice($likedArticles, 0, 8) as $likedArticle)
            {
                affiche_produit($likedArticle);
            }
        }
        else
        {
            $suggestions = ohnous_get_article_suggestions([], 8);
            echo '<div class="empty-liquid-state"><div class="empty-liquid-state__icon"><i class="fa-regular fa-heart"></i></div><p>Vous n’avez encore aimé aucun article. Voici quelques suggestions pour commencer.</p></div>';
            foreach($suggestions as $suggestion)
            {
                affiche_produit($suggestion);
            }
        }
    ?>
</div>
