<?php
    ohnous_require_admin_or_redirect();

    $storeId = (int)($_GET['id'] ?? 0);
    $boutique = $storeId > 0 ? only_select('boutiques', 'id = '.$storeId, null, null) : null;

    if(!$boutique)
    {
        header('Location:/admin-boutiques');
        exit();
    }

    $backgrounds = "";
    if(!empty($boutique['backgrounds']))
    {
        $backgrounds = 'style="background : '.htmlspecialchars((string)$boutique['backgrounds'], ENT_QUOTES, 'UTF-8').';"';
    }

    $profile = '<img src="'.ASSET.'images/profile/default.jpg" alt="" srcset="">';
    if(!empty($boutique['profile']))
    {
        $profile = '
                    <img 
                        class="blur-up"
                        src="'.htmlspecialchars((string)$boutique['profile'], ENT_QUOTES, 'UTF-8').'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                        srcset="
                            '.htmlspecialchars((string)$boutique['profile'], ENT_QUOTES, 'UTF-8').'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                            '.htmlspecialchars((string)$boutique['profile'], ENT_QUOTES, 'UTF-8').'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                            '.htmlspecialchars((string)$boutique['profile'], ENT_QUOTES, 'UTF-8').'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                        sizes="(max-width:768px) 90vw, 600px"
                        loading="lazy"
                    />';
    }
?>
<script>
    let home_page = true;
    window.ohnousAdminStoreProfileConfig = {
        storeId: <?= (int)$boutique['id'] ?>,
        storeSlug: <?= json_encode((string)$boutique['slug']) ?>,
        storeName: <?= json_encode((string)$boutique['nom']) ?>,
        fileId: <?= json_encode((string)($boutique['fileId'] ?? '')) ?>,
        redirectUrl: <?= json_encode('/admin-boutique?id='.(int)$boutique['id']) ?>
    };
</script>
<div class="content_page">
    <span id="fileId" data-file="<?= htmlspecialchars((string)($boutique['fileId'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></span>
    <section class="liquid-panel upload-product-panel">
        <div class="upload-product-panel__head">
            <div>
                <h1>Modifier la photo de profil</h1>
                <p>L’admin peut recadrer et remplacer la photo de profil de cette boutique en gardant le même flux que la boutique.</p>
            </div>
            <a href="/admin-boutique?id=<?= (int)$boutique['id'] ?>" class="btn_ohnous second">Retour à la boutique</a>
        </div>

        <div class="div_profile_editer_profile">
            <div class="profile_editer_profile" <?= $backgrounds ?>>
                <?= $profile ?>
            </div>
        </div>

        <form id="productForm" enctype="multipart/form-data">
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Photo de profil</label>

                <div class="upload-zone" id="uploadZone">
                    <div class="upload-content">
                        <span class="upload-icon"><i class="fa-solid fa-folder-open"></i></span>
                        <p>Glissez-déposez votre image ici</p>
                        <p class="upload-subtext">ou</p>
                        <button type="button" class="btn_ohnous btn-primary" id="open_store_profile_picker">
                            Choisir une image
                        </button>
                        <input type="file" id="fileInput" multiple accept="image/*" class="input_ajout_image" style="display: none;">
                    </div>
                </div>

                <div class="image-preview" id="imagePreview"></div>
            </div>

            <button type="submit" class="btn_ohnous btn-success" style="display: none;" id="valide_photo_profile" disabled>Modifier la photo de profil</button>
        </form>
    </section>
</div>

<div class="modal" id="cropModal">
    <div class="modal_background" onclick="closeCrop()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Recadrer l'image</h3>
            <button class="close" type="button" onclick="closeCrop()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="crop-container">
                <img id="cropImage" src="">
            </div>
            <div class="crop-preview"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn_ohnous second" onclick="closeCrop()">Annuler</button>
            <button type="button" class="btn_ohnous" onclick="applyCrop()">Appliquer</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= ASSET ?>css/style_ajout_image.css?<?= filemtime("asset/css/style_ajout_image.css") ?>">
<link rel="stylesheet" href="<?= ASSET ?>css/cropper.min.css?<?= filemtime("asset/css/cropper.min.css") ?>">
<script src="<?= ASSET ?>js/cropper.min.js?<?= filemtime("asset/js/cropper.min.js") ?>"></script>
<script src="https://unpkg.com/imagekit-javascript/dist/imagekit.min.js"></script>
<script src="/asset/js/admin_edit_store_profile.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_edit_store_profile.js") ?>" defer></script>
