<?php
// Base jetable locale uniquement ; aucun transport réseau Moko ni envoi SMTP.
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../service/MokoClient.php';
require __DIR__.'/../service/MokoPayoutService.php';
require __DIR__.'/../model/PayoutTransaction.php';
require __DIR__.'/../fonctions/admin_password_security.php';
require __DIR__.'/../fonctions/moko_recipient.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function ohnous_get_current_account() { return ['id'=>1, 'nom'=>'Admin test']; }
function ohnous_is_store_active($boutique) { return (int)$boutique['activer'] === 1; }
function ohnous_table_exists($table) { return true; }
function update_bdd($db, $table, $data, $where) {
    $stmt = $db->prepare('UPDATE '.$table.' SET '.implode(',', array_map(fn($key)=>$key.'=?', array_keys($data))).' WHERE '.$where);
    return $stmt->execute(array_values($data));
}
$dsn = getenv('MOKO_TEST_DSN') ?: 'mysql:host=127.0.0.1;charset=utf8mb4';
if (strpos($dsn, 'dbname=') !== false || !preg_match('/host=(127\.0\.0\.1|localhost)(;|$)/', $dsn)) throw new RuntimeException('Utilisez un serveur de test local sans dbname.');
$db = new PDO($dsn, getenv('MOKO_TEST_USER') ?: 'root', getenv('MOKO_TEST_PASSWORD') ?: '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES=>false]);
$database = 'ohnous_boutique_test_'.bin2hex(random_bytes(6));
$db->exec('CREATE DATABASE '.$database);
try {
    $db->exec('USE '.$database);
    $db->exec('CREATE TABLE boutiques (id INT PRIMARY KEY, nom TEXT, activer INT NOT NULL)');
    $db->exec("INSERT INTO boutiques VALUES (1,'Boutique A',1),(2,'Boutique B',1),(3,'Boutique C',0),(4,'Boutique D',1),(5,'Boutique E',1)");
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
    $recipients = []; $calls = 0; $recipientCalls = 0; $mode = 'ok';
    $client = new MokoClient($cfg, function ($method, $path, $raw) use (&$recipients, &$calls, &$recipientCalls, &$mode, $db) {
        $data = json_decode($raw, true);
        if (strpos($path, '/v1/recipients?') === 0) return ['http'=>200,'data'=>['items'=>array_values($recipients),'total'=>count($recipients)],'valid'=>true];
        if ($path === '/v1/recipients') {
            $recipientCalls++;
            check(!isset($data['recipient_id']), 'ID Moko généré localement');
            $id = 'rec_'.md5($data['merchant_recipient_id']);
            if (isset($recipients[$id])) return ['http'=>409,'data'=>['detail'=>['code'=>'recipient_already_exists']],'valid'=>true];
            $recipients[$id] = $data + ['recipient_id'=>$id, 'status'=>$mode === 'pending' ? 'PENDING_KYC' : 'ACTIVE'];
            if ($mode === 'timeout') return ['http'=>0,'data'=>[],'valid'=>false];
            return ['http'=>201, 'data'=>$recipients[$id], 'valid'=>true];
        }
        if (strpos($path, '/v1/recipients/') === 0) return isset($recipients[basename($path)]) ? ['http'=>200, 'data'=>$recipients[basename($path)], 'valid'=>true] : ['http'=>404,'data'=>['detail'=>['code'=>'recipient_not_found']],'valid'=>true];
        $calls++;
        check((int)$db->query('SELECT COUNT(*) FROM boutique_payout_links')->fetchColumn() === $calls, 'Rattachement absent avant API');
        return ['http'=>202, 'valid'=>true, 'data'=>['payout_id'=>'PAY-TEST-'.$calls, 'recipient_id'=>$data['recipient_id'], 'amount'=>$data['amount'], 'currency'=>$data['currency'], 'status'=>'COMPLETED']];
    });
    $service = new MokoPayoutService($db, $client, $cfg);
    $model = new PayoutTransaction($db);
    $input = ['boutique_id'=>1, 'reference'=>'boutique-a-001', 'merchant_recipient_id'=>'boutique_1', 'beneficiary'=>'Boutique A', 'phone_number'=>'+243812345678', 'operator'=>'mpesa', 'amount'=>'100', 'currency'=>'CDF', 'reason'=>'Test'];
    try { $service->initiatePayout($input); throw new LogicException('Payout sans bénéficiaire enregistré'); } catch (InvalidArgumentException $expected) {}
    check($calls === 0 && $recipientCalls === 0, 'Appel avant enregistrement explicite');
    $created = $service->registerBoutiqueRecipient(1, array_merge($input, ['merchant_recipient_id'=>'faux','existing_recipient_id'=>'faux']));
    check($created['registered'] && $created['profile']['merchant_recipient_id'] === 'boutique_1', 'Identifiant interne non généré');
    check($calls === 0, 'Enregistrement ayant déclenché un paiement');
    $service->registerBoutiqueRecipient(1);
    check($recipientCalls === 1, 'Bénéficiaire dupliqué');
    $service->initiatePayout($input);
    $service->initiatePayout($input);
    check($calls === 1, 'Double versement');
    try { $service->initiatePayout(array_merge($input, ['boutique_id'=>2])); throw new LogicException('Référence déplacée'); } catch (InvalidArgumentException $expected) {}
    $service->initiatePayout(array_merge($input, ['reference'=>'boutique-a-002', 'merchant_recipient_id'=>'boutique_1_orange', 'operator'=>'orange']));
    $second = array_merge($input, ['reference'=>'boutique-b-001', 'boutique_id'=>2, 'merchant_recipient_id'=>'boutique_2', 'beneficiary'=>'Boutique B', 'phone_number'=>'+243912345678', 'currency'=>'USD']);
    $service->registerBoutiqueRecipient(2, $second);
    $service->initiatePayout(['boutique_id'=>2,'reference'=>$second['reference'],'amount'=>'100','currency'=>'USD','reason'=>'Test']);
    $context = $model->boutiqueContext(1);
    check($context['profile']['operator'] === 'mpesa', 'Profil initial écrasé');
    check(count($context['phones']) === 1 && $context['phones'][0]['operator'] === 'mpesa', 'Coordonnées navigateur ayant remplacé le profil');
    check(count($model->boutiqueContext(2)['phones']) === 1, 'Suggestions inter-boutiques');
    check(count($model->search(['boutique_id'=>1], null)) === 2, 'Rapport boutique incorrect');
    check(count($model->search(['boutique_id'=>1, 'operator'=>'mpesa'], null)) === 2, 'Filtre rapport incorrect');
    check((int)$model->statistics(2)['successful'] === 1, 'Statistiques non isolées');
    check($model->amountsByCurrency(1)[0]['paid'] === '200.00', 'Montant boutique incorrect');
    check(count($model->amountsByCurrency()) === 2, 'Devises mélangées');
    $before = $recipientCalls;
    $draft = $service->registerBoutiqueRecipient(3, $input);
    check(!$draft['registered'] && $recipientCalls === $before, 'Boutique inactive transmise à Moko');
    $db->exec('UPDATE boutiques SET activer=1 WHERE id=3');
    ohnous_register_recipient_on_activation($db, 3, $service);
    check(!empty($model->recipientProfile(3)['existing_recipient_id']), 'Coordonnées non réutilisées à l’activation');
    $before = $recipientCalls;
    ohnous_register_recipient_on_activation($db, 4, $service);
    check($recipientCalls === $before, 'Activation sans coordonnées envoyée à Moko');
    try { $service->registerBoutiqueRecipient(4, array_merge($input, ['phone_number'=>'+33123456789'])); throw new LogicException('Numéro étranger accepté'); } catch (InvalidArgumentException $expected) {}
    check($recipientCalls === $before, 'Numéro invalide transmis à Moko');
    $mode = 'pending';
    check($service->registerBoutiqueRecipient(4, $input)['registered'], 'PENDING_KYC non mémorisé');
    try { $service->initiatePayout(array_merge($input, ['boutique_id'=>4,'reference'=>'pending-kyc'])); throw new LogicException('PENDING_KYC payé'); } catch (RuntimeException $expected) { check(strpos($expected->getMessage(), 'non actif') !== false, 'Erreur KYC incorrecte'); }
    $recipients['rec_'.md5('boutique_4')]['status'] = 'ACTIVE';
    check($service->registerBoutiqueRecipient(4)['profile']['recipient_status'] === 'ACTIVE', 'Statut Moko non actualisé');
    $mode = 'timeout';
    try { $service->registerBoutiqueRecipient(5, $input); throw new LogicException('Timeout accepté'); } catch (RuntimeException $expected) { check(strpos($expected->getMessage(), 'Enregistrement') !== false, 'Erreur timeout incorrecte'); }
    $mode = 'ok';
    $recovered = $service->registerBoutiqueRecipient(5);
    check($recovered['registered'], 'Bénéficiaire non récupéré après timeout et 409');
    $db->exec("UPDATE moko_recipients SET recipient_id='rec_introuvable' WHERE merchant_recipient_id='boutique_1'");
    check($service->registerBoutiqueRecipient(1)['profile']['existing_recipient_id'] === $created['profile']['existing_recipient_id'], 'Ancien ID introuvable non récupéré');
    check($calls === 3, 'Enregistrement ou validation ayant déclenché un versement');
    echo "Bénéficiaires : création séparée, identifiants serveur, doublon, brouillon avant activation, KYC, timeout, récupération 409/404 et coordonnées autoritaires : OK.\n";
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
