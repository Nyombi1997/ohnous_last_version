<?php

function ohnous_recipient_service(PDO $bdd)
{
    require_once __DIR__.'/../model/PayoutTransaction.php';
    require_once __DIR__.'/../service/MokoClient.php';
    require_once __DIR__.'/../service/MokoPayoutService.php';
    return new MokoPayoutService($bdd);
}

function ohnous_recipient_csrf($renew = false)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if ($renew || empty($_SESSION['recipient_csrf']) || time() - (int)($_SESSION['recipient_csrf_at'] ?? 0) > 7200) {
        $_SESSION['recipient_csrf'] = bin2hex(random_bytes(24));
        $_SESSION['recipient_csrf_at'] = time();
    }
    return $_SESSION['recipient_csrf'];
}

function ohnous_validate_recipient_csrf($token)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    return is_string($token) && !empty($_SESSION['recipient_csrf'])
        && hash_equals($_SESSION['recipient_csrf'], $token)
        && time() - (int)($_SESSION['recipient_csrf_at'] ?? 0) <= 7200;
}

function ohnous_register_recipient_on_activation(PDO $bdd, $boutiqueId, ?MokoPayoutService $service = null)
{
    try {
        require_once __DIR__.'/../model/PayoutTransaction.php';
        $profile = (new PayoutTransaction($bdd))->recipientProfile($boutiqueId);
        if (empty($profile['beneficiary']) || empty($profile['phone_number']) || empty($profile['operator'])) return;
        ($service ?? ohnous_recipient_service($bdd))->registerBoutiqueRecipient($boutiqueId);
    } catch (Throwable $e) {
        // L’activation reste acquise ; le rappel invite la boutique à reprendre l’enregistrement.
        error_log('Moko recipient activation boutique '.(int)$boutiqueId.': '.get_class($e));
    }
}
