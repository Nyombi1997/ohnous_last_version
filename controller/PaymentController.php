<?php

class PaymentController
{
    private function bootDependencies()
    {
        include_once MODEL . 'bdd.php';
        include_once MODEL . 'select.php';
        include_once FONCTION . 'fonctions.php';
        include_once FONCTION . 'email.php';
        include_once MODEL . 'PaymentTransaction.php';
        include_once MODEL . 'PayoutTransaction.php';
        include_once SERVICE . 'OrderAmountService.php';
        include_once SERVICE . 'FreshPayService.php';

        return $GLOBALS['bdd'] ?? null;
    }

    private function buildPublicErrorPayload($scope, Throwable $e)
    {
        $errorCode = 'FP-' . strtoupper(substr(sha1($scope . '|' . $e->getMessage() . '|' . microtime(true)), 0, 10));
        $technicalMessage = trim((string)$e->getMessage());

        error_log($scope . ' [' . $errorCode . ']: ' . $technicalMessage);

        return [
            'result' => 'error',
            'msg' => "Le paiement n'a pas pu être initié.",
            'error_code' => $errorCode,
            'technical_error' => $technicalMessage,
            'shareable_error' => $errorCode . ' | ' . $technicalMessage,
        ];
    }

    public function startPayment()
    {
        $bdd = $this->bootDependencies();
        header('Content-Type: application/json; charset=utf-8');

        if (!validateHoneypot('checkout')) {
            ohnous_honeypot_neutral_json();
        }

        try {
            if (!$bdd instanceof PDO) {
                throw new RuntimeException("Connexion PDO introuvable après chargement de model/bdd.php.");
            }

            $service = new FreshPayService($bdd);
            $response = $service->initiateCheckoutPayment($_POST);
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode($this->buildPublicErrorPayload('FreshPay startPayment', $e), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        exit();
    }

    public function handleFreshPayCallback()
    {
        $bdd = $this->bootDependencies();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!$bdd instanceof PDO) {
                throw new RuntimeException("Connexion PDO introuvable après chargement de model/bdd.php.");
            }

            $service = new FreshPayService($bdd);
            $response = $service->handleCallback();
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Throwable $e) {
            error_log('FreshPay callback: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'result' => 'error',
                'msg' => 'Callback FreshPay invalide.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        exit();
    }

    public function verifyPaymentStatus()
    {
        $bdd = $this->bootDependencies();
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!$bdd instanceof PDO) {
                throw new RuntimeException("Connexion PDO introuvable après chargement de model/bdd.php.");
            }

            $service = new FreshPayService($bdd);
            $reference = trim((string)($_GET['reference'] ?? $_POST['reference'] ?? ''));
            $response = $service->verifyTransactionStatus($reference);
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (Throwable $e) {
            error_log('FreshPay verify: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'result' => 'error',
                'msg' => 'Vérification impossible.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        exit();
    }

    public function showReturnPage()
    {
        $bdd = $this->bootDependencies();

        if (!$bdd instanceof PDO) {
            http_response_code(500);
            echo 'Connexion base de données introuvable.';
            exit();
        }

        $reference = trim((string)($_GET['reference'] ?? ''));
        $transactionModel = new PaymentTransaction($bdd);
        $payment = $reference !== '' ? $transactionModel->findByReference($reference) : null;
        $GLOBALS['payment_return'] = [
            'reference' => $reference,
            'payment' => $payment,
            'status' => $payment['trans_status'] ?? ($payment['status'] ?? 'pending'),
        ];

        $view = new View('payment-return');
        $view->render('Ohnous | Retour paiement');
    }

    public function startPayout()
    {
        $bdd = $this->bootDependencies();
        header('Content-Type: application/json; charset=utf-8');
        try {
            ohnous_require_payout_permission(true);
            if (!ohnous_validate_csrf($_POST['csrf_token'] ?? '')) {
                http_response_code(419);
                echo json_encode(['result' => 'error', 'msg' => 'Session expirée. Rechargez la page puis réessayez.'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            if (!$bdd instanceof PDO) {
                throw new RuntimeException('Connexion PDO introuvable.');
            }
            echo json_encode((new FreshPayService($bdd))->initiatePayout($_POST), JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode($this->buildPublicErrorPayload('FreshPay startPayout', $e), JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    public function verifyPayoutStatus()
    {
        $bdd = $this->bootDependencies();
        header('Content-Type: application/json; charset=utf-8');
        try {
            ohnous_require_payout_permission(true);
            if (!$bdd instanceof PDO) {
                throw new RuntimeException('Connexion PDO introuvable.');
            }
            $reference = trim((string)($_GET['reference'] ?? $_POST['reference'] ?? ''));
            echo json_encode((new FreshPayService($bdd))->verifyPayoutStatus($reference), JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['result' => 'error', 'msg' => 'Vérification impossible.'], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    public function exportPayouts()
    {
        $bdd = $this->bootDependencies();
        ohnous_require_payout_permission();
        if (!$bdd instanceof PDO || !ohnous_table_exists('payout_transactions')) {
            http_response_code(503);
            echo 'Module PayOut indisponible.';
            exit();
        }

        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'status' => trim((string)($_GET['status'] ?? '')),
            'operator' => trim((string)($_GET['operator'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
        ];
        $rows = (new PayoutTransaction($bdd))->search($filters, null);
        $filename = 'rapport-payout-' . date('Y-m-d-His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        echo '<table border="1"><thead><tr>';
        foreach (['Référence interne','Bénéficiaire','Numéro','Opérateur','Montant','Devise','Statut','Date','Référence FreshPay','Référence opérateur','Transaction','Administrateur'] as $heading) {
            echo '<th>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ([$row['reference'], $row['beneficiary'], $row['phone_number'], $row['operator'], $row['amount'], $row['currency'], $row['status'], $row['created_at'], $row['freshpay_reference'] ?? '', $row['operator_reference'] ?? '', $row['transaction_id'] ?? '', $row['admin_name'] ?? ''] as $value) {
                echo '<td>' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        exit();
    }
}
