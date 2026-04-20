<?php
    ohnous_require_store_or_redirect();

    $currentAccount = ohnous_get_current_account();
    $articleId = (int)($_GET['id'] ?? 0);
    $article = $articleId > 0 ? only_select('articles', 'id = '.$articleId, null, null) : null;

    if(!$article || (int)$article['boutique'] !== (int)$currentAccount['id'])
    {
        header('Location:/boutique');
        exit();
    }

    $boutique = only_select('boutiques', 'id = '.(int)$article['boutique'], null, null);
    $articleCategorie = only_select('categorie_article', 'article = '.(int)$article['id'], null, null);
    $articleType = only_select('types_article', 'article = '.(int)$article['id'], null, null);
    $articleTailles = select_bdd($bdd, 'taille_articles', 'article = '.(int)$article['id'], null, 0, 'id ASC', false);
    $categorieList = select_bdd($bdd, 'categorie', null, null, 0, 'nom ASC', false);
    $articleImages = select_bdd($bdd, 'image_articles', 'article = '.(int)$article['id'], null, 0, 'id ASC', false);

    $selectedTailles = array_map(function($row){
        return (int)$row['taille'];
    }, $articleTailles);

    $existingImages = array_map(function($image){
        return [
            'id' => 'existing_'.$image['id'],
            'dbId' => (int)$image['id'],
            'dataUrl' => (string)$image['img'],
            'style' => (string)($image['styles'] ?? ''),
            'background' => (string)($image['background'] ?? ''),
            'fileId' => (string)($image['fileId'] ?? ''),
            'isPrimary' => false,
            'isExisting' => true
        ];
    }, $articleImages);

    if(!empty($existingImages))
    {
        $existingImages[0]['isPrimary'] = true;
    }
?>
<script>
    let home_page = true;
    window.ohnousAdminEditProductConfig = {
        articleId: <?= (int)$article['id'] ?>,
        articleSlug: <?= json_encode((string)$article['slug']) ?>,
        storeSlug: <?= json_encode((string)($boutique['slug'] ?? 'boutique')) ?>,
        selectedCategory: <?= (int)($articleCategorie['categorie'] ?? 0) ?>,
        selectedType: <?= (int)($articleType['types'] ?? 0) ?>,
        selectedTailles: <?= json_encode(array_values($selectedTailles)) ?>,
        existingImages: <?= json_encode($existingImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        submitUrl: '/fonctions/store_article_actions.php',
        actionName: 'update_article',
        redirectUrl: '/boutique'
    };
</script>
<div class="content_page">
    <section class="liquid-panel upload-product-panel">
        <div class="upload-product-panel__head">
            <div>
                <h1>Modifier l’article</h1>
                <p>Retrouvez l’espace d’ajout avec vos images, vos options et la possibilité de remplacer ou supprimer une photo.</p>
            </div>
            <a href="/boutique" class="btn_ohnous second">Retour boutique</a>
        </div>

        <form id="admin_edit_article_form" class="upload-product-form" enctype="multipart/form-data">
            <div class="upload-product-layout">
                <div class="upload-product-main">
                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Images du produit</label>
                        <div class="upload-zone" id="uploadZone">
                            <div class="upload-content">
                                <span class="upload-icon"><i class="fa-solid fa-images"></i></span>
                                <p>Glissez-déposez vos nouvelles images ici</p>
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
                        <input type="text" id="nom_article" class="input_ajout_image" value="<?= htmlspecialchars((string)$article['nom'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Prix ($)</label>
                        <input type="number" id="prix_article" step="0.01" min="0" class="input_ajout_image" value="<?= htmlspecialchars((string)$article['prix'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <?php if(ohnous_column_exists('articles', 'promo_prix')): ?>
                        <div class="form_group_ajout_image">
                            <label class="label_ajout_image">Prix promotionnel ($)</label>
                            <input type="number" id="promo_prix_article" step="0.01" min="0" class="input_ajout_image" value="<?= htmlspecialchars((string)($article['promo_prix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    <?php endif; ?>

                    <div class="admin-form-grid single">
                        <label class="admin-switch-card">
                            <input type="checkbox" id="reserve_article" <?= ((int)$article['reserve'] === 1) ? 'checked' : '' ?>>
                            <span>Article visible dans le site</span>
                        </label>
                        <?php if(ohnous_column_exists('articles', 'promo_actif')): ?>
                            <label class="admin-switch-card">
                                <input type="checkbox" id="promo_actif_article" <?= ((int)($article['promo_actif'] ?? 0) === 1) ? 'checked' : '' ?>>
                                <span>Mettre en promotion</span>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image">Catégorie</label>
                        <select class="input_ajout_image" id="category_select">
                            <option value="0">-- Choisir une catégorie --</option>
                            <?php foreach($categorieList as $category): ?>
                                <option value="<?= (int)$category['id'] ?>" <?= ((int)$category['id'] === (int)($articleCategorie['categorie'] ?? 0)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
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
                        <textarea id="description_article" rows="5" class="input_ajout_image"><?= htmlspecialchars((string)$article['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <button type="submit" class="btn_ohnous">Enregistrer les modifications</button>
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
                <img id="cropImage" src="">
            </div>
            <div class="crop-preview"></div>
        </div>
        <div class="modal-footer">
            <button class="btn_ohnous second" onclick="closeCrop()">Annuler</button>
            <button class="btn_ohnous" onclick="applyCrop()">Appliquer</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= ASSET ?>css/style_ajout_image.css?<?= filemtime("asset/css/style_ajout_image.css") ?>">
<link rel="stylesheet" href="<?= ASSET ?>css/cropper.min.css?<?= filemtime("asset/css/cropper.min.css") ?>">
<script src="<?= ASSET ?>js/cropper.min.js?<?= filemtime("asset/js/cropper.min.js") ?>"></script>
<script src="https://unpkg.com/imagekit-javascript/dist/imagekit.min.js"></script>
<script src="/asset/js/admin_edit_article.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_edit_article.js") ?>" defer></script>
