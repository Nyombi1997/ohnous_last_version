<?php
header('Content-Type: application/json; charset=utf-8');

// Debug
error_log("=== DEBUT UPLOAD ===");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_FILES['croppedImage']) || $_FILES['croppedImage']['error'] !== UPLOAD_ERR_OK) {
    error_log("Erreur fichier: " . ($_FILES['croppedImage']['error'] ?? 'Fichier non défini'));
    echo json_encode(['error' => 'Aucun fichier envoyé ou erreur d\'upload']);
    exit;
}

$lqip = $_POST['lqip'] ?? null;
error_log("LQIP reçu: " . substr($lqip ?? 'null', 0, 50) . "...");

// Configuration des dossiers
$uploadDir = __DIR__ . "/../images/uploads/articles/";
$qualityDir = __DIR__ . "/../images/uploads/articles/quality/";

// Créer les dossiers
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
if (!file_exists($qualityDir)) mkdir($qualityDir, 0777, true);

// Générer un ID unique pour toutes les versions
$imageId = uniqid('img_');
$originalPath = $uploadDir . $imageId . '_original.jpg';

error_log("Début move_uploaded_file...");
if (move_uploaded_file($_FILES['croppedImage']['tmp_name'], $originalPath)) {
    error_log("Fichier uploadé avec succès: " . $originalPath);
    
    // Générer différentes qualités
    $qualities = [
        'lqip' => [20, 15, 10],   // Très basse qualité pour le placeholder
        'mobile' => [400, 300, 60], // Pour connexion lente
        'tablet' => [800, 600, 75], // Pour connexion moyenne  
        'desktop' => [1200, 900, 85], // Pour connexion rapide
        'hd' => [1920, 1440, 92] // Qualité maximale
    ];
    
    $generatedVersions = [];
    
    foreach ($qualities as $quality => $settings) {
        $width = $settings[0];
        $height = $settings[1];
        $compression = $settings[2];
        
        $qualityPath = $qualityDir . $imageId . '_' . $quality . '.jpg';
        
        if (createOptimizedVersion($originalPath, $qualityPath, $width, $height, $compression)) {
            $generatedVersions[$quality] = '/images/uploads/articles/quality/' . $imageId . '_' . $quality . '.jpg';
            error_log("Version $quality créée: " . $qualityPath);
        } else {
            error_log("Échec création version $quality");
        }
    }
    
    // URLs simulées pour différents serveurs CDN
    $cdnServers = [
        'https://cdn1.votresite.com',
        'https://cdn2.votresite.com', 
        'https://cdn3.votresite.com'
    ];
    
    // Préparer la réponse structurée
    $response = [
        'success' => true,
        'image_id' => $imageId,
        'versions' => $generatedVersions,
        'cdn_servers' => $cdnServers,
        'lqip_data' => $lqip,
        'message' => 'Image optimisée avec ' . count($generatedVersions) . ' versions'
    ];
    
    error_log("Réponse préparée: " . json_encode($response));
    echo json_encode($response);
    
} else {
    $error = "Échec move_uploaded_file de " . $_FILES['croppedImage']['tmp_name'] . " vers " . $originalPath;
    error_log($error);
    echo json_encode(['error' => $error]);
}

function createOptimizedVersion($sourcePath, $destPath, $maxWidth, $maxHeight, $quality = 75) {
    // Vérifier que le fichier source existe
    if (!file_exists($sourcePath)) {
        error_log("Fichier source non trouvé: $sourcePath");
        return false;
    }
    
    $imageInfo = @getimagesize($sourcePath);
    if (!$imageInfo) {
        error_log("Impossible de lire l'image: $sourcePath");
        return false;
    }
    
    list($origWidth, $origHeight, $type) = $imageInfo;
    
    // Vérifier que c'est une JPEG
    if ($type !== IMAGETYPE_JPEG) {
        error_log("Format non supporté: $type");
        return false;
    }
    
    // Calculer les nouvelles dimensions en conservant le ratio
    $ratio = $origWidth / $origHeight;
    if ($maxWidth / $maxHeight > $ratio) {
        $newWidth = $maxHeight * $ratio;
        $newHeight = $maxHeight;
    } else {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
    }
    
    $newWidth = (int)round($newWidth);
    $newHeight = (int)round($newHeight);
    
    error_log("Redimensionnement: {$origWidth}x{$origHeight} -> {$newWidth}x{$newHeight}");
    
    $source = @imagecreatefromjpeg($sourcePath);
    if (!$source) {
        error_log("Échec imagecreatefromjpeg");
        return false;
    }
    
    $thumb = @imagecreatetruecolor($newWidth, $newHeight);
    if (!$thumb) {
        error_log("Échec imagecreatetruecolor");
        imagedestroy($source);
        return false;
    }
    
    // Améliorer la qualité du redimensionnement
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    
    // Application d'un léger flou pour les très basses qualités
    if ($quality < 30) {
        for ($i = 0; $i < 2; $i++) {
            imagefilter($thumb, IMG_FILTER_GAUSSIAN_BLUR);
        }
    }
    
    $result = @imagejpeg($thumb, $destPath, $quality);
    
    imagedestroy($source);
    imagedestroy($thumb);
    
    if ($result) {
        error_log("Version sauvegardée: $destPath (qualité: $quality%)");
    } else {
        error_log("Échec imagejpeg pour: $destPath");
    }
    
    return $result;
}
?>