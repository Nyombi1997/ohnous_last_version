<?php

class FreshPayService
{
    private $bdd;
    private $config;
    private $transactionModel;
    private $amountService;

    public function __construct(PDO $bdd)
    {
        $this->bdd = $bdd;
        $this->config = include CONFIG . 'payment.php';
        $this->transactionModel = new PaymentTransaction($bdd);
        $this->amountService = new OrderAmountService();
    }

    public function initiateCheckoutPayment(array $request)
    {
        ohnous_boot_checkout_session();

        $mode = trim((string)($request['mode'] ?? 'cart'));
        $context = ohnous_get_checkout_context($mode);
        if (empty($context['items'])) {
            return ['result' => 'error', 'msg' => "Aucun article n'est disponible pour ce checkout."];
        }

        if (
            !ohnous_table_exists('commandes') ||
            !ohnous_table_exists('commande_articles') ||
            !ohnous_table_exists('payment_transactions')
        ) {
            return ['result' => 'error', 'msg' => "Les tables SQL du paiement sont manquantes. Appliquez le SQL ajouté dans le README.md."];
        }

        $telephone = trim((string)($request['telephone'] ?? ''));
        $adresse = trim((string)($request['adresse'] ?? ''));
        $email = trim((string)($request['email'] ?? ''));
        $zoneId = (int)($request['zone_id'] ?? 0);
        $paymentMethod = trim((string)($request['payment_method'] ?? 'mobile_money'));
        $paymentOperator = trim((string)($request['payment_operator'] ?? ''));
        $customerNumber = trim((string)($request['customer_number'] ?? $telephone));
        $firstname = trim((string)($request['firstname'] ?? 'Client'));
        $lastname = trim((string)($request['lastname'] ?? 'OhNous'));

        if ($telephone === '' || $adresse === '' || $email === '' || $zoneId <= 0) {
            return ['result' => 'error', 'msg' => "Veuillez renseigner le téléphone, l'adresse, l'email et la zone de livraison."];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['result' => 'error', 'msg' => "L'adresse email saisie est invalide."];
        }

        $zone = ohnous_get_delivery_zone_by_id($zoneId);
        if (!$zone || (isset($zone['actif']) && (int)$zone['actif'] !== 1)) {
            return ['result' => 'error', 'msg' => "La zone de livraison sélectionnée est invalide."];
        }

        if ($paymentMethod === 'visa' && empty($this->config['freshpay']['visa']['enabled'])) {
            return ['result' => 'error', 'msg' => "Le paiement Visa est bientôt disponible. La structure technique est prête, mais les paramètres FreshPay Visa restent à confirmer dans la configuration."];
        }

        if ($paymentMethod === 'mobile_money' && $customerNumber === '') {
            return ['result' => 'error', 'msg' => "Veuillez saisir le numéro Mobile Money du client."];
        }

        $account = ohnous_get_current_account();
        $totals = $this->amountService->resolveCheckoutTotals($context['items'], $zoneId);
        $fingerprint = sha1(json_encode([
            'mode' => $context['mode'],
            'items' => $context['items'],
            'telephone' => $telephone,
            'email' => $email,
            'zone_id' => $zoneId,
            'payment_method' => $paymentMethod,
            'customer_number' => $customerNumber,
            'total' => $totals['total'],
        ], JSON_UNESCAPED_UNICODE));

        $duplicateGuard = $_SESSION['ohnous_payment_guard'] ?? [];
        if (
            !empty($duplicateGuard['fingerprint']) &&
            $duplicateGuard['fingerprint'] === $fingerprint &&
            !empty($duplicateGuard['reference']) &&
            !empty($duplicateGuard['created_at']) &&
            (time() - (int)$duplicateGuard['created_at']) <= 45
        ) {
            return [
                'result' => 'ok',
                'msg' => 'Une demande identique est déjà en cours de traitement.',
                'reference' => $duplicateGuard['reference'],
                'payment_status' => 'pending',
                'redirect' => '/paiement-retour?reference=' . rawurlencode($duplicateGuard['reference']),
            ];
        }

        $order = $this->createOrder($context, $account, [
            'telephone' => $telephone,
            'adresse' => $adresse,
            'email' => $email,
            'zone_id' => $zoneId,
            'zone_nom' => $zone['nom'] ?? '',
            'firstname' => $firstname,
            'lastname' => $lastname,
        ], $totals);

        $reference = $this->generateReference($order['order_id']);
        $_SESSION['ohnous_payment_guard'] = [
            'fingerprint' => $fingerprint,
            'reference' => $reference,
            'created_at' => time(),
        ];

        $existingPayment = $this->transactionModel->findByOrderId($order['order_id']);
        if ($existingPayment && in_array((string)$existingPayment['trans_status'], ['success', 'successful', 'paid'], true)) {
            return ['result' => 'error', 'msg' => "Cette commande a déjà été payée."];
        }

        $requestPayload = [
            'merchant_id' => (string)$this->config['freshpay']['merchant_id'],
            'merchant_secrete' => (string)$this->config['freshpay']['merchant_secret'],
            'amount' => number_format((float)$totals['total'], 2, '.', ''),
            'currency' => $totals['currency'],
            'action' => 'payment',
            'customer_number' => $customerNumber,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'e-mail' => $email,
            'reference' => $reference,
            'method' => $this->resolveFreshPayMethod($paymentMethod),
            'callback_url' => rtrim(HOST, '/') . '/paiement-callback-freshpay',
        ];

        if ($paymentOperator !== '') {
            $requestPayload['operator'] = $paymentOperator;
        }

        $transactionId = $this->transactionModel->create([
            'order_id' => (int)$order['order_id'],
            'provider' => 'freshpay',
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'freshpay_transaction_id' => null,
            'financial_institution_id' => null,
            'customer_number' => $this->maskCustomerNumber($customerNumber),
            'amount' => number_format((float)$totals['total'], 2, '.', ''),
            'currency' => $totals['currency'],
            'request_payload' => json_encode($requestPayload, JSON_UNESCAPED_UNICODE),
            'response_payload' => null,
            'callback_payload' => null,
            'status' => 'initiated',
            'trans_status' => 'pending',
            'trans_status_description' => 'Paiement initié côté OhNous, en attente de la réponse FreshPay.',
        ]);

        if ($paymentMethod === 'visa') {
            $this->transactionModel->updateById($transactionId, [
                'status' => 'todo',
                'trans_status' => 'todo',
                'trans_status_description' => 'TODO Visa : compléter les paramètres FreshPay Visa dès que le flux officiel est confirmé.',
            ]);

            return [
                'result' => 'error',
                'msg' => "La structure Visa est prête, mais les paramètres FreshPay Visa doivent encore être confirmés dans la configuration.",
                'reference' => $reference,
            ];
        }

        $remoteResponse = $this->sendApiRequest('initiate', $requestPayload);
        $normalized = $this->normalizeGatewayResponse($remoteResponse);
        $this->clearCheckoutSource($context['mode']);

        $this->transactionModel->updateById($transactionId, [
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'trans_status_description' => $normalized['description'],
            'freshpay_transaction_id' => $normalized['transaction_id'],
            'financial_institution_id' => $normalized['financial_institution_id'],
            'response_payload' => json_encode($remoteResponse, JSON_UNESCAPED_UNICODE),
        ]);

        update_bdd($this->bdd, 'commandes', [
            'statut' => $normalized['order_status'],
        ], "id = '" . (int)$order['order_id'] . "'");

        return [
            'result' => $normalized['result'],
            'msg' => $normalized['frontend_message'],
            'reference' => $reference,
            'order_number' => $order['order_number'],
            'payment_status' => $normalized['trans_status'],
            'redirect' => '/paiement-retour?reference=' . rawurlencode($reference),
        ];
    }

    public function handleCallback()
    {
        $rawBody = file_get_contents('php://input');
        $decoded = json_decode($rawBody, true);
        $payload = is_array($decoded) ? $decoded : $_POST;

        if (!$this->isValidCallbackSignature($rawBody, $payload)) {
            http_response_code(401);
            return [
                'result' => 'error',
                'msg' => 'Signature callback invalide.'
            ];
        }

        $data = $this->extractCallbackData($payload);
        $reference = trim((string)($data['reference'] ?? $payload['reference'] ?? ''));

        if ($reference === '') {
            http_response_code(422);
            return [
                'result' => 'error',
                'msg' => 'Référence callback manquante.'
            ];
        }

        $transaction = $this->transactionModel->findByReference($reference);
        if (!$transaction) {
            http_response_code(404);
            return [
                'result' => 'error',
                'msg' => 'Transaction introuvable.'
            ];
        }

        $normalized = $this->normalizeGatewayResponse($data);
        $this->transactionModel->updateById((int)$transaction['id'], [
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'trans_status_description' => $normalized['description'],
            'freshpay_transaction_id' => $normalized['transaction_id'] ?: $transaction['freshpay_transaction_id'],
            'financial_institution_id' => $normalized['financial_institution_id'] ?: $transaction['financial_institution_id'],
            'callback_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'response_payload' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        update_bdd($this->bdd, 'commandes', [
            'statut' => $normalized['order_status'],
        ], "id = '" . (int)$transaction['order_id'] . "'");

        return [
            'result' => 'ok',
            'msg' => 'Callback FreshPay traité.',
            'reference' => $reference,
            'trans_status' => $normalized['trans_status'],
        ];
    }

    public function verifyTransactionStatus($reference)
    {
        $reference = trim((string)$reference);
        if ($reference === '') {
            return ['result' => 'error', 'msg' => 'Référence manquante.'];
        }

        $transaction = $this->transactionModel->findByReference($reference);
        if (!$transaction) {
            return ['result' => 'error', 'msg' => 'Transaction introuvable.'];
        }

        $requestPayload = [
            'reference' => $reference,
            'merchant_id' => (string)$this->config['freshpay']['merchant_id'],
            'merchant_secrete' => (string)$this->config['freshpay']['merchant_secret'],
        ];

        $remoteResponse = $this->sendApiRequest('status', $requestPayload);
        $normalized = $this->normalizeGatewayResponse($remoteResponse);

        $this->transactionModel->updateById((int)$transaction['id'], [
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'trans_status_description' => $normalized['description'],
            'freshpay_transaction_id' => $normalized['transaction_id'] ?: $transaction['freshpay_transaction_id'],
            'financial_institution_id' => $normalized['financial_institution_id'] ?: $transaction['financial_institution_id'],
            'response_payload' => json_encode($remoteResponse, JSON_UNESCAPED_UNICODE),
        ]);

        update_bdd($this->bdd, 'commandes', [
            'statut' => $normalized['order_status'],
        ], "id = '" . (int)$transaction['order_id'] . "'");

        return [
            'result' => 'ok',
            'msg' => 'Statut synchronisé.',
            'reference' => $reference,
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'description' => $normalized['description'],
        ];
    }

    private function createOrder(array $context, array $account, array $customer, array $totals)
    {
        $orderNumber = 'OHN-' . date('YmdHis') . '-' . mt_rand(100, 999);

        insert_bdd($this->bdd, 'commandes', [
            'order_number' => $orderNumber,
            'checkout_mode' => $context['mode'],
            'client_type' => $account['type'] ?? 'invite',
            'client_id' => (int)($account['id'] ?? 0),
            'nom_client' => trim($customer['firstname'] . ' ' . $customer['lastname']),
            'telephone' => $customer['telephone'],
            'adresse' => $customer['adresse'],
            'email' => $customer['email'],
            'zone_id' => (int)$customer['zone_id'],
            'zone_nom' => $customer['zone_nom'],
            'livraison_prix' => $totals['delivery_price'],
            'sous_total' => $totals['subtotal'],
            'total' => $totals['total'],
            'statut' => 'paiement_initié',
        ]);

        $orderId = (int)$this->bdd->lastInsertId();

        foreach ($context['items'] as $item) {
            $articleId = (int)($item['id'] ?? 0);
            $article = $articleId > 0 ? only_select('articles', 'id = ' . $articleId, null, null) : null;

            insert_bdd($this->bdd, 'commande_articles', [
                'commande_id' => $orderId,
                'article_id' => $articleId,
                'article_nom' => $item['name'] ?? '',
                'article_slug' => $item['slug'] ?? '',
                'taille' => $item['size'] ?? '',
                'quantite' => max(1, (int)($item['qty'] ?? 1)),
                'prix_unitaire' => (float)($item['price'] ?? 0),
                'image' => $item['image'] ?? '',
                'boutique_id' => (int)($article['boutique'] ?? 0),
            ]);
        }

        return [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
        ];
    }

    private function generateReference($orderId)
    {
        return 'FP-' . date('YmdHis') . '-' . (int)$orderId . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    private function resolveFreshPayMethod($paymentMethod)
    {
        $map = $this->config['freshpay']['method_map'];
        return $map[$paymentMethod] ?? $paymentMethod;
    }

    private function sendApiRequest($type, array $payload)
    {
        $mode = $this->config['freshpay']['mode'] === 'production' ? 'production' : 'test';
        $url = trim((string)($this->config['freshpay']['endpoints'][$mode][$type] ?? ''));

        if ($url === '') {
            throw new RuntimeException("CONFIG_ENDPOINT_MISSING: endpoint '{$type}' non configuré pour le mode '{$mode}'.");
        }

        $ch = curl_init($url);
        $format = $this->config['freshpay']['http']['request_format'];
        $headers = ['Accept: application/json'];

        if ($format === 'json') {
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Type: application/json';
        } else {
            $body = http_build_query($payload);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => (int)$this->config['freshpay']['http']['connect_timeout'],
            CURLOPT_TIMEOUT => (int)$this->config['freshpay']['http']['timeout'],
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $responseBody = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false || $curlError !== '') {
            throw new RuntimeException('HTTP_TRANSPORT_ERROR: cURL errno=' . (int)$curlErrno . '; message=' . $curlError);
        }

        if ($statusCode >= 400) {
            $snippet = trim(mb_substr(preg_replace('/\s+/', ' ', (string)$responseBody), 0, 300));
            throw new RuntimeException('HTTP_STATUS_ERROR: status=' . $statusCode . '; body=' . $snippet);
        }

        $decoded = json_decode($responseBody, true);
        if (is_array($decoded)) {
            $decoded['_http_status'] = $statusCode;
            return $decoded;
        }

        return [
            '_http_status' => $statusCode,
            'raw_body' => $responseBody,
        ];
    }

    private function normalizeGatewayResponse(array $response)
    {
        $callbackConfig = $this->config['freshpay']['callback'];
        $status = strtolower((string)($response[$callbackConfig['status_field']] ?? $response['status'] ?? 'submitted'));
        $transStatus = strtolower((string)($response[$callbackConfig['trans_status_field']] ?? $response['trans_status'] ?? $status));
        $description = trim((string)($response[$callbackConfig['description_field']] ?? $response['message'] ?? 'Paiement en attente de confirmation.'));
        $transactionId = trim((string)($response[$callbackConfig['transaction_id_field']] ?? $response['transaction_id'] ?? ''));
        $financialInstitutionId = trim((string)($response[$callbackConfig['financial_institution_id_field']] ?? $response['financial_institution_id'] ?? ''));

        $isSuccess = in_array($transStatus, ['success', 'successful', 'paid', 'completed'], true);
        $isFailed = in_array($transStatus, ['failed', 'cancelled', 'canceled', 'rejected', 'error'], true);

        return [
            'result' => $isFailed ? 'error' : 'ok',
            'status' => $status !== '' ? $status : 'submitted',
            'trans_status' => $transStatus !== '' ? $transStatus : 'pending',
            'description' => $description !== '' ? $description : 'Paiement en attente de confirmation.',
            'transaction_id' => $transactionId !== '' ? $transactionId : null,
            'financial_institution_id' => $financialInstitutionId !== '' ? $financialInstitutionId : null,
            'order_status' => $isSuccess ? 'payée' : ($isFailed ? 'paiement_échoué' : 'paiement_en_attente'),
            'frontend_message' => $isSuccess
                ? 'Paiement confirmé.'
                : ($isFailed ? 'Le paiement a échoué.' : 'Paiement en cours de confirmation.'),
        ];
    }

    private function isValidCallbackSignature($rawBody, array $payload)
    {
        $hmacKey = trim((string)$this->config['freshpay']['hmac_key']);
        if ($hmacKey === '') {
            return true;
        }

        $provided = '';
        foreach ($this->config['freshpay']['callback']['signature_headers'] as $serverKey) {
            if (!empty($_SERVER[$serverKey])) {
                $provided = trim((string)$_SERVER[$serverKey]);
                break;
            }
        }

        if ($provided === '') {
            $field = $this->config['freshpay']['callback']['signature_field'];
            $provided = trim((string)($payload[$field] ?? ''));
        }

        if ($provided === '') {
            return false;
        }

        $computed = hash_hmac('sha256', (string)$rawBody, $hmacKey);
        return hash_equals($computed, $provided);
    }

    private function extractCallbackData(array $payload)
    {
        $field = $this->config['freshpay']['callback']['encrypted_field'];
        $mode = $this->config['freshpay']['callback']['decrypt_mode'];

        if ($mode === 'plain_json') {
            if (isset($payload[$field]) && is_array($payload[$field])) {
                return $payload[$field];
            }

            if (isset($payload[$field]) && is_string($payload[$field])) {
                $decoded = json_decode($payload[$field], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            return $payload;
        }

        // TODO FreshPay : compléter ici le déchiffrement exact dès validation du mode réel documenté.
        return $payload;
    }

    private function maskCustomerNumber($number)
    {
        $digits = preg_replace('/\D+/', '', (string)$number);
        if ($digits === '' || strlen($digits) <= 4) {
            return $digits;
        }

        return str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -4);
    }

    private function clearCheckoutSource($mode)
    {
        if ($mode === 'direct') {
            ohnous_clear_direct_checkout();
            return;
        }

        unset($_SESSION[ohnous_get_cart_session_key()]);
    }
}
