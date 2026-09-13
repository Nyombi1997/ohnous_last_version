<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../service/MokoClient.php';
require __DIR__.'/../service/MokoPayoutService.php';
function check($value, $message) { if (!$value) throw new RuntimeException($message); }
$config = ['base_url'=>'https://example.test','public_key'=>'pk_test_example','secret_key'=>'sk_test_example','webhook_secret'=>'webhook-test-example'];
$calls = []; $status = 200;
$client = new MokoClient($config, function ($method, $path, $raw, $headers) use (&$calls, &$status, $config) {
    $mapped = [];
    foreach ($headers as $header) { [$key,$value] = explode(': ', $header, 2); $mapped[$key] = $value; }
    $canonical = implode("\n", [$method, $path, hash('sha256', $raw), $mapped['X-Moko-Timestamp'], $mapped['X-Moko-Nonce']]);
    check(hash_equals(hash_hmac('sha256', $canonical, $config['secret_key']), $mapped['X-Moko-Signature']), 'HMAC différent des octets HTTP');
    check(abs((float)$mapped['X-Moko-Timestamp'] - microtime(true) * 1000) < 1000, 'Timestamp non UTC millisecondes');
    check(!isset($calls[$mapped['X-Moko-Nonce']]), 'Nonce réutilisé');
    $calls[$mapped['X-Moko-Nonce']] = [$method,$path,$raw];
    return ['http'=>$status,'data'=>['detail'=>['code'=>'test_error','message'=>'Message original'],'secret_key'=>$config['secret_key'],'nested'=>['webhook_secret'=>$config['webhook_secret']]],'valid'=>true];
});
$client->request('get', '/v1/recipients', null, ['status'=>'ACTIVE','page_size'=>100,'page'=>2]);
check(array_values($calls)[0] === ['GET','/v1/recipients?page=2&page_size=100&status=ACTIVE',''], 'Query triée / body GET');
$client->request('POST', '/v1/recipients', ['full_name'=>'Été / Kasaï','phone'=>'+243812345678']);
check(array_values($calls)[1][2] === '{"full_name":"Été / Kasaï","phone":"+243812345678"}', 'Encodage JSON UTF-8 altéré');
$trace = json_encode($client->lastExchange());
foreach (['public_key','secret_key','webhook_secret'] as $key) check(strpos($trace, $config[$key]) === false, 'Secret présent dans les logs');
check($client->lastExchange()['error_message'] === 'Message original', 'Message Moko perdu');
foreach ([422=>1,409=>1,401=>1,429=>3,503=>3,0=>3] as $status=>$expected) {
    $calls = [];
    $client->request('GET', '/v1/health');
    check(count($calls) === $expected, 'Nombre de retries GET incorrect : '.$status);
}
$calls = []; $status = 503;
$client->request('POST', '/v1/payouts', ['merchant_reference'=>'intent-42']);
check(count($calls) === 1, 'Retries PayOut doublés dans le client et le service');
foreach (['0812345678','243812345678','00243812345678','+243 812 345 678'] as $phone) check(MokoPayoutService::normalizePhone($phone) === '+243812345678', 'Normalisation RDC');
foreach (['+33123456789','2438123','abc0812345678'] as $phone) {
    try { MokoPayoutService::normalizePhone($phone); throw new LogicException('Numéro invalide accepté'); } catch (InvalidArgumentException $expected) {}
}
check(MokoPayoutService::validRecipientId('rec_'.str_repeat('a',32)) && !MokoPayoutService::validRecipientId('boutique_42'), 'Identifiants confondus');
echo "Client Moko : octets JSON/HMAC, headers, UTC, nonce unique, retries, secrets masqués et normalisation RDC : OK.\n";
