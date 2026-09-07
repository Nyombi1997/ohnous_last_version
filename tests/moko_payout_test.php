<?php
// Self-contained integration suite: disposable MariaDB database, fake Moko transport.
// MOKO_TEST_DSN must target a local test server without a dbname component.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require __DIR__.'/../service/MokoClient.php';
require __DIR__.'/../service/MokoPayoutService.php';
require __DIR__.'/../model/PayoutTransaction.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function ohnous_get_current_account() { return ['id'=>1,'nom'=>'Test admin']; }
function ohnous_table_exists($table) { return true; }
function update_bdd($db,$table,$data,$where) {
    $stmt=$db->prepare('UPDATE '.$table.' SET '.implode(',',array_map(function($k){return $k.'=?';},array_keys($data))).' WHERE '.$where);
    return $stmt->execute(array_values($data));
}
$body='{"amount":100,"description":"Été"}';
$canonical="POST\n/v1/payouts\n".hash('sha256',$body)."\n1717420800123\n00000000-0000-4000-8000-000000000000";
check(MokoClient::signature('test-secret','POST','/v1/payouts',$body,'1717420800123','00000000-0000-4000-8000-000000000000')===hash_hmac('sha256',$canonical,'test-secret'),'Canonical signature mismatch');
check((bool)preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/',MokoClient::nonce()),'UUID v4');
check(!MokoPayoutService::acceptsTransition('COMPLETED','FAILED'),'Terminal protection');
check(!MokoPayoutService::acceptsTransition('WAITING_CALLBACK','RESERVED'),'Out of order protection');
check(!MokoClient::verifyWebhook('{}','t=1717420800123,v1='.str_repeat('0',64),'secret'),'Forged webhook');
$cfg=['enabled'=>true,'base_url'=>'https://example.test','public_key'=>'pk_test','secret_key'=>'test-secret','webhook_secret'=>'hook-test','callback_url'=>''];
$seen=[];
$signingClient=new MokoClient($cfg,function($method,$path,$raw,$headers)use(&$seen){$seen=[$method,$path,$raw,$headers];return ['http'=>200,'data'=>[],'valid'=>true];});
$signingClient->request('GET','/v1/recipients',null,['status'=>'ACTIVE','page'=>1]);
check($seen[1]==='/v1/recipients?page=1&status=ACTIVE' && $seen[2]==='','GET sorted query and empty body');
$dsn=getenv('MOKO_TEST_DSN');
if (!$dsn) { echo "Signature and transition tests passed. Set MOKO_TEST_DSN for database integration tests.\n"; exit(); }
if (strpos($dsn,'dbname=')!==false || !preg_match('/host=(127\.0\.0\.1|localhost)(;|$)/',$dsn)) throw new RuntimeException('Use a local DSN without dbname.');
$db=new PDO($dsn,getenv('MOKO_TEST_USER')?:'root',getenv('MOKO_TEST_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
$database='ohnous_moko_test_'.bin2hex(random_bytes(6));
$db->exec('CREATE DATABASE '.$database.' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
try {
    $db->exec('USE '.$database);
    $dump=file_get_contents(getenv('MOKO_TEST_DUMP')?:__DIR__.'/../u577654037_ohnous.sql');
    foreach (['payout_transactions','payout_status_history','payout_audit_log'] as $table) {
        check(preg_match('/CREATE TABLE `'.$table.'` \(.*?\) ENGINE=.*?;/s',$dump,$m)===1,'Missing dump table '.$table);
        $db->exec($m[0]);
        preg_match_all('/ALTER TABLE `'.$table.'`\s+.*?;/s',$dump,$alters);
        foreach($alters[0] as $sql)$db->exec($sql);
    }
    $migration=file_get_contents(__DIR__.'/../data base/20260907_001_moko_payout.sql');
    check($migration!==false && trim($migration)!=='','SQL migration missing');
    foreach ([$migration,$migration] as $pass) foreach(explode(';',preg_replace('/^--.*$/m','',$pass))as$sql)if(trim($sql)!=='')$db->exec($sql);
    $calls=0;$mode='ok';$payoutId='PAY-20260907120000-ABC123';$service=null;
    $recipient=['recipient_id'=>'rec_'.str_repeat('a',32),'status'=>'ACTIVE','merchant_recipient_id'=>'vendor_42','full_name'=>'Jean Test','phone'=>'+243812345678','operator'=>'mpesa'];
    $sentBodies=[];
    $client=new MokoClient($cfg,function($method,$path,$raw,$headers)use(&$calls,&$mode,&$service,&$sentBodies,$db,$payoutId,$recipient){
        if(strpos($path,'/v1/recipients')===0)return ['http'=>$method==='POST'?201:200,'data'=>$mode==='inactive'?array_merge($recipient,['status'=>'PENDING_KYC']):$recipient,'valid'=>true];
        $data=['payout_id'=>$payoutId,'recipient_id'=>$recipient['recipient_id'],'amount'=>'100','currency'=>'CDF','status'=>'RESERVED'];
        if($method==='POST') {
            $calls++;
            $payload=json_decode($raw,true);
            $stmt=$db->prepare('SELECT request_payload FROM payout_transactions WHERE reference=?');$stmt->execute([$payload['merchant_reference']]);
            check($stmt->fetchColumn()===$raw,'Payload not persisted before HTTP');
            if(isset($sentBodies[$payload['merchant_reference']]))check($sentBodies[$payload['merchant_reference']]===$raw,'Retry changed body');
            $sentBodies[$payload['merchant_reference']]=$raw;
            if($mode==='timeout')return ['http'=>0,'data'=>[],'valid'=>false];
            if($mode==='reject')return ['http'=>422,'data'=>['detail'=>['code'=>'insufficient_balance']],'valid'=>true];
            if($mode==='early') {
                $event=json_encode(['event'=>'payout.completed','data'=>array_merge($data,['status'=>'COMPLETED'])]);
                $sig='t=1717420800123,v1='.hash_hmac('sha256','1717420800123.'.$event,'hook-test');
                $service->handleWebhook($event,$sig);
            }
        }
        return ['http'=>$method==='POST'?202:200,'data'=>$data,'valid'=>true];
    });
    $service=new MokoPayoutService($db,$client,$cfg);
    $input=['reference'=>'order-123-vendor-42','merchant_recipient_id'=>'vendor_42','beneficiary'=>'Jean Test','phone_number'=>'+243812345678','operator'=>'mpesa','amount'=>'100','currency'=>'CDF','reason'=>'Reversement'];
    $mode='early';$first=$service->initiatePayout($input);
    check($first['status']==='completed','Early webhook must survive creation response');
    $service->initiatePayout(array_merge($input,['reference'=>'ORDER-123-VENDOR-42']));
    check($calls===1,'Duplicate payout submitted');
    try{$service->initiatePayout(array_merge($input,['amount'=>'101']));throw new RuntimeException('Conflict was accepted');}catch(InvalidArgumentException $expected){}
    $service->verifyPayoutStatus($input['reference']);
    check($db->query('SELECT status FROM payout_transactions')->fetchColumn()==='completed','GET regressed terminal status');
    $event=json_encode(['event'=>'payout.completed','data'=>['payout_id'=>$payoutId,'status'=>'COMPLETED']]);
    $sig='t=1717420800123,v1='.hash_hmac('sha256','1717420800123.'.$event,'hook-test');
    $service->handleWebhook($event,$sig);$service->handleWebhook($event,$sig);
    check((int)$db->query("SELECT COUNT(*) FROM payout_status_history WHERE status='completed'")->fetchColumn()===1,'Webhook duplicated history');
    try{$service->handleWebhook($event.' ',$sig);throw new RuntimeException('Tampered event accepted');}catch(UnexpectedValueException $expected){}
    $mode='timeout';$input['reference']='order-timeout';
    $res=$service->initiatePayout($input);check($res['status']==='send_unknown','Timeout marked definitive failure');
    $before=$calls;$service->initiatePayout($input);check($calls===$before,'Backoff ignored');
    for($i=0;$i<2;$i++){$db->exec("UPDATE payout_transactions SET next_attempt_at=NULL WHERE reference='order-timeout'");$service->initiatePayout($input);}
    check($db->query("SELECT status FROM payout_transactions WHERE reference='order-timeout'")->fetchColumn()==='manual_review','Attempts not bounded');
    $before=$calls;$service->initiatePayout($input);check($calls===$before,'Fourth attempt sent');
    $input['reference']='order-too-old';$service->initiatePayout($input);
    $db->exec("UPDATE payout_transactions SET first_attempt_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR),next_attempt_at=NULL WHERE reference='order-too-old'");
    $before=$calls;$res=$service->initiatePayout($input);check($calls===$before && $res['status']==='manual_review','Expired idempotency retried');
    $mode='reject';$input['reference']='order-rejected';$res=$service->initiatePayout($input);check($res['status']==='send_rejected','4xx not rejected');
    $before=$calls;$service->initiatePayout($input);check($calls===$before,'4xx automatically retried');
    try{$service->initiatePayout(array_merge($input,['reference'=>'bad-phone','phone_number'=>'+33123456789']));throw new RuntimeException('Foreign number accepted');}catch(InvalidArgumentException $expected){}
    $mode='inactive';$before=$calls;
    try{$service->initiatePayout(array_merge($input,['reference'=>'inactive-recipient']));throw new LogicException('Inactive recipient accepted');}catch(RuntimeException $expected){check(strpos($expected->getMessage(),'non actif')!==false,'Unexpected inactive error');}
    check($calls===$before,'Inactive recipient paid');
    $disabled=new MokoPayoutService($db,$client,array_merge($cfg,['enabled'=>false]));
    try{$disabled->initiatePayout(array_merge($input,['reference'=>'disabled-payout']));throw new LogicException('Disabled payout accepted');}catch(RuntimeException $expected){check(strpos($expected->getMessage(),'désactivés')!==false,'Unexpected disabled error');}
    check(count((new PayoutTransaction($db))->search(['q'=>'order']))===4,'Native prepared search failed');
    echo "PASS: signatures, UUID, sorted GET, migration twice on supplied payout schema, recipient creation, early/duplicate/forged webhooks, terminal ordering, duplicate/conflicting references, timeout, backoff, three-attempt limit, 24h cutoff, 4xx, phone validation, native SQL search. No real HTTP calls.\n";
} finally { $db->exec('DROP DATABASE '.$database); }
