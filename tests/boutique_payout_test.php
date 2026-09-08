<?php
// Base jetable locale uniquement ; aucun transport réseau Moko ni envoi SMTP.
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../service/MokoClient.php';
require __DIR__.'/../service/MokoPayoutService.php';
require __DIR__.'/../model/PayoutTransaction.php';
require __DIR__.'/../fonctions/admin_password_security.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function ohnous_get_current_account() { return ['id'=>1, 'nom'=>'Admin test']; }
function ohnous_table_exists($table) { return true; }
function update_bdd($db, $table, $data, $where) {
    $stmt = $db->prepare('UPDATE '.$table.' SET '.implode(',', array_map(fn($key)=>$key.'=?', array_keys($data))).' WHERE '.$where);
    return $stmt->execute(array_values($data));
}
$db = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', getenv('MOKO_TEST_USER') ?: 'root', getenv('MOKO_TEST_PASSWORD') ?: '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
$database = 'ohnous_boutique_test_'.bin2hex(random_bytes(6));
$db->exec('CREATE DATABASE '.$database);
try {
    $db->exec('USE '.$database);
    $db->exec('CREATE TABLE boutiques (id INT PRIMARY KEY, nom TEXT)');
    $db->exec("INSERT INTO boutiques VALUES (1,'Boutique A'),(2,'Boutique B')");
    $db->exec('CREATE TABLE payout_transactions (id INT AUTO_INCREMENT PRIMARY KEY, reference VARCHAR(120), beneficiary VARCHAR(190), phone_number VARCHAR(32), operator VARCHAR(30), amount DECIMAL(15,2), currency VARCHAR(3), reason VARCHAR(255), status VARCHAR(40), status_description TEXT, request_payload LONGTEXT, response_payload LONGTEXT, freshpay_reference VARCHAR(190), transaction_id VARCHAR(190), created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
    $db->exec('CREATE TABLE payout_status_history (id INT AUTO_INCREMENT PRIMARY KEY, payout_id INT, status VARCHAR(40), description TEXT, source VARCHAR(40), payload LONGTEXT, created_at DATETIME)');
    $db->exec('CREATE TABLE payout_audit_log (id INT AUTO_INCREMENT PRIMARY KEY, payout_id INT, admin_id INT, admin_name VARCHAR(190), action VARCHAR(80), amount DECIMAL(15,2), currency VARCHAR(3), phone_number VARCHAR(32), operator VARCHAR(30), ip_address VARCHAR(64), user_agent VARCHAR(500))');
    $moko = file_get_contents(__DIR__.'/../data base/20260907_001_moko_payout.sql');
    $moko = str_replace(['ADD COLUMN IF NOT EXISTS', 'CREATE UNIQUE INDEX IF NOT EXISTS'], ['ADD COLUMN', 'CREATE UNIQUE INDEX'], $moko);
    $migration = file_get_contents(__DIR__.'/../data base/20260908_001_admin_boutique_payout.sql');
    foreach ([$moko, $migration, $migration] as $script) {
        foreach (explode(';', preg_replace('/^--.*$/m', '', $script)) as $sql) if (trim($sql) !== '') $db->exec($sql);
    }
    $cfg = ['enabled'=>true, 'base_url'=>'https://example.test', 'public_key'=>'test', 'secret_key'=>'test', 'webhook_secret'=>'test', 'callback_url'=>''];
    $recipients = []; $calls = 0;
    $client = new MokoClient($cfg, function ($method, $path, $raw) use (&$recipients, &$calls, $db) {
        $data = json_decode($raw, true);
        if ($path === '/v1/recipients') {
            $id = 'rec_'.md5($data['merchant_recipient_id']);
            $recipients[$id] = $data + ['recipient_id'=>$id, 'status'=>'ACTIVE'];
            return ['http'=>201, 'data'=>$recipients[$id], 'valid'=>true];
        }
        if (strpos($path, '/v1/recipients/') === 0) return ['http'=>200, 'data'=>$recipients[basename($path)], 'valid'=>true];
        $calls++;
        check((int)$db->query('SELECT COUNT(*) FROM boutique_payout_links')->fetchColumn() === $calls, 'Rattachement absent avant API');
        return ['http'=>202, 'valid'=>true, 'data'=>['payout_id'=>'PAY-TEST-'.$calls, 'recipient_id'=>$data['recipient_id'], 'amount'=>$data['amount'], 'currency'=>$data['currency'], 'status'=>'COMPLETED']];
    });
    $service = new MokoPayoutService($db, $client, $cfg);
    $model = new PayoutTransaction($db);
    $input = ['boutique_id'=>1, 'reference'=>'boutique-a-001', 'merchant_recipient_id'=>'boutique_1', 'beneficiary'=>'Boutique A', 'phone_number'=>'+243812345678', 'operator'=>'mpesa', 'amount'=>'100', 'currency'=>'CDF', 'reason'=>'Test'];
    $service->initiatePayout($input);
    $service->initiatePayout($input);
    check($calls === 1, 'Double versement');
    try { $service->initiatePayout(array_merge($input, ['boutique_id'=>2])); throw new LogicException('Référence déplacée'); } catch (InvalidArgumentException $expected) {}
    $service->initiatePayout(array_merge($input, ['reference'=>'boutique-a-002', 'merchant_recipient_id'=>'boutique_1_orange', 'operator'=>'orange']));
    $service->initiatePayout(array_merge($input, ['reference'=>'boutique-b-001', 'boutique_id'=>2, 'merchant_recipient_id'=>'boutique_2', 'beneficiary'=>'Boutique B', 'phone_number'=>'+243912345678', 'currency'=>'USD']));
    $context = $model->boutiqueContext(1);
    check($context['profile']['operator'] === 'mpesa', 'Profil initial écrasé');
    check(count($context['phones']) === 1 && $context['phones'][0]['operator'] === 'orange', 'Dernier opérateur incorrect');
    check(count($model->boutiqueContext(2)['phones']) === 1, 'Suggestions inter-boutiques');
    check(count($model->search(['boutique_id'=>1], null)) === 2, 'Rapport boutique incorrect');
    check(count($model->search(['boutique_id'=>1, 'operator'=>'orange'], null)) === 1, 'Filtre rapport incorrect');
    check((int)$model->statistics(2)['successful'] === 1, 'Statistiques non isolées');
    check($model->amountsByCurrency(1)[0]['paid'] === '200.00', 'Montant boutique incorrect');
    check(count($model->amountsByCurrency()) === 2, 'Devises mélangées');
    $db->exec('CREATE TABLE admins (id INT PRIMARY KEY, mdp VARCHAR(255))');
    $db->exec("INSERT INTO admins VALUES (1,'ancien'),(2,'autre')");
    $code = 'otp:'.hash('sha256', 'TEST-CODE');
    $db->prepare('INSERT INTO admin_password_resets (admin_id,token,expire_at) VALUES (1,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE))')->execute([$code]);
    check(ohnous_complete_admin_password_reset($db, $code, 'nouveau-test') === 1, 'Mauvais administrateur');
    check(password_verify('nouveau-test', $db->query('SELECT mdp FROM admins WHERE id=1')->fetchColumn()), 'Mot de passe non modifié');
    check($db->query('SELECT mdp FROM admins WHERE id=2')->fetchColumn() === 'autre', 'Autre administrateur modifié');
    try { ohnous_complete_admin_password_reset($db, $code, 'reutilisation'); throw new LogicException('Code réutilisé'); } catch (InvalidArgumentException $expected) {}
    $db->exec("INSERT INTO admin_password_resets (admin_id,token,expire_at) VALUES (1,'expire',DATE_SUB(NOW(), INTERVAL 1 MINUTE))");
    try { ohnous_complete_admin_password_reset($db, 'expire', 'expiration'); throw new LogicException('Code expiré accepté'); } catch (InvalidArgumentException $expected) {}
    echo "Réinitialisation : compte ciblé, mot de passe haché, expiration et usage unique : OK.\n";
    echo "Migration rejouée, profils stables, suggestions, rapports, devises et idempotence : OK.\n";
} finally {
    if (preg_match('/^ohnous_boutique_test_[a-f0-9]{12}$/D', $database)) $db->exec('DROP DATABASE '.$database);
}
