<?php

class PaymentController
{
    private function bootDependencies()
    {
        include_once MODEL . 'bdd.php';
        include_once MODEL . 'select.php';
        include_once FONCTION . 'fonctions.php';
        include_once MODEL . 'PaymentTransaction.php';
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
}
