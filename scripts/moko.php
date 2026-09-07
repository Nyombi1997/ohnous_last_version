<?php
// CLI only. No HTTP access, no credentials in command arguments.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
define('ROOT', dirname(__DIR__).'/');
define('MODEL', ROOT.'model/');
define('CONFIG', ROOT.'config/');
define('FONCTION', ROOT.'fonctions/');
require MODEL.'bdd.php';
require MODEL.'select.php';
require FONCTION.'fonctions.php';
require MODEL.'PayoutTransaction.php';
require ROOT.'service/MokoClient.php';
require ROOT.'service/MokoPayoutService.php';
try {
    if (!isset($bdd) || !$bdd instanceof PDO) throw new RuntimeException('Connexion PDO indisponible.');
    $service = new MokoPayoutService($bdd);
    switch ($argv[1] ?? '') {
        case 'diagnostic': $result = $service->diagnostic(); break;
        case 'reconcile': $result = $service->reconcile(); break;
        case 'recover':
            if (empty($argv[2]) || empty($argv[3])) throw new InvalidArgumentException('Usage : php scripts/moko.php recover reference PAY-...');
            $result = $service->recover($argv[2],$argv[3]); break;
        default: throw new InvalidArgumentException('Usage : php scripts/moko.php diagnostic|reconcile|recover');
    }
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $e) { fwrite(STDERR, $e instanceof PDOException ? "Erreur base de données.\n" : $e->getMessage().PHP_EOL); exit(1); }
