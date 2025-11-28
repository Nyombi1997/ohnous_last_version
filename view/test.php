<?php
// Activer l'affichage des erreurs pour le debug
/* error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once MODEL.'bdd.php';
require_once FONCTION . 'upload_images.php';
require_once FONCTION . 'product_manager.php';

// Debug des données reçues
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== DEBUG UPLOAD ===");
    error_log("FILES: " . print_r($_FILES, true));
    error_log("POST: " . print_r($_POST, true));
    
    // Vérifier la taille des fichiers
    if (isset($_FILES['product_images'])) {
        foreach ($_FILES['product_images']['tmp_name'] as $index => $tmpName) {
            if ($tmpName) {
                error_log("Fichier $index - Taille: " . $_FILES['product_images']['size'][$index] . " - Type: " . $_FILES['product_images']['type'][$index]);
            }
        }
    }
} */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="<?= ASSET ?>css/style_ajout_image.css">
    <link rel="stylesheet" href="<?= ASSET ?>css/cropper.min.css">
</head>
<body>
    <div class="container">
        <h1>➕ Ajouter un produit</h1>
        
        <form id="productForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nom du produit *</label>
                <input type="text" name="product_name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="product_description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Prix * (€)</label>
                <input type="number" name="product_price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Images du produit</label>
                
                <!-- Zone upload -->
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-content">
                        <span class="upload-icon">📁</span>
                        <p>Glissez-déposez vos images ici</p>
                        <p class="upload-subtext">ou</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                            Choisir des fichiers
                        </button>
                        <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                    </div>
                </div>
                
                <!-- Prévisualisation -->
                <div class="image-preview" id="imagePreview"></div>
            </div>
            
            <div class="progress_cropper">
                <div class="progress_bar_cropper"></div>
            </div>

            <button type="submit" class="btn btn-success">Créer le produit</button>
        </form>
    </div>

    <!-- Modal Crop -->
    <div class="modal" id="cropModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✂️ Recadrer l'image</h3>
                <button class="close" onclick="closeCrop()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="crop-container">
                    <img id="cropImage" src="">
                </div>
                <div class="crop-preview"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeCrop()">Annuler</button>
                <button class="btn btn-primary" onclick="applyCrop()">Appliquer</button>
            </div>
        </div>
    </div>

    <script src="<?= ASSET ?>js/cropper.min.js"></script>
    <script src="<?= ASSET ?>js/uploader_ajout_image.js"></script>
    <script src="<?= ASSET ?>js/main_ajout_image.js"></script>
</body>
</html>