<?php
require_once '../model/bdd.php';

class ImageProcessor {
    public static function createOptimizedVersion($sourcePath, $destPath, $maxWidth, $maxHeight = null, $quality = 75) {
        if (!file_exists($sourcePath)) {
            throw new Exception("Fichier source non trouvé: $sourcePath");
        }
        
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new Exception("Impossible de lire l'image: $sourcePath");
        }
        
        list($origWidth, $origHeight, $type) = $imageInfo;
        
        // CORRECTION: Calcul automatique de la hauteur si null
        if ($maxHeight === null) {
            $maxHeight = (int)($maxWidth * ($origHeight / $origWidth));
        }
        
        error_log("📐 Redimensionnement: {$origWidth}x{$origHeight} -> {$maxWidth}x{$maxHeight}");
        
        // Créer l'image selon le type
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($sourcePath);
                imagealphablending($source, false);
                imagesavealpha($source, true);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new Exception("Format non supporté: $type");
        }
        
        if (!$source) {
            throw new Exception("Échec création image depuis source");
        }
        
        // Calcul des nouvelles dimensions avec maintien du ratio
        $ratio = $origWidth / $origHeight;
        $targetRatio = $maxWidth / $maxHeight;
        
        if ($targetRatio > $ratio) {
            $newWidth = (int)($maxHeight * $ratio);
            $newHeight = $maxHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = (int)($maxWidth / $ratio);
        }
        
        error_log("🎯 Dimensions finales: {$newWidth}x{$newHeight}");
        
        // Créer la nouvelle image
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        
        // Gestion transparence PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Redimensionnement qualité
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Flou pour très basses qualités
        if ($quality < 30) {
            imagefilter($thumb, IMG_FILTER_GAUSSIAN_BLUR);
        }
        
        // Sauvegarde - CORRECTION: toujours sauvegarder en JPEG
        $result = imagejpeg($thumb, $destPath, $quality);
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        if (!$result) {
            throw new Exception("Échec sauvegarde image: $destPath");
        }
        
        // Vérifier que le fichier a été créé
        if (!file_exists($destPath)) {
            throw new Exception("Fichier de destination non créé: $destPath");
        }
        
        $fileSize = filesize($destPath);
        error_log("✅ Version créée: $destPath - {$newWidth}x{$newHeight} - {$fileSize} bytes");
        
        return [
            'width' => $newWidth,
            'height' => $newHeight,
            'file_size' => $fileSize
        ];
    }
    
    public static function generateLQIP($sourcePath, $width = 20) {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) return null;
        
        list($origWidth, $origHeight, $type) = $imageInfo;
        $height = (int)($width * ($origHeight / $origWidth));
        
        $lqip = imagecreatetruecolor($width, $height);
        $source = imagecreatefromjpeg($sourcePath);
        
        imagecopyresampled($lqip, $source, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
        
        ob_start();
        imagejpeg($lqip, null, 10);
        $lqipData = ob_get_clean();
        
        imagedestroy($source);
        imagedestroy($lqip);
        
        return 'data:image/jpeg;base64,' . base64_encode($lqipData);
    }
}
?>