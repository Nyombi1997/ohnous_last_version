<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['store_ohnous_987654321']))
    {
        header("Location:/connexion");
        exit();
    }

    $boutique = only_select("boutiques", "unique_id = '".$_SESSION['store_ohnous_987654321']."'", null, null);
    if(!$boutique)
    {
        header("Location:/404");
        exit();
    }

    if(!ohnous_is_store_active($boutique))
    {
        header("Location:/activer-boutique");
        exit();
    }
?>
<script>
    let home_page = true;
    window.ohnousUploadProductConfig = {
        storeSlug: <?= json_encode((string)$boutique['slug']) ?>
    };
</script>
<div class="content_page">
    <section class="liquid-panel upload-product-panel">
        <div class="upload-product-panel__head">
            <div>
                <h1>Ajouter un article</h1>
                <p>Ajoutez plusieurs images, préparez votre fiche produit et publiez-la dans votre boutique.</p>
            </div>
            <a href="/boutique" class="btn_ohnous second">Retour boutique</a>
        </div>

        <form id="productForm" class="upload-product-form" enctype="multipart/form-data">
            <div class="upload-product-layout">
                <div class="upload-product-main">
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Images du produit</label>
                        <div class="upload-zone" id="uploadZone">
                            <div class="upload-content">
                                <span class="upload-icon"><i class="fa-solid fa-images"></i></span>
                                <p>Glissez-déposez vos images ici</p>
                                <p class="upload-subtext">ou</p>
                                <button type="button" class="btn_ohnous btn-primary" id="open_file_picker">Choisir des images</button>
                                <input type="file" id="fileInput" multiple accept="image/*" class="input_ajout_image" style="display:none;">
                            </div>
                        </div>
                        <div class="image-preview image-preview--product" id="imagePreview"></div>
                    </div>
                </div>

                <div class="upload-product-side">
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Nom</label>
                        <input type="text" id="nom_article" class="input_ajout_image" maxlength="150" required>
                        <small class="article-name-limit-hint" id="article_name_limit_hint"></small>
                    </div>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Prix ($)</label>
                        <input type="text" id="prix_article" inputmode="decimal" class="input_ajout_image js-price-input" required>
                    </div>

                    <?php if(ohnous_column_exists('articles', 'promo_prix')): ?>
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image">Prix promotionnel ($)</label>
                            <input type="text" id="promo_prix_article" inputmode="decimal" class="input_ajout_image js-price-input">
                        </div>
                    <?php endif; ?>

                    <?php if(ohnous_column_exists('articles', 'promo_actif')): ?>
                        <div class="admin-form-grid single">
                            <label class="admin-switch-card">
                                <input type="checkbox" id="promo_actif_article">
                                <span>Mettre l’article en promotion</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="admin-form-grid single">
                        <label class="admin-switch-card">
                            <input type="checkbox" id="reserve_article">
                            <span>Article réservé</span>
                        </label>
                    </div>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Catégorie</label>
                        <select class="input_ajout_image" id="category_select">
                            <option value="0">-- Choisir une catégorie --</option>
                            <?php
                                $categories = select_bdd($bdd, "categorie", null, null, 0, "nom", false);
                                foreach($categories as $category)
                                {
                                    echo '<option value="'.$category['id'].'">'.htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8').'</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <div class="form_group_ajout_image null" id="types_container">
                        <label class="label_ajout_image">Type</label>
                        <div class="table" id="table_types"></div>
                    </div>

                    <div class="form_group_ajout_image null" id="tailles_container">
                        <label class="label_ajout_image">Taille</label>
                        <div class="table" id="table_tailles"></div>
                    </div>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Description</label>
                        <textarea id="description_article" rows="5" class="input_ajout_image"></textarea>
                    </div>

                    <button type="submit" class="btn_ohnous">Publier l’article</button>
                </div>
            </div>
        </form>
    </section>
</div>

<div class="modal" id="cropModal">
    <div class="modal_background" onclick="closeCrop()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Recadrer l’image</h3>
            <button class="close" onclick="closeCrop()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="crop-container">
                <div id="croppieCropImage" class="croppie-crop-image"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn_ohnous second" onclick="closeCrop()">Annuler</button>
            <button class="btn_ohnous" onclick="applyCrop()">Appliquer</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?=  ASSET ?>css/style_ajout_image.css?<?= filemtime("asset/css/style_ajout_image.css") ?>">
<script src="https://unpkg.com/imagekit-javascript/dist/imagekit.min.js"></script>
<script src="/asset/js/croppie_uploader.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/croppie_uploader.js") ?>"></script>
<script src="/asset/js/upload_product.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/upload_product.js") ?>" defer></script>
