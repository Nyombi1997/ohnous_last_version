<?php
    $host = 'localhost'; // L'adresse de votre serveur de base de données
    $dbname = 'u946733711_ohnous'; // Le nom de votre base de données
    //$dbname = 'ekuke'; // Le nom de votre base de données
    $username = 'u946733711_ohnous'; // Le nom d'utilisateur de votre base de données
    //$username = 'root'; // Le nom d'utilisateur de votre base de données
    $password = 'Kinshasa@1997'; // Le mot de passe de votre base de données
    //$password = ''; // Le mot de passe de votre base de données

    try {
        $bdd = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $GLOBALS['bdd'] = $bdd;
    } catch (PDOException $e) {
        echo "Échec de la connexion : " . $e->getMessage();
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    class Database {
        private static $pdo = null;
        
        public static function getConnection() {
            if (self::$pdo === null) {
                try {
                    $dsn = "mysql:host=localhost;dbname=u946733711_ohnous;charset=utf8mb4";
                    self::$pdo = new PDO($dsn, 'u946733711_ohnous', 'Kinshasa@1997');
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    error_log("Database connection failed: " . $e->getMessage());
                    die("Erreur de connexion à la base de données");
                }
            }
            return self::$pdo;
        }
    }
            
    // Qualités des images AVEC hauteurs définies
    $IMAGE_QUALITIES = [
        'lqip' => ['width' => 20, 'height' => 15, 'quality' => 10],
        'mobile' => ['width' => 400, 'height' => 300, 'quality' => 60],
        'tablet' => ['width' => 800, 'height' => 600, 'quality' => 75],
        'desktop' => ['width' => 1200, 'height' => 900, 'quality' => 85],
        'hd' => ['width' => 1920, 'height' => 1440, 'quality' => 92]
    ];   
?>