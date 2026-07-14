<?php

class FreshPayService
{
    private $bdd;
    private $config;
    private $transactionModel;
    private $payoutModel;
    private $amountService;

    public function __construct(PDO $bdd)
    {
        $this->bdd = $bdd;
        $this->config = include CONFIG . 'payment.php';
        $this->transactionModel = new PaymentTransaction($bdd);
        $this->payoutModel = class_exists('PayoutTransaction') ? new PayoutTransaction($bdd) : null;
        $this->amountService = new OrderAmountService();
    }

    public function initiateCheckoutPayment(array $request)
    {
        ohnous_boot_checkout_session();

        if (!ohnous_is_payment_enabled()) {
            return ['result' => 'error', 'msg' => "Le mode de paiement est temporairement désactivé."];
        }

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
        $paymentOperator = strtolower(trim((string)($request['payment_operator'] ?? '')));
        $customerNumber = trim((string)($request['customer_number'] ?? $telephone));
        $firstname = trim((string)($request['firstname'] ?? 'Client'));
        $lastname = trim((string)($request['lastname'] ?? 'OhNous'));
        $gatewayProfile = $this->resolveGatewayCustomerProfile();

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

        if ($paymentMethod === 'mobile_money' && !$this->isSupportedMobileMoneyOperator($paymentOperator)) {
            return ['result' => 'error', 'msg' => "Veuillez sélectionner un opérateur Mobile Money pris en charge."];
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
            'payment_operator' => $paymentOperator,
            'customer_number' => $customerNumber,
            'total' => $totals['total'],
            'currency' => $totals['currency'],
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
            // FreshPay attend ici le payload contractuel communiqué par leur équipe.
            'amount' => $this->formatFreshPayAmount($totals['total']),
            'currency' => $totals['currency'],
            'action' => 'debit',
            'customer_number' => $customerNumber,
            'firstname' => $gatewayProfile['firstname'],
            'lastname' => $gatewayProfile['lastname'],
            'email' => $gatewayProfile['email'],
            'reference' => $reference,
            'method' => $this->resolveFreshPayMethod($paymentMethod, $paymentOperator),
            'callback_url' => $this->resolveCallbackUrl(),
        ];

        $transactionId = $this->transactionModel->create([
            'order_id' => (int)$order['order_id'],
            'provider' => 'freshpay',
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'freshpay_transaction_id' => null,
            'financial_institution_id' => null,
            'customer_number' => $this->maskCustomerNumber($customerNumber),
            'amount_ht' => number_format((float)$totals['amount_ht'], 2, '.', ''),
            'payment_fee_rate' => number_format((float)$totals['payment_fee_rate'], 4, '.', ''),
            'payment_fee_amount' => number_format((float)$totals['payment_fee_amount'], 2, '.', ''),
            'amount' => number_format((float)$totals['total'], 2, '.', ''),
            'currency' => $totals['currency'],
            'request_payload' => json_encode($requestPayload, JSON_UNESCAPED_UNICODE),
            'response_payload' => null,
            'callback_payload' => null,
            'status' => 'initiated',
            'trans_status' => 'pending',
            'trans_status_description' => 'Paiement initié côté OhNous, en attente de la confirmation FreshPay.',
        ]);

        if ($paymentMethod === 'visa') {
            $this->transactionModel->updateById($transactionId, [
                'status' => 'todo',
                'trans_status' => 'todo',
                'trans_status_description' => 'TODO FreshPay : compléter les paramètres FreshPay Visa dès que le flux officiel est confirmé.',
            ]);

            return [
                'result' => 'error',
                'msg' => "La structure Visa est prête, mais les paramètres FreshPay Visa doivent encore être confirmés dans la configuration.",
                'reference' => $reference,
            ];
        }

        $remoteResponse = $this->sendApiRequest('initiate', $requestPayload);
        $normalized = $this->normalizeGatewayResponse($remoteResponse, [
            'source' => 'initiate',
            'default_description' => 'Demande de paiement reçue par FreshPay. Confirmation finale en attente.',
        ]);
        $this->clearCheckoutSource($context['mode']);

        $this->transactionModel->updateById($transactionId, [
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'trans_status_description' => $normalized['description'],
            'freshpay_transaction_id' => $normalized['transaction_id'],
            'provider_reference' => $normalized['provider_reference'],
            'transaction_number' => $normalized['transaction_number'],
            'financial_institution_id' => $normalized['financial_institution_id'],
            'response_payload' => json_encode($remoteResponse, JSON_UNESCAPED_UNICODE),
        ]);

        update_bdd($this->bdd, 'commandes', [
            'statut' => $normalized['order_status'],
        ], "id = '" . (int)$order['order_id'] . "'");

        if ($this->isSuccessStatus($normalized['trans_status'])) {
            $this->sendReceiptIfConfirmed($transactionId);
        }

        return [
            'result' => $normalized['result'],
            'msg' => $normalized['frontend_message'],
            'reference' => $reference,
            'order_number' => $order['order_number'],
            'payment_status' => $normalized['trans_status'],
            'transaction_id' => $normalized['transaction_id'],
            'redirect' => '/paiement-retour?reference=' . rawurlencode($reference),
        ];
    }

    public function initiatePayout(array $request)
    {
        if (!$this->payoutModel || !ohnous_table_exists('payout_transactions')) {
            return ['result' => 'error', 'msg' => 'La table SQL des PayOut est manquante. Appliquez la migration du README.md.'];
        }

        $phone = preg_replace('/[^0-9+]/', '', trim((string)($request['phone_number'] ?? '')));
        $operator = strtolower(trim((string)($request['operator'] ?? '')));
        $amount = round((float)($request['amount'] ?? 0), 2);
        $currency = strtoupper(trim((string)($request['currency'] ?? $this->config['currency'])));
        $reason = trim((string)($request['reason'] ?? ''));
        $beneficiary = trim((string)($request['beneficiary'] ?? ''));
        $reference = trim((string)($request['reference'] ?? ''));

        if (!preg_match('/^\+[1-9][0-9]{7,14}$/', $phone)) {
            return ['result' => 'error', 'msg' => 'Le numéro doit être valide et au format international.'];
        }
        if (!$this->isSupportedMobileMoneyOperator($operator)) {
            return ['result' => 'error', 'msg' => 'Opérateur Mobile Money non pris en charge.'];
        }
        if ($amount <= 0 || $reason === '') {
            return ['result' => 'error', 'msg' => 'Le montant et le motif sont obligatoires.'];
        }
        if ($reference === '') {
            $reference = 'PO-' . date('YmdHis') . '-' . mt_rand(100, 999);
        }
        if ($this->payoutModel->findByReference($reference)) {
            return ['result' => 'error', 'msg' => 'Cette référence existe déjà.'];
        }

        $profile = $this->resolveGatewayCustomerProfile();
        $payload = [
            'merchant_id' => (string)$this->config['freshpay']['merchant_id'],
            'merchant_secrete' => (string)$this->config['freshpay']['merchant_secret'],
            'amount' => $this->formatFreshPayAmount($amount),
            'currency' => $currency,
            'action' => (string)($this->config['freshpay']['payout']['action'] ?? 'credit'),
            'customer_number' => $phone,
            'firstname' => $beneficiary !== '' ? $beneficiary : $profile['firstname'],
            'lastname' => $profile['lastname'],
            'email' => $profile['email'],
            'reference' => $reference,
            'method' => $this->resolveFreshPayMethod('mobile_money', $operator),
            'callback_url' => $this->resolveCallbackUrl(),
            'description' => $reason,
        ];
        $id = $this->payoutModel->create([
            'reference' => $reference, 'beneficiary' => $beneficiary, 'phone_number' => $phone,
            'operator' => $operator, 'amount' => number_format($amount, 2, '.', ''), 'currency' => $currency,
            'reason' => $reason, 'status' => 'pending', 'status_description' => 'PayOut en attente de soumission.',
            'request_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $response = $this->sendApiRequest('initiate', $payload);
        $normalized = $this->normalizeGatewayResponse($response, ['source' => 'initiate', 'default_description' => 'PayOut soumis à FreshPay.']);
        $this->payoutModel->updateById($id, [
            'status' => $normalized['trans_status'], 'status_description' => $normalized['description'],
            'freshpay_reference' => $normalized['provider_reference'], 'transaction_id' => $normalized['transaction_id'],
            'response_payload' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);
        return ['result' => $normalized['result'], 'msg' => $normalized['frontend_message'], 'reference' => $reference, 'status' => $normalized['trans_status']];
    }

    public function verifyPayoutStatus($reference)
    {
        if (!$this->payoutModel || !ohnous_table_exists('payout_transactions')) {
            return ['result' => 'error', 'msg' => 'Module PayOut indisponible.'];
        }
        $payout = $this->payoutModel->findByReference(trim((string)$reference));
        if (!$payout) {
            return ['result' => 'error', 'msg' => 'PayOut introuvable.'];
        }
        $payload = [
            'merchant_id' => (string)$this->config['freshpay']['merchant_id'],
            'merchant_secrete' => (string)$this->config['freshpay']['merchant_secret'],
            'action' => 'verify', 'reference' => $payout['reference'],
        ];
        $response = $this->sendApiRequest('status', $payload);
        $normalized = $this->normalizeGatewayResponse($response, ['source' => 'verify', 'default_description' => 'Statut PayOut synchronisé.']);
        $this->payoutModel->updateById((int)$payout['id'], [
            'status' => $normalized['trans_status'], 'status_description' => $normalized['description'],
            'freshpay_reference' => $normalized['provider_reference'] ?: $payout['freshpay_reference'],
            'transaction_id' => $normalized['transaction_id'] ?: $payout['transaction_id'],
            'response_payload' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ]);
        return ['result' => 'ok', 'status' => $normalized['trans_status'], 'description' => $normalized['description'], 'reference' => $payout['reference']];
    }

    public function handleCallback()
    {
        $rawBody = file_get_contents('php://input');
        $decoded = json_decode($rawBody, true);

        // Pour désactiver tous les logs callback, commente simplement la ligne ci-dessous.
        $callbackLogger = function ($stage, $httpStatus, array $context = []) use ($rawBody, $decoded) {
            $this->logFreshPayCallbackDebug($stage, $httpStatus, $rawBody, is_array($decoded) ? $decoded : null, $context);
        };

        if (!is_array($decoded)) {
            http_response_code(400);
            $callbackLogger('invalid_json', 400, [
                'message' => 'Le callback FreshPay doit être envoyé en JSON.',
            ]);
            return [
                'result' => 'error',
                'msg' => 'Le callback FreshPay doit être envoyé en JSON.'
            ];
        }

        $payload = $decoded;
        $signature = $this->extractProvidedCallbackSignature($payload);
        if ($signature === '') {
            http_response_code(400);
            $callbackLogger('missing_signature', 400, [
                'message' => 'Signature callback absente.',
                'payload' => $payload,
            ]);
            return [
                'result' => 'error',
                'msg' => 'Signature callback absente.'
            ];
        }

        if (!$this->isValidCallbackSignature($rawBody, $payload)) {
            http_response_code(401);
            $callbackLogger('invalid_signature', 401, [
                'message' => 'Signature callback invalide.',
                'payload' => $payload,
            ]);
            return [
                'result' => 'error',
                'msg' => 'Signature callback invalide.'
            ];
        }

        try {
            $data = $this->extractCallbackData($payload);
        } catch (RuntimeException $e) {
            http_response_code(400);
            $callbackLogger('invalid_callback_data', 400, [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return [
                'result' => 'error',
                'msg' => $e->getMessage(),
            ];
        }

        $reference = trim((string)($data['Reference'] ?? $data['reference'] ?? $payload['reference'] ?? ''));
        if ($reference === '') {
            http_response_code(422);
            $callbackLogger('missing_reference', 422, [
                'message' => 'Référence callback manquante.',
                'payload' => $payload,
                'data' => $data,
            ]);
            return [
                'result' => 'error',
                'msg' => 'Référence callback manquante.'
            ];
        }

        $transaction = $this->transactionModel->findByReference($reference);
        if (!$transaction) {
            http_response_code(404);
            $callbackLogger('transaction_not_found', 404, [
                'message' => 'Transaction introuvable.',
                'reference' => $reference,
                'payload' => $payload,
                'data' => $data,
            ]);
            return [
                'result' => 'error',
                'msg' => 'Transaction introuvable.'
            ];
        }

        $normalized = $this->normalizeGatewayResponse($data, [
            'source' => 'callback',
        ]);
        $this->transactionModel->updateById((int)$transaction['id'], [
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'trans_status_description' => $normalized['description'],
            'freshpay_transaction_id' => $normalized['transaction_id'] ?: $transaction['freshpay_transaction_id'],
            'provider_reference' => $normalized['provider_reference'] ?: ($transaction['provider_reference'] ?? null),
            'transaction_number' => $normalized['transaction_number'] ?: ($transaction['transaction_number'] ?? null),
            'financial_institution_id' => $normalized['financial_institution_id'] ?: $transaction['financial_institution_id'],
            'callback_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'response_payload' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        update_bdd($this->bdd, 'commandes', [
            'statut' => $normalized['order_status'],
        ], "id = '" . (int)$transaction['order_id'] . "'");

        if ($this->isSuccessStatus($normalized['trans_status'])) {
            $this->sendReceiptIfConfirmed((int)$transaction['id']);
        }

        http_response_code(200);
        $callbackLogger('callback_processed', 200, [
            'message' => 'Callback FreshPay traité.',
            'reference' => $reference,
            'normalized' => $normalized,
            'payload' => $payload,
            'data' => $data,
        ]);
        return [
            'result' => 'ok',
            'msg' => 'Callback FreshPay traité.',
            'reference' => $reference,
            'trans_status' => $normalized['trans_status'],
            'data' => $data,
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
            'merchant_id' => (string)$this->config['freshpay']['merchant_id'],
            'merchant_secrete' => (string)$this->config['freshpay']['merchant_secret'],
            'action' => 'verify',
            'reference' => $reference,
        ];

        $remoteResponse = $this->sendApiRequest('status', $requestPayload);
        $normalized = $this->normalizeGatewayResponse($remoteResponse, [
            'source' => 'verify',
            'default_description' => 'Statut récupéré depuis FreshPay.',
        ]);

        $this->transactionModel->updateById((int)$transaction['id'], [
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'trans_status_description' => $normalized['description'],
            'freshpay_transaction_id' => $normalized['transaction_id'] ?: $transaction['freshpay_transaction_id'],
            'provider_reference' => $normalized['provider_reference'] ?: ($transaction['provider_reference'] ?? null),
            'transaction_number' => $normalized['transaction_number'] ?: ($transaction['transaction_number'] ?? null),
            'financial_institution_id' => $normalized['financial_institution_id'] ?: $transaction['financial_institution_id'],
            'response_payload' => json_encode($remoteResponse, JSON_UNESCAPED_UNICODE),
        ]);

        update_bdd($this->bdd, 'commandes', [
            'statut' => $normalized['order_status'],
        ], "id = '" . (int)$transaction['order_id'] . "'");

        if ($this->isSuccessStatus($normalized['trans_status'])) {
            $this->sendReceiptIfConfirmed((int)$transaction['id']);
        }

        $updatedTransaction = $this->transactionModel->findByReference($reference) ?: $transaction;
        $order = only_select('commandes', "id = " . (int)$transaction['order_id'], null, null);

        return [
            'result' => 'ok',
            'msg' => 'Statut synchronisé.',
            'reference' => $reference,
            'status' => $normalized['status'],
            'trans_status' => $normalized['trans_status'],
            'description' => $normalized['description'],
            'transaction_id' => $updatedTransaction['freshpay_transaction_id'] ?? null,
            'provider_reference' => $updatedTransaction['provider_reference'] ?? null,
            'transaction_number' => $updatedTransaction['transaction_number'] ?? null,
            'payment_method' => $updatedTransaction['payment_method'] ?? null,
            'amount' => $updatedTransaction['amount'] ?? null,
            'currency' => $updatedTransaction['currency'] ?? null,
            'order_number' => $order['order_number'] ?? null,
        ];
    }

    private function createOrder(array $context, array $account, array $customer, array $totals)
    {
        $orderNumber = 'OHN-' . date('YmdHis') . '-' . mt_rand(100, 999);

        $orderData = [
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
        ];

        if (ohnous_column_exists('commandes', 'frais_paiement')) {
            $orderData['frais_paiement'] = $totals['payment_fee_amount'];
        }

        insert_bdd($this->bdd, 'commandes', $orderData);

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

    private function resolveFreshPayMethod($paymentMethod, $paymentOperator = '')
    {
        $map = $this->config['freshpay']['method_map'];

        if ($paymentMethod === 'mobile_money') {
            $operatorKey = strtolower(trim((string)$paymentOperator));
            return $map[$operatorKey] ?? $operatorKey;
        }

        return $map[$paymentMethod] ?? $paymentMethod;
    }

    private function isSupportedMobileMoneyOperator($paymentOperator)
    {
        return in_array((string)$paymentOperator, ['airtel', 'orange', 'mpesa', 'afrimoney'], true);
    }

    private function resolveCallbackUrl()
    {
        // FreshPay a demandé un callback_url vide dans le payload actuel.
        return (string)($this->config['freshpay']['callback_url'] ?? '');
    }

    private function resolveGatewayCustomerProfile()
    {
        $profile = $this->config['freshpay']['customer_profile'] ?? [];

        return [
            'firstname' => trim((string)($profile['firstname'] ?? 'Edo')),
            'lastname' => trim((string)($profile['lastname'] ?? 'systeme')),
            'email' => trim((string)($profile['email'] ?? 'edosysteme@gmail.com')),
        ];
    }

    private function formatFreshPayAmount($amount)
    {
        $formatted = number_format((float)$amount, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function logFreshPayCallbackDebug($stage, $httpStatus, $rawBody, $decodedBody = null, array $context = [])
    {
        $logFile = ROOT . 'freshpay-callback.log';

        $entry = [
            'logged_at' => date('Y-m-d H:i:s'),
            'stage' => $stage,
            'http_status' => (int)$httpStatus,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'raw_body' => $rawBody,
            'decoded_body' => $decodedBody,
            'context' => $context,
        ];

        error_log(
            json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            3,
            $logFile
        );
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

        $decoded = json_decode($responseBody, true);
        if (is_array($decoded)) {
            $decoded['_http_status'] = $statusCode;
            if ($statusCode >= 400 && empty($decoded['Status']) && empty($decoded['status'])) {
                $decoded['Status'] = 'error';
            }
            return $decoded;
        }

        if ($statusCode >= 400) {
            return [
                '_http_status' => $statusCode,
                'Status' => 'error',
                'Trans_Status' => 'error',
                'Trans_Status_Description' => trim(mb_substr(preg_replace('/\s+/', ' ', (string)$responseBody), 0, 500)),
                'raw_body' => $responseBody,
            ];
        }

        return [
            '_http_status' => $statusCode,
            'raw_body' => $responseBody,
        ];
    }

    private function normalizeGatewayResponse(array $response, array $options = [])
    {
        $callbackConfig = $this->config['freshpay']['callback'];
        $source = $options['source'] ?? 'generic';

        $status = strtolower(trim((string)($response[$callbackConfig['status_field']] ?? $response['status'] ?? 'submitted')));
        $transStatusRaw = trim((string)($response[$callbackConfig['trans_status_field']] ?? $response['trans_status'] ?? ''));
        $description = trim((string)($response[$callbackConfig['description_field']] ?? $response['message'] ?? $response['Message'] ?? $response['error'] ?? $response['Error'] ?? $response['Comment'] ?? ($options['default_description'] ?? 'Paiement en attente de confirmation.')));
        $transactionId = trim((string)($response[$callbackConfig['transaction_id_field']] ?? $response['transaction_id'] ?? $response['PayDRC_Reference'] ?? ''));
        $financialInstitutionId = trim((string)($response[$callbackConfig['financial_institution_id_field']] ?? $response['financial_institution_id'] ?? ''));
        $providerReference = trim((string)($response['Provider_Reference'] ?? $response['provider_reference'] ?? $response['FreshPay_Reference'] ?? $response['PayDRC_Reference'] ?? $transactionId));
        $transactionNumber = trim((string)($response['Transaction_Number'] ?? $response['transaction_number'] ?? $response['Trans_Number'] ?? $response['trans_number'] ?? $financialInstitutionId));

        $transStatus = strtolower($transStatusRaw);
        if ($transStatus === '') {
            $transStatus = 'pending';
        }

        $isSuccess = in_array($transStatus, ['success', 'successful', 'paid', 'completed'], true);
        $isFailed = in_array($transStatus, ['failed', 'cancelled', 'canceled', 'rejected', 'refused', 'declined', 'error', 'expired', 'refunded'], true);
        $statusAcknowledged = in_array($status, ['success', 'submitted', 'accepted'], true);
        $description = $this->resolveGatewayDescription($description, $response);

        if (!$isSuccess && !$isFailed && $source === 'initiate' && $statusAcknowledged) {
            $transStatus = 'pending';
        }

        return [
            'result' => $isFailed ? 'error' : 'ok',
            'status' => $status !== '' ? $status : 'submitted',
            'trans_status' => $transStatus !== '' ? $transStatus : 'pending',
            'description' => $description !== '' ? $description : 'Paiement en attente de confirmation.',
            'transaction_id' => $transactionId !== '' ? $transactionId : null,
            'provider_reference' => $providerReference !== '' ? $providerReference : null,
            'transaction_number' => $transactionNumber !== '' ? $transactionNumber : null,
            'financial_institution_id' => $financialInstitutionId !== '' ? $financialInstitutionId : null,
            'order_status' => $isSuccess ? 'payée' : ($isFailed ? 'échouée' : 'paiement_en_attente'),
            'frontend_message' => $isSuccess
                ? 'Paiement réussi'
                : ($isFailed ? $description : 'Paiement en cours de confirmation.'),
        ];
    }

    private function resolveGatewayDescription($description, array $response)
    {
        $description = trim((string)$description);
        $code = strtolower(trim((string)($response['Code'] ?? $response['code'] ?? $response['Error_Code'] ?? $response['error_code'] ?? '')));
        $haystack = strtolower($description . ' ' . $code);

        if (strpos($haystack, 'insufficient') !== false || strpos($haystack, 'solde') !== false || strpos($haystack, 'fund') !== false) {
            return "Le paiement n'a pas pu être initié car votre compte Mobile Money ne dispose pas d'un solde suffisant.";
        }

        if (strpos($haystack, 'refus') !== false || strpos($haystack, 'reject') !== false || strpos($haystack, 'declin') !== false || strpos($haystack, 'cancel') !== false) {
            return "Le paiement Mobile Money a été refusé. Vérifiez la notification reçue sur votre téléphone puis réessayez.";
        }

        return $description !== '' ? $description : 'Paiement en attente de confirmation.';
    }

    private function isSuccessStatus($status)
    {
        return in_array(strtolower((string)$status), ['success', 'successful', 'paid', 'completed'], true);
    }

    private function sendReceiptIfConfirmed($transactionId)
    {
        if (!function_exists('ohnous_send_payment_receipt_email') || !ohnous_column_exists('payment_transactions', 'receipt_sent_at')) {
            return false;
        }

        $payment = null;
        $stmt = $this->bdd->prepare("SELECT * FROM payment_transactions WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$transactionId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment || !empty($payment['receipt_sent_at']) || !$this->isSuccessStatus($payment['trans_status'])) {
            return false;
        }

        $order = only_select('commandes', "id = " . (int)$payment['order_id'], null, null);
        if (!$order || empty($order['email']) || !filter_var($order['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $items = $this->transactionModel->getOrderItems((int)$payment['order_id']);
        if (!ohnous_send_payment_receipt_email($payment, $order, $items)) {
            return false;
        }

        $this->transactionModel->updateById((int)$payment['id'], [
            'receipt_sent_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    private function isValidCallbackSignature($rawBody, array $payload)
    {
        $hmacKey = trim((string)$this->config['freshpay']['hmac_key']);
        if ($hmacKey === '') {
            return true;
        }

        $provided = $this->extractProvidedCallbackSignature($payload);
        if ($provided === '') {
            return false;
        }

        $signedMessage = $this->extractSignedCallbackMessage($rawBody, $payload);
        $computed = hash_hmac('sha256', $signedMessage, $hmacKey);
        return hash_equals($computed, $provided);
    }

    private function extractCallbackData(array $payload)
    {
        $field = $this->config['freshpay']['callback']['encrypted_field'];
        $mode = strtolower(trim((string)$this->config['freshpay']['callback']['decrypt_mode']));

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

        if (!isset($payload[$field]) || !is_string($payload[$field]) || trim($payload[$field]) === '') {
            throw new RuntimeException('Le body callback ne contient pas le champ chiffré attendu.');
        }

        if ($mode !== 'aes') {
            throw new RuntimeException('Mode de déchiffrement FreshPay non pris en charge.');
        }

        $encryptedData = trim($payload[$field]);
        $decodedData = base64_decode($encryptedData, true);
        if ($decodedData === false) {
            throw new RuntimeException('Le body callback FreshPay n’est pas un payload Base64 valide.');
        }

        $cipher = trim((string)$this->config['freshpay']['callback']['decrypt_cipher']);
        if ($cipher === '') {
            throw new RuntimeException('Cipher FreshPay manquant pour le callback.');
        }

        $key = $this->resolveCallbackSecretKey($cipher);
        $iv = $this->resolveCallbackIv($payload, $cipher, $key);
        $decrypted = openssl_decrypt($decodedData, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new RuntimeException('Le déchiffrement du callback FreshPay a échoué.');
        }

        $decoded = json_decode($decrypted, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Le callback FreshPay déchiffré n’est pas un JSON valide.');
        }

        return $decoded;
    }

    private function resolveCallbackSecretKey($cipher)
    {
        $secretKey = (string)$this->config['freshpay']['secret_key'];
        if ($secretKey === '') {
            throw new RuntimeException('FRESHPAY_SECRET_KEY4 est manquante pour le callback.');
        }

        $expectedLength = $this->resolveCipherKeyLength($cipher);
        if ($expectedLength === 0) {
            // TODO FreshPay : confirmer la longueur exacte de clé si un autre cipher est livré en production.
            return $secretKey;
        }

        if (strlen($secretKey) !== $expectedLength) {
            throw new RuntimeException('La clé de déchiffrement FreshPay ne correspond pas à la longueur attendue pour ' . $cipher . '.');
        }

        return $secretKey;
    }

    private function resolveCallbackIv(array $payload, $cipher, $key)
    {
        $ivLength = openssl_cipher_iv_length($cipher);
        if ($ivLength <= 0) {
            throw new RuntimeException('Longueur IV FreshPay invalide pour le cipher configuré.');
        }

        $ivField = $this->config['freshpay']['callback']['decrypt_iv_field'];
        $payloadIv = $payload[$ivField] ?? null;
        if (is_string($payloadIv) && trim($payloadIv) !== '') {
            $candidate = trim($payloadIv);
            $decoded = base64_decode($candidate, true);
            if ($decoded !== false && strlen($decoded) === $ivLength) {
                return $decoded;
            }

            if (strlen($candidate) === $ivLength) {
                return $candidate;
            }
        }

        // TODO FreshPay : la doc publique parle d’AES-256-CBC alors que l’exemple PHP réutilise SECRET_KEY comme IV.
        return substr($key, 0, $ivLength);
    }

    private function resolveCipherKeyLength($cipher)
    {
        $cipher = strtoupper(trim((string)$cipher));
        if (strpos($cipher, 'AES-128-') === 0) {
            return 16;
        }

        if (strpos($cipher, 'AES-256-') === 0) {
            return 32;
        }

        return 0;
    }

    private function extractProvidedCallbackSignature(array $payload)
    {
        foreach ($this->config['freshpay']['callback']['signature_headers'] as $serverKey) {
            if (!empty($_SERVER[$serverKey])) {
                return trim((string)$_SERVER[$serverKey]);
            }
        }

        $field = $this->config['freshpay']['callback']['signature_field'];
        return trim((string)($payload[$field] ?? ''));
    }

    private function extractSignedCallbackMessage($rawBody, array $payload)
    {
        $field = $this->config['freshpay']['callback']['encrypted_field'];
        if (isset($payload[$field]) && is_string($payload[$field]) && trim($payload[$field]) !== '') {
            return trim($payload[$field]);
        }

        return (string)$rawBody;
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
