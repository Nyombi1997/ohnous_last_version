<?php
require_once '../model/bdd.php';
require_once 'fonctions.php';

class ProductManager {
    private $bdd;
    
    public function __construct() {
        $this->bdd = Database::getConnection();
    }
    
    public function createProduct($data) {
        $slug = generateSlug($data['name']);
        $counter = 1;
        $originalSlug = $slug;
        
        // Gérer les slugs dupliqués
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $stmt = $this->bdd->prepare("
            INSERT INTO articles (nom, description, prix, slug, unique_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $unique_id = uniqid();
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $slug,
            $unique_id
        ]);
        
        // Récupérer l'ID via unique_id pour éviter toute confusion en cas d'inserts concurrents
        $select = $this->bdd->prepare("SELECT id FROM articles WHERE unique_id = ? LIMIT 1");
        $select->execute([$unique_id]);
        $row = $select->fetch(PDO::FETCH_ASSOC);

        // Si on trouve la ligne via unique_id on l'utilise, sinon fallback sur lastInsertId()
        if ($row && isset($row['id'])) {
            $stmt_ = ['id' => (int) $row['id']];
        } else {
            $stmt_ = ['id' => (int) $this->bdd->lastInsertId()];
        }
        
        return $stmt_['id'];
    }
    
    public function slugExists($slug) {
        $stmt = $this->bdd->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() !== false;
    }
    
    public function getProduct($productId) {
        error_log("🔍 getProduct called for ID: " . $productId);
        
        // D'abord récupérer les infos de base du produit
        $stmt = $this->bdd->prepare("
            SELECT * FROM articles 
            WHERE id = ? AND is_active = TRUE
        ");
        
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            error_log("❌ Produit non trouvé: " . $productId);
            return null;
        }
        
        error_log("✅ Produit trouvé: " . $product['name']);
        
        // Ensuite récupérer les images avec une requête séparée
        $product['images'] = $this->getProductImages($productId);
        error_log("📸 Images chargées: " . count($product['images']));
        
        return $product;
    }

    public function getLatestarticles($limit = 12) {
        // Version alternative sans LEFT JOIN complexe
        $stmt = $this->bdd->prepare("
            SELECT p.* 
            FROM articles p
            WHERE p.is_active = TRUE
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        
        $stmt->bindParam(':limit', $limit, pdo::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        // Récupérer les images séparément pour chaque produit
        foreach ($articles as &$product) {
            $product['images'] = $this->getProductImages($product['id']);
        }
        
        return $articles;
    }

    private function getProductImages($productId) {
        $stmt = $this->bdd->prepare("
            SELECT pi.image_id, pi.alt_text, pi.is_primary,
                iv.version_type, iv.file_path, iv.width, iv.height
            FROM product_images pi
            LEFT JOIN image_versions iv ON pi.image_id = iv.image_id
            WHERE pi.product_id = ? AND pi.is_active = TRUE
            ORDER BY pi.display_order ASC, pi.is_primary DESC
        ");
        
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll();
        
        $images = [];
        foreach ($rows as $row) {
            if (!isset($images[$row['image_id']])) {
                $images[$row['image_id']] = [
                    'image_id' => $row['image_id'],
                    'alt_text' => $row['alt_text'],
                    'is_primary' => $row['is_primary'],
                    'versions' => []
                ];
            }
            
            if ($row['version_type']) {
                $images[$row['image_id']]['versions'][$row['version_type']] = [
                    'path' => $row['file_path'],
                    'width' => $row['width'],
                    'height' => $row['height']
                ];
            }
        }
        
        return array_values($images);
    }

    public function updateProduct($productId, $data) {
        $stmt = $this->bdd->prepare("
            UPDATE articles 
            SET name = ?, description = ?, price = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $productId
        ]);
    }
}
?>