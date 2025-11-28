<?php
require_once '../model/bdd.php';
require_once 'image_processor.php';
// Créer d'abord le produit
require_once 'product_manager.php';

class ImageUploader {
    private $bdd;
    
    public function __construct() {
        $this->bdd = Database::getConnection();
    }
    
    public function uploadProductImages($productId, $uploadedFiles, $altTexts = []) {
        error_log("=== DEBUT UPLOAD PRODUCT IMAGES ===");
        error_log("Product ID: $productId");
        error_log("Files count: " . count($uploadedFiles['tmp_name']));
        
        // Créer les dossiers
        $productDir = '../asset/images/articles/' . $productId . '/';
        $qualityDir = $productDir . 'quality/';
        
        error_log("Product Dir: $productDir");
        error_log("Quality Dir: $qualityDir");
        
        if (!file_exists($productDir)) {
            mkdir($productDir, 0777, true);
            error_log("✅ Dossier produit créé");
        }
        if (!file_exists($qualityDir)) {
            mkdir($qualityDir, 0777, true);
            error_log("✅ Dossier quality créé");
        }
        
        $uploadedImages = [];
        $errors = [];
        
        foreach ($uploadedFiles['tmp_name'] as $index => $tmpName) {
            error_log("--- Traitement image $index ---");
            
            if ($uploadedFiles['error'][$index] !== UPLOAD_ERR_OK) {
                $errorMsg = "Erreur upload image " . ($index + 1) . ": " . $this->getUploadError($uploadedFiles['error'][$index]);
                error_log("❌ $errorMsg");
                $errors[] = $errorMsg;
                continue;
            }
            
            try {
                // Validation
                $this->validateFile($tmpName, $uploadedFiles['size'][$index]);
                
                $imageId = uniqid('img_');
                $originalName = $uploadedFiles['name'][$index];
                $altText = $altTexts[$index] ?? pathinfo($originalName, PATHINFO_FILENAME);
                
                error_log("Image ID: $imageId");
                error_log("Original name: $originalName");
                
                $originalPath = $productDir . $imageId . '_original.jpg';
                error_log("Original path: $originalPath");
                
                // Déplacer le fichier uploadé
                if (!move_uploaded_file($tmpName, $originalPath)) {
                    throw new Exception("Impossible de déplacer le fichier uploadé");
                }
                error_log("✅ Fichier déplacé avec succès");
                
                // Vérifier que le fichier existe
                if (!file_exists($originalPath)) {
                    throw new Exception("Fichier original non créé");
                }
                error_log("✅ Fichier original vérifié - Taille: " . filesize($originalPath));
                
                // Générer les versions
                $versions = $this->generateImageVersions($originalPath, $qualityDir, $imageId);
                error_log("✅ Versions générées: " . count($versions));
                
                // Sauvegarde BDD
                $displayOrder = count($uploadedImages) + 1;
                $isPrimary = $displayOrder === 1;
                
                $this->saveImageToDatabase($productId, $imageId, $originalName, $altText, 
                                        $displayOrder, $isPrimary, $originalPath, $versions);
                
                $uploadedImages[] = [
                    'image_id' => $imageId,
                    'versions' => $versions,
                    'is_primary' => $isPrimary,
                    'display_order' => $displayOrder
                ];
                
                error_log("✅ Image $index sauvegardée avec succès");
                
            } catch (Exception $e) {
                $errorMsg = "Image " . ($index + 1) . ": " . $e->getMessage();
                error_log("❌ $errorMsg");
                $errors[] = $errorMsg;
                $this->cleanupFiles($productDir, $qualityDir, $imageId ?? null);
            }
        }
        
        error_log("=== FIN UPLOAD - Succès: " . count($uploadedImages) . " - Erreurs: " . count($errors) . " ===");
        
        return [
            'success' => count($uploadedImages) > 0,
            'uploaded_images' => $uploadedImages,
            'errors' => $errors
        ];
    }
    
    private function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (php.ini)',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (formulaire)',
            UPLOAD_ERR_PARTIAL => 'Upload partiel',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque',
            UPLOAD_ERR_EXTENSION => 'Extension PHP arrêtée'
        ];
        return $errors[$errorCode] ?? 'Erreur inconnue';
    }
    
    private function validateFile($tmpPath, $fileSize) {
        error_log("Validation fichier - Taille: $fileSize - Path: $tmpPath");
        $max_file_size = 10 * 1024 * 1024;
        
        if ($fileSize > $max_file_size) {
            throw new Exception("Fichier trop volumineux: " . round($fileSize/1024/1024, 2) . "MB (max: " . round(MAX_FILE_SIZE/1024/1024, 2) . "MB)");
        }
        
        if ($fileSize == 0) {
            throw new Exception("Fichier vide");
        }
        
        // Type MIME
        $mimeType = mime_content_type($tmpPath);
        error_log("MIME Type: $mimeType");
        
        // Types MIME autorisés
        $allowed_type = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
        if (!in_array($mimeType, $allowed_type)) {
            throw new Exception("Type de fichier non autorisé: $mimeType");
        }
        
        // Vérifier que c'est une image valide
        $imageInfo = @getimagesize($tmpPath);
        if (!$imageInfo) {
            throw new Exception("Fichier n'est pas une image valide");
        }
        
        error_log("✅ Fichier validé - Dimensions: " . $imageInfo[0] . "x" . $imageInfo[1]);
    }
    
    private function generateImageVersions($originalPath, $qualityDir, $imageId) {
        global $IMAGE_QUALITIES;
        $versions = [];
        
        foreach ($IMAGE_QUALITIES as $qualityType => $settings) {
            $outputPath = $qualityDir . $imageId . '_' . $qualityType . '.jpg';
            error_log("Génération version $qualityType -> $outputPath");
            
            try {
                $result = ImageProcessor::createOptimizedVersion(
                    $originalPath,
                    $outputPath,
                    $settings['width'],
                    $settings['height'],
                    $settings['quality']
                );
                
                $versions[$qualityType] = [
                    'img' => $imageId . '_' . $qualityType . '.jpg',
                    'path' => str_replace(dirname(__DIR__), '', $outputPath),
                    'width' => $result['width'],
                    'height' => $result['height'],
                    'file_size' => $result['file_size']
                ];
                
                error_log("✅ Version $qualityType créée: {$result['width']}x{$result['height']}");
                
            } catch (Exception $e) {
                error_log("❌ Erreur version $qualityType: " . $e->getMessage());
            }
        }
        
        return $versions;
    }
    private function saveImageToDatabase($productId, $imageId, $originalName, $altText, 
                                    $displayOrder, $isPrimary, $originalPath, $versions) {
        global $IMAGE_QUALITIES;
        error_log("Sauvegarde BDD - Product: $productId - Image: $imageId");
        
        $this->bdd->beginTransaction();
        
        try {
            // Image principale - RÉCUPÉREZ L'ID INSÉRÉ
            $stmt = $this->bdd->prepare("
                INSERT INTO image_articles 
                (article, unique_id, img, alt_text, display_order, is_primary, file_size, mime_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $fileSize = filesize($originalPath);
            $mimeType = mime_content_type($originalPath);
            
            $stmt->execute([
                $productId,
                $imageId, // Ceci est votre custom ID, pas l'ID auto-incrément
                $originalPath,
                $altText,
                $displayOrder,
                $isPrimary,
                $fileSize,
                $mimeType
            ]);
            
            // CORRECTION : Récupérez l'ID auto-incrémenté de product_images
            error_log("✅ Image principale sauvegardée en BDD - ID: $imageId");
            
            // Versions - UTILISEZ L'ID DE product_images
            foreach ($versions as $versionType => $versionData) {
                $stmt = $this->bdd->prepare("
                    INSERT INTO image_versions 
                    (image_id, version_type, file_path, width, height, file_size, quality) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $imageId, // Utilisez l'ID auto-incrémenté, pas le custom image_id
                    $versionType,
                    $versionData['img'],
                    $versionData['width'],
                    $versionData['height'],
                    $versionData['file_size'],
                    $IMAGE_QUALITIES[$versionType]['quality']
                ]);
                
                error_log("✅ Version $versionType sauvegardée en BDD");
            }
            
            $this->bdd->commit();
            error_log("✅ Transaction BDD commitée");
            
        } catch (Exception $e) {
            $this->bdd->rollBack();
            error_log("❌ Erreur BDD: " . $e->getMessage());
            throw new Exception("Erreur sauvegarde BDD: " . $e->getMessage());
        }
    }
    
    private function cleanupFiles($productDir, $qualityDir, $imageId) {
        if ($imageId) {
            error_log("Nettoyage fichiers pour: $imageId");
            
            // Fichier original
            $originalFile = $productDir . $imageId . '_original.jpg';
            if (file_exists($originalFile)) {
                unlink($originalFile);
                error_log("🗑️ Fichier original supprimé: $originalFile");
            }
            
            // Versions
            foreach (['lqip', 'mobile', 'tablet', 'desktop', 'hd'] as $version) {
                $versionFile = $qualityDir . $imageId . '_' . $version . '.jpg';
                if (file_exists($versionFile)) {
                    unlink($versionFile);
                    error_log("🗑️ Version $version supprimée: $versionFile");
                }
            }
        }
    }
}

// Point d'entrée pour l'upload via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'upload_product') {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        error_log("=== UPLOAD VIA AJAX ===");
        $productManager = new ProductManager();
        
        $productId = $productManager->createProduct([
            'name' => $_POST['product_name'],
            'description' => $_POST['product_description'] ?? '',
            'price' => $_POST['product_price'] ?? 0
        ]);
        
        if (!$productId) {
            throw new Exception('Erreur création produit');
        }
        
        error_log("✅ Produit créé avec ID: $productId");
        
        // Upload des images
        $uploader = new ImageUploader();
        $result = $uploader->uploadProductImages(
            $productId, 
            $_FILES['product_images'],
            $_POST['alt_texts'] ?? []
        );
        
        $result['product_id'] = $productId;
        
        error_log("=== RÉSULTAT UPLOAD ===");
        error_log(print_r($result, true));
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        error_log("❌ ERREUR GLOBALE: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>