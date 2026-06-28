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
	<div class="edit-shop-page">
		<div class="blob-bg" aria-hidden="true">
            <span id="new_boutique"></span>
        </div>
        <div class="edit-shop-shell">
            <div class="edit-shop-layout">
                <aside class="edit-shop-sidebar">
                    <?php $storeNavCurrent = 'infos'; include VIEW.'store-account-nav.php'; ?>
                </aside>
                <main class="edit-shop-content">
                    <section class="edit-shop-card">
                        <div class="edit-shop-card__head">
                            <div>
                                <h2>Profil public</h2>
                                <p>Ces informations sont utilisées sur la page publique de votre boutique.</p>
                            </div>
                            <a href="/boutique" class="edit-shop-view-link">
                                <i class="fa-solid fa-store"></i>
                                Voir la boutique
                            </a>
                        </div>

                        <div class="edit-shop-profile-row">
                            <div class="edit-shop-avatar">
                                <?= $profile ?>
                            </div>
                            <div class="edit-shop-profile-copy">
                                <strong><?= htmlspecialchars((string)$boutique['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <button type="button" class="btn_ohnous edit-shop-secondary-button" id="edit_profil">
                                <i class="fa-solid fa-camera"></i>
                                Modifier la photo
                            </button>
                        </div>

                        <div class="edit-shop-form-title">
                            <p>Espace boutique</p>
                            <h1>Informations générales</h1>
                        </div>

                        <div class="edit-shop-forms" id="form">
                            <form action="" method="post" id="form_nom" class="edit-shop-form">
                                <div class="edit-shop-field">
                                    <label for="nom">Nom de la boutique</label>
                                    <div class="edit-shop-input">
                                        <i class="fa-solid fa-store"></i>
                                        <input type="text" id="nom" autocomplete="off" required value="<?= htmlspecialchars((string)$boutique['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="choix_form_ohnous null" id="choix_form_ohnous"></div>
                                </div>
                                <button type="submit" class="btn_ohnous" id="valide_nom">Modifier</button>
                            </form>

                            <form action="" method="post" id="form_email" class="edit-shop-form">
                                <div class="edit-shop-field">
                                    <label for="email">Adresse email</label>
                                    <div class="edit-shop-input">
                                        <i class="fa-solid fa-envelope"></i>
                                        <input type="email" id="email" autocomplete="off" required value="<?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn_ohnous" id="valide_email">Modifier</button>
                            </form>

                            <form action="" method="post" id="form_description" class="edit-shop-form edit-shop-form--wide">
                                <div class="edit-shop-field">
                                    <label for="description">Description</label>
                                    <textarea id="description" placeholder="La description de votre boutique"><?= htmlspecialchars((string)$boutique['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <button type="submit" class="btn_ohnous" id="valid_description">Modifier</button>
                            </form>
                        </div>
                    </section>
                </main>
            </div>
        </div>
	</div>

    <!-- script edit boutique -->
	<script src="/asset/js/edit_boutique.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/edit_boutique.js") ?>" defer></script> 
