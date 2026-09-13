<?php

class MokoPayoutService
{
    private $db;
    private $client;
    private $config;
    private $model;
    const STATUSES = [
        'RESERVED' => ['pending', 'Fonds réservés.'],
        'HOLD_REVIEW' => ['pending', 'Validation du backoffice Moko requise.'],
        'DISPATCHED' => ['processing', 'Transfert envoyé à l’opérateur.'],
        'WAITING_CALLBACK' => ['processing', 'Confirmation opérateur attendue.'],
        'COMPLETED' => ['completed', 'Bénéficiaire crédité.'],
        'FAILED' => ['failed', 'Échec définitif, fonds restitués par Moko.'],
        'EXPIRED' => ['expired', 'Payout expiré, fonds restitués par Moko.'],
        'RELEASED' => ['cancelled', 'Payout annulé par le backoffice Moko.'],
    ];

    public function __construct(PDO $db, ?MokoClient $client = null, ?array $config = null)
    {
        $this->db = $db;
        $this->config = $config ?? require __DIR__.'/../config/moko.php';
        $this->client = $client ?? new MokoClient($this->config);
        $this->model = new PayoutTransaction($db);
        try {
            $this->query('SELECT provider,moko_recipient_id,moko_payout_id,moko_status,operator_reference,admin_id,admin_name,error_detail,send_attempts,first_attempt_at,next_attempt_at,last_checked_at,completed_at FROM payout_transactions LIMIT 0');
            $this->query('SELECT merchant_recipient_id,recipient_id,full_name,phone,operator,kyc_reference,status FROM moko_recipients LIMIT 0');
            $this->query('SELECT event_hash,payout_id,payload,processed_at FROM moko_webhook_events LIMIT 0');
            $this->query('SELECT payout_id,status,description,source,payload,created_at FROM payout_status_history LIMIT 0');
            $this->query('SELECT payout_id,admin_id,admin_name,action,amount,currency,phone_number,operator,ip_address,user_agent FROM payout_audit_log LIMIT 0');
        } catch (PDOException $e) { throw new RuntimeException('Module Moko indisponible : appliquez le SQL Moko du README dans la base du site.', 0, $e); }
    }

    private function query($sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    private function enabled()
    {
        if (!$this->config['enabled']) throw new RuntimeException('Les nouveaux versements Moko sont désactivés. Configurez MOKO_PAYOUT_ENABLED=1 après installation.');
        if (!$this->config['public_key'] || !$this->config['secret_key']) throw new RuntimeException('Clés API Moko manquantes.');
        if ($this->config['callback_url'] !== '' && (!filter_var($this->config['callback_url'], FILTER_VALIDATE_URL) || stripos($this->config['callback_url'], 'https://') !== 0 || !$this->config['webhook_secret'])) {
            throw new RuntimeException('Configurez une URL de callback HTTPS et le secret webhook Moko.');
        }
    }

    private function lock($key, callable $action)
    {
        $name = 'moko:'.hash('sha256', strtolower($key));
        $name = substr($name, 0, 64);
        if ((int)$this->query('SELECT GET_LOCK(?, 0)', [$name])->fetchColumn() !== 1) throw new RuntimeException('Opération déjà en cours. Consultez le suivi avant de réessayer.');
        try { return $action(); }
        finally { $this->query('SELECT RELEASE_LOCK(?)', [$name]); }
    }

    private static function field(array $input, $key, $max, $required = true)
    {
        if (isset($input[$key]) && !is_scalar($input[$key])) throw new InvalidArgumentException('Champ invalide : '.$key);
        $value = trim((string)($input[$key] ?? ''));
        $length = preg_match_all('/./us', $value);
        if ($length === false || $length > $max || ($required && $value === '')) throw new InvalidArgumentException('Champ manquant ou trop long : '.$key);
        return $value;
    }

    public function initiatePayout(array $input)
    {
        $this->enabled();
        $reference = strtolower(self::field($input, 'reference', 120));
        $boutiqueId = (int)self::field($input, 'boutique_id', 10, false);
        if ($boutiqueId && !$this->query('SELECT id FROM boutiques WHERE id=?', [$boutiqueId])->fetchColumn()) throw new InvalidArgumentException('Boutique introuvable.');
        if ($boutiqueId) {
            $profile = $this->model->recipientProfile($boutiqueId);
            if (empty($profile['existing_recipient_id'])) throw new InvalidArgumentException('Enregistrez d’abord le bénéficiaire de cette boutique.');
            foreach (['merchant_recipient_id','beneficiary','phone_number','operator','kyc_reference','existing_recipient_id'] as $field) $input[$field] = $profile[$field] ?? '';
        }
        $merchantId = self::field($input, 'merchant_recipient_id', 128);
        $name = self::field($input, 'beneficiary', 190);
        $phone = self::field($input, 'phone_number', 30);
        $operator = self::field($input, 'operator', 16);
        $currency = self::field($input, 'currency', 3);
        $amount = self::field($input, 'amount', 13);
        $reason = self::field($input, 'reason', 255);
        $kyc = self::field($input, 'kyc_reference', 128, false);
        $importId = self::field($input, 'existing_recipient_id', 64, false);
        if ($boutiqueId) {
            $owner = $this->query('SELECT boutique_id FROM boutique_payout_profiles WHERE merchant_recipient_id=?', [$merchantId])->fetchColumn();
            if ($owner && (int)$owner !== $boutiqueId) throw new InvalidArgumentException('Ce bénéficiaire est déjà rattaché à une autre boutique.');
        }
        if (!preg_match('/^[a-z0-9._-]{4,120}$/D', $reference) || !preg_match('/^[a-zA-Z0-9_-]{1,128}$/D', $merchantId)) throw new InvalidArgumentException('Référence ou identifiant bénéficiaire invalide.');
        if (!preg_match('/^\+243[0-9]{9}$/D', $phone)) throw new InvalidArgumentException('Le numéro doit être au format +243 suivi de 9 chiffres.');
        if (preg_match_all('/./us', $name) < 2 || !in_array($operator, ['mpesa','airtel','orange','afrimoney'], true) || !in_array($currency, ['USD','CDF'], true)) throw new InvalidArgumentException('Bénéficiaire, opérateur ou devise invalide.');
        if (!preg_match('/^(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?$/D', $amount) || (float)$amount <= 0) throw new InvalidArgumentException('Montant positif requis, avec au plus deux décimales.');
        $amount = number_format((float)$amount, 2, '.', '');
        return $this->lock('payout:'.$reference, function () use ($reference,$merchantId,$name,$phone,$operator,$currency,$amount,$reason,$kyc,$importId,$boutiqueId) {
            $row = $this->model->findByReference($reference);
            if ($row) {
                if ($boutiqueId && (int)$this->query('SELECT boutique_id FROM boutique_payout_links WHERE payout_id=?', [$row['id']])->fetchColumn() !== $boutiqueId) throw new InvalidArgumentException('Cette référence appartient à un autre versement.');
                if (($row['provider'] ?? '') !== 'moko' || $row['phone_number'] !== $phone || $row['operator'] !== $operator || $row['currency'] !== $currency || $row['amount'] !== $amount || $row['reason'] !== $reason || $row['beneficiary'] !== $name) throw new InvalidArgumentException('Cette référence désigne déjà un autre versement.');
                $storedMerchant = $this->query('SELECT merchant_recipient_id FROM moko_recipients WHERE recipient_id=?', [$row['moko_recipient_id']])->fetchColumn();
                if (strcasecmp((string)$storedMerchant, $merchantId) !== 0) throw new InvalidArgumentException('Cette référence appartient à un autre bénéficiaire.');
                return $this->send($row);
            }
            $recipient = $this->recipient($merchantId,$name,$phone,$operator,$kyc,$importId);
            $payload = ['recipient_id'=>$recipient['recipient_id'], 'amount'=>(float)$amount, 'currency'=>$currency, 'merchant_reference'=>$reference, 'description'=>$reason, 'callback_url'=>$this->config['callback_url'] ?: null];
            $admin = ohnous_get_current_account();
            $adminName = (string)($admin['nom'] ?? $admin['name'] ?? 'Administrateur');
            $this->db->beginTransaction();
            try {
                $this->query('INSERT INTO payout_transactions (provider,reference,beneficiary,phone_number,operator,amount,currency,reason,status,status_description,moko_recipient_id,request_payload,admin_id,admin_name) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    ['moko',$reference,$name,$phone,$operator,$amount,$currency,$reason,'pending_send','Prêt à être envoyé à Moko.',$recipient['recipient_id'],json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),(int)($admin['id']??0),$adminName]);
                $id = (int)$this->db->lastInsertId();
                if ($boutiqueId) {
                    $this->query('INSERT INTO boutique_payout_links (payout_id,boutique_id) VALUES (?,?)', [$id,$boutiqueId]);
                    // Le premier profil reste stable, même si un autre numéro est utilisé ensuite.
                    $this->query('INSERT INTO boutique_payout_profiles (boutique_id,merchant_recipient_id,beneficiary,phone_number,operator,kyc_reference,existing_recipient_id) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE boutique_id=boutique_id', [$boutiqueId,$merchantId,$name,$phone,$operator,$kyc ?: null,$recipient['recipient_id']]);
                    $owner = $this->query('SELECT boutique_id FROM boutique_payout_profiles WHERE merchant_recipient_id=?', [$merchantId])->fetchColumn();
                    if ($owner && (int)$owner !== $boutiqueId) throw new InvalidArgumentException('Ce bénéficiaire est déjà rattaché à une autre boutique.');
                }
                $this->model->addStatusEvent($id, 'pending_send', 'Intention enregistrée avant envoi.', 'admin');
                $this->query('INSERT INTO payout_audit_log (payout_id,admin_id,admin_name,action,amount,currency,phone_number,operator,ip_address,user_agent) VALUES (?,?,?,?,?,?,?,?,?,?)',
                    [$id,(int)($admin['id']??0),$adminName,'moko_payout_created',$amount,$currency,$phone,$operator,substr($_SERVER['REMOTE_ADDR']??'',0,64),substr($_SERVER['HTTP_USER_AGENT']??'',0,500)]);
                $this->db->commit();
            } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
            return $this->send($this->model->findByReference($reference));
        });
    }

    public function registerBoutiqueRecipient($boutiqueId, array $input = [])
    {
        $boutiqueId = filter_var($boutiqueId, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        if (!$boutiqueId) throw new InvalidArgumentException('Choisissez une boutique.');
        return $this->lock('boutique-recipient:'.$boutiqueId, function () use ($boutiqueId, $input) {
            $boutique = $this->query('SELECT * FROM boutiques WHERE id=?', [$boutiqueId])->fetch(PDO::FETCH_ASSOC);
            if (!$boutique) throw new InvalidArgumentException('Boutique introuvable.');
            $profile = $this->model->recipientProfile($boutiqueId);
            // Les coordonnées déjà transmises à Moko restent immuables, même après une réponse incertaine.
            $stored = $profile && (!empty($profile['existing_recipient_id']) || isset($profile['recipient_status']));
            $details = $stored ? $profile : array_merge($profile ?? [], $input);
            $name = self::field($details, 'beneficiary', 190);
            $phone = self::field($details, 'phone_number', 30);
            $operator = self::field($details, 'operator', 16);
            $kyc = self::field($details, 'kyc_reference', 128, false);
            if (preg_match_all('/./us', $name) < 2) throw new InvalidArgumentException('Renseignez le nom complet du titulaire du compte Mobile Money.');
            if (!preg_match('/^\+243[0-9]{9}$/D', $phone)) throw new InvalidArgumentException('Le numéro doit être au format +243 suivi de 9 chiffres.');
            if (!in_array($operator, ['mpesa','airtel','orange','afrimoney'], true)) throw new InvalidArgumentException('Choisissez un opérateur Mobile Money.');
            $merchantId = $profile['merchant_recipient_id'] ?? 'boutique_'.$boutiqueId;
            $owner = $this->query('SELECT boutique_id FROM boutique_payout_profiles WHERE merchant_recipient_id=?', [$merchantId])->fetchColumn();
            if ($owner && (int)$owner !== $boutiqueId) throw new InvalidArgumentException('Ce bénéficiaire appartient à une autre boutique.');
            $this->query('INSERT INTO boutique_payout_profiles (boutique_id,merchant_recipient_id,beneficiary,phone_number,operator,kyc_reference) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE beneficiary=VALUES(beneficiary),phone_number=VALUES(phone_number),operator=VALUES(operator),kyc_reference=VALUES(kyc_reference)', [$boutiqueId,$merchantId,$name,$phone,$operator,$kyc ?: null]);
            if (!ohnous_is_store_active($boutique)) return ['result'=>'ok','registered'=>false,'msg'=>'Coordonnées enregistrées. Le bénéficiaire sera créé à l’activation de votre boutique.', 'profile'=>$this->model->recipientProfile($boutiqueId)];
            $this->enabled();
            $recipient = $this->recipient($merchantId,$name,$phone,$operator,$kyc,(string)($profile['existing_recipient_id'] ?? ''),false);
            $this->query('UPDATE boutique_payout_profiles SET existing_recipient_id=? WHERE boutique_id=?', [$recipient['recipient_id'],$boutiqueId]);
            return ['result'=>'ok','registered'=>true,'msg'=>$recipient['status'] === 'ACTIVE' ? 'Bénéficiaire enregistré chez Moko.' : 'Bénéficiaire enregistré. Sa validation par Moko est encore nécessaire.', 'profile'=>$this->model->recipientProfile($boutiqueId)];
        });
    }

    private function recipient($merchantId,$name,$phone,$operator,$kyc,$importId,$requireActive = true)
    {
        return $this->lock('recipient:'.$merchantId, function () use ($merchantId,$name,$phone,$operator,$kyc,$importId,$requireActive) {
            $row = $this->query('SELECT * FROM moko_recipients WHERE merchant_recipient_id=?', [$merchantId])->fetch(PDO::FETCH_ASSOC);
            if ($row && ($row['full_name'] !== $name || $row['phone'] !== $phone || $row['operator'] !== $operator)) throw new InvalidArgumentException('Ce bénéficiaire existe avec des coordonnées différentes. Utilisez ses coordonnées enregistrées ; une modification sensible se traite chez Moko.');
            if (!$row) {
                $this->query('INSERT INTO moko_recipients (merchant_recipient_id,full_name,phone,operator,kyc_reference) VALUES (?,?,?,?,?)', [$merchantId,$name,$phone,$operator,$kyc ?: null]);
                $row = $this->query('SELECT * FROM moko_recipients WHERE merchant_recipient_id=?', [$merchantId])->fetch(PDO::FETCH_ASSOC);
            }
            $id = $row['recipient_id'] ?: $importId;
            if ($id !== '') {
                $response = $this->client->request('GET', '/v1/recipients/'.rawurlencode($id));
                if (!$requireActive && $response['http'] === 404 && ($response['data']['detail']['code'] ?? '') === 'recipient_not_found') {
                    $response = $this->client->request('POST', '/v1/recipients', ['merchant_recipient_id'=>$merchantId,'full_name'=>$name,'phone'=>$phone,'operator'=>$operator,'country'=>'CD','kyc_reference'=>$kyc ?: null]);
                }
            } else {
                $response = $this->client->request('POST', '/v1/recipients', ['merchant_recipient_id'=>$merchantId,'full_name'=>$name,'phone'=>$phone,'operator'=>$operator,'country'=>'CD','kyc_reference'=>$kyc ?: null]);
            }
            if ($response['http'] === 409 && !$requireActive) $response = $this->findRecipient($merchantId);
            if ($response['http'] === 409) throw new RuntimeException('Bénéficiaire déjà enregistré chez Moko. Enregistrez à nouveau le bénéficiaire pour le rattacher.');
            $data = $response['data'];
            if (!in_array($response['http'], [200,201], true) || empty($data['recipient_id']) || empty($data['status'])) throw new RuntimeException('Enregistrement bénéficiaire : '.$this->error($response));
            foreach (['merchant_recipient_id'=>$merchantId,'full_name'=>$name,'phone'=>$phone,'operator'=>$operator] as $key=>$expected) {
                if (!isset($data[$key]) || $data[$key] !== $expected) throw new RuntimeException('Les coordonnées retournées par Moko ne correspondent pas au bénéficiaire demandé.');
            }
            $this->query('UPDATE moko_recipients SET recipient_id=?, status=? WHERE id=?', [$data['recipient_id'],$data['status'],$row['id']]);
            if ($requireActive && $data['status'] !== 'ACTIVE') throw new RuntimeException('Bénéficiaire Moko non actif : '.$data['status'].'. Faites valider son dossier avant de verser.');
            return $data;
        });
    }

    private function findRecipient($merchantId)
    {
        // Après un timeout ou un doublon, retrouver le même bénéficiaire sans changer son identifiant marchand.
        for ($page = 1; $page <= 100; $page++) {
            $response = $this->client->request('GET', '/v1/recipients', null, ['page'=>$page,'page_size'=>100]);
            if ($response['http'] !== 200) throw new RuntimeException('Recherche du bénéficiaire : '.$this->error($response));
            $data = $response['data'];
            $items = $data['items'] ?? $data['recipients'] ?? (($data === [] || array_keys($data) === range(0, count($data) - 1)) ? $data : null);
            if (!is_array($items)) throw new RuntimeException('Liste des bénéficiaires Moko non exploitable. Contactez le support.');
            foreach ($items as $item) {
                if (is_array($item) && ($item['merchant_recipient_id'] ?? '') === $merchantId && !empty($item['recipient_id'])) {
                    return $this->client->request('GET', '/v1/recipients/'.rawurlencode($item['recipient_id']));
                }
            }
            if (!$items || (isset($data['total']) && $page * 100 >= (int)$data['total'])) break;
        }
        throw new RuntimeException('Bénéficiaire déjà présent chez Moko, mais introuvable dans la liste. Contactez le support Moko pour vérifier son rattachement.');
    }

    private function error(array $response)
    {
        $detail = $response['data']['detail'] ?? [];
        $code = is_array($detail) ? ($detail['code'] ?? '') : '';
        $messages = ['insufficient_balance'=>'Solde Moko insuffisant. Approvisionnez le portefeuille.', 'recipient_cooldown'=>'Bénéficiaire en période de sécurité de 24 h.', 'auth_signature_invalid'=>'Authentification refusée : vérifiez les clés et l’horloge du serveur.'];
        return $messages[$code] ?? ($code !== '' ? 'Erreur Moko : '.$code : 'Réponse Moko non exploitable (HTTP '.(int)$response['http'].').');
    }

    private function send(array $row)
    {
        if ($row['moko_payout_id'] || !in_array($row['status'], ['pending_send','send_unknown'], true)) return $this->result($row);
        if ($row['first_attempt_at'] && strtotime($row['first_attempt_at'].' UTC') <= time()-23*3600) {
            $this->model->updateById($row['id'], ['status'=>'manual_review','status_description'=>'Envoi incertain ancien : rapprochez cette référence avec Moko avant toute autre opération.']);
            return $this->result($this->model->findById($row['id']));
        }
        if ((int)$row['send_attempts'] >= 3 || ($row['next_attempt_at'] && strtotime($row['next_attempt_at'].' UTC') > time())) return $this->result($row);
        $this->enabled();
        // Persist attempt BEFORE HTTP. A process crash consumes an attempt and keeps the same body/reference.
        $this->query("UPDATE payout_transactions SET status='send_unknown', first_attempt_at=COALESCE(first_attempt_at,UTC_TIMESTAMP()), send_attempts=send_attempts+1, next_attempt_at=DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 SECOND) WHERE id=?", [$row['id']]);
        try { $response = $this->client->request('POST', '/v1/payouts', json_decode($row['request_payload'], true, 512, JSON_THROW_ON_ERROR)); }
        catch (Throwable $e) { $response = ['http'=>0,'data'=>[],'valid'=>false]; }
        $data = $response['data'];
        if (in_array($response['http'], [200,202], true) && !empty($data['payout_id']) && isset(self::STATUSES[$data['status'] ?? ''])) {
            $this->apply($row['id'], $data, 'moko_api');
            $this->processInbox($data['payout_id']);
        } else {
            $uncertain = $response['http'] === 0 || $response['http'] === 429 || $response['http'] >= 500 || ($response['http'] >= 200 && $response['http'] < 300);
            $attempts = (int)$row['send_attempts'] + 1;
            $status = $uncertain ? ($attempts >= 3 ? 'manual_review' : 'send_unknown') : ($attempts > 1 ? 'manual_review' : 'send_rejected');
            $message = $uncertain ? 'Résultat incertain. Conservez cette référence. '.($attempts >= 3 ? 'Trois tentatives atteintes : rapprochement Moko requis.' : 'Reprise différée avec la même référence.') : $this->error($response);
            if (!$uncertain && $attempts > 1) $message .= ' Une tentative antérieure reste incertaine : rapprochement Moko requis.';
            $delay = 60 * (2 ** $attempts);
            $this->query('UPDATE payout_transactions SET status=?,status_description=?,response_payload=?,error_detail=?,next_attempt_at=DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? SECOND) WHERE id=?', [$status,$message,json_encode($data),$this->error($response),$delay,$row['id']]);
            $this->model->addStatusEvent($row['id'], $status, $message, 'moko_api', ['http'=>$response['http']]);
        }
        return $this->result($this->model->findById($row['id']));
    }

    public static function acceptsTransition($old, $new)
    {
        if (!isset(self::STATUSES[$new])) return false;
        if (in_array($old, ['COMPLETED','FAILED','EXPIRED','RELEASED'], true)) return $old === $new;
        $rank = ['RESERVED'=>1,'HOLD_REVIEW'=>1,'DISPATCHED'=>2,'WAITING_CALLBACK'=>3];
        return !isset($rank[$new]) || ($rank[$new] >= ($rank[$old] ?? 0));
    }

    private function apply($id, array $data, $source)
    {
        $this->db->beginTransaction();
        try {
            $row = $this->query('SELECT * FROM payout_transactions WHERE id=? FOR UPDATE', [$id])->fetch(PDO::FETCH_ASSOC);
            if (!$row || $row['provider'] !== 'moko' || empty($data['payout_id']) || strlen($data['payout_id']) > 64) throw new RuntimeException('Payout Moko non reconnu.');
            if ($row['moko_payout_id'] && $row['moko_payout_id'] !== $data['payout_id']) throw new RuntimeException('Identifiant Moko incohérent.');
            foreach (['recipient_id'=>$row['moko_recipient_id'],'currency'=>$row['currency'],'merchant_reference'=>$row['reference']] as $key=>$expected) {
                if (isset($data[$key]) && (string)$data[$key] !== $expected) throw new RuntimeException('Réponse Moko incohérente : '.$key);
            }
            if (isset($data['amount']) && (!is_numeric($data['amount']) || number_format((float)$data['amount'],2,'.','') !== $row['amount'])) throw new RuntimeException('Montant Moko incohérent.');
            $new = $data['status'] ?? '';
            if (!self::acceptsTransition($row['moko_status'], $new)) { $this->db->commit(); return; }
            [$status,$message] = self::STATUSES[$new];
            $this->query('UPDATE payout_transactions SET moko_payout_id=?,moko_status=?,transaction_id=?,status=?,status_description=?,operator_reference=COALESCE(?,operator_reference),response_payload=?,completed_at=CASE WHEN ? = \'COMPLETED\' THEN COALESCE(completed_at,UTC_TIMESTAMP()) ELSE completed_at END WHERE id=?',
                [$data['payout_id'],$new,$data['payout_id'],$status,$message,$data['switch_reference']??null,json_encode($data, JSON_UNESCAPED_UNICODE),$new,$id]);
            if ($row['moko_status'] !== $new) $this->model->addStatusEvent($id,$status,$new.' — '.$message,$source,$data);
            $this->db->commit();
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    private function result(array $row)
    {
        $stopped = in_array($row['status'], ['completed','failed','expired','cancelled','send_rejected','manual_review'], true);
        return ['result'=>'ok','msg'=>$row['status_description'],'description'=>$row['status_description'],'reference'=>$row['reference'], 'status'=>$row['status'],'moko_status'=>$row['moko_status'], 'transaction_id'=>$row['moko_payout_id'],'freshpay_reference'=>$row['moko_payout_id'],'operator_reference'=>$row['operator_reference'],'final'=>$stopped,'redirect'=>'/admin-payout-suivi?reference='.rawurlencode($row['reference'])];
    }

    public function verifyPayoutStatus($reference)
    {
        $row = $this->model->findByReference($reference);
        if (!$row || $row['provider'] !== 'moko') throw new InvalidArgumentException('Payout Moko introuvable.');
        if ($row['moko_payout_id']) {
            $response = $this->client->request('GET', '/v1/payouts/'.rawurlencode($row['moko_payout_id']));
            if ($response['http'] !== 200 || empty($response['data']['payout_id']) || !isset(self::STATUSES[$response['data']['status']??''])) throw new RuntimeException($this->error($response));
            $this->apply($row['id'], $response['data'], 'status_api');
            $this->processInbox($row['moko_payout_id']);
        }
        return $this->result($this->model->findById($row['id']));
    }

    public function handleWebhook($raw, $signature)
    {
        if (!MokoClient::verifyWebhook($raw, $signature, $this->config['webhook_secret'])) throw new UnexpectedValueException('Signature invalide.');
        $event = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $data = $event['data'] ?? [];
        if (!is_array($data) || empty($data['payout_id']) || !is_string($data['payout_id']) || strlen($data['payout_id']) > 64 || !isset(self::STATUSES[$data['status']??''])) throw new InvalidArgumentException('Événement Moko invalide.');
        $this->query('INSERT INTO moko_webhook_events (event_hash,payout_id,payload) VALUES (?,?,?) ON DUPLICATE KEY UPDATE event_hash=VALUES(event_hash)', [hash('sha256',$raw),$data['payout_id'],$raw]);
        $this->processInbox($data['payout_id']);
        return ['result'=>'ok'];
    }

    private function processInbox($payoutId)
    {
        $row = $this->query("SELECT id FROM payout_transactions WHERE provider='moko' AND moko_payout_id=?", [$payoutId])->fetch(PDO::FETCH_ASSOC);
        if (!$row) return; // Durable inbox covers webhook arriving before the API response.
        $events = $this->query('SELECT * FROM moko_webhook_events WHERE payout_id=? AND processed_at IS NULL ORDER BY id LIMIT 100', [$payoutId])->fetchAll(PDO::FETCH_ASSOC);
        foreach ($events as $event) {
            $payload = json_decode($event['payload'], true, 512, JSON_THROW_ON_ERROR);
            $this->apply($row['id'], $payload['data'], 'moko_callback');
            $this->query('UPDATE moko_webhook_events SET processed_at=UTC_TIMESTAMP() WHERE id=?', [$event['id']]);
        }
    }

    public function reconcile()
    {
        $rows = $this->query("SELECT reference FROM payout_transactions WHERE provider='moko' AND status IN ('pending_send','send_unknown','pending','processing') ORDER BY COALESCE(last_checked_at,created_at),id LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        $report = [];
        foreach ($rows as $item) {
            try {
                $report[] = $this->lock('payout:'.$item['reference'], function () use ($item) {
                    $row = $this->model->findByReference($item['reference']);
                    $this->query('UPDATE payout_transactions SET last_checked_at=UTC_TIMESTAMP() WHERE id=?', [$row['id']]);
                    if ($row['moko_payout_id']) return $this->verifyPayoutStatus($row['reference']);
                    return $this->send($row);
                });
            } catch (Throwable $e) { $report[] = ['reference'=>$item['reference'],'result'=>'error','msg'=>$e->getMessage()]; }
        }
        return $report;
    }

    public function diagnostic()
    {
        return ['health'=>$this->client->request('GET','/v1/health'), 'balance'=>$this->client->request('GET','/v1/balance')];
    }

    public function recover($reference, $payoutId)
    {
        return $this->lock('payout:'.$reference, function () use ($reference,$payoutId) {
            $row = $this->model->findByReference($reference);
            if (!$row || $row['provider'] !== 'moko') throw new InvalidArgumentException('Payout local introuvable.');
            $response = $this->client->request('GET','/v1/payouts/'.rawurlencode($payoutId));
            $data = $response['data'];
            if ($response['http'] !== 200 || ($data['payout_id']??'') !== $payoutId || strtolower($data['merchant_reference']??'') !== $row['reference'] || !isset($data['amount'],$data['currency'],$data['recipient_id'])) throw new RuntimeException('Rattachement refusé : Moko doit confirmer tous les identifiants et le montant de cette opération.');
            $this->apply($row['id'],$data,'recovery');
            $this->processInbox($payoutId);
            return $this->result($this->model->findById($row['id']));
        });
    }
}
