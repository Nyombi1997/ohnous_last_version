<?php
    $paymentReturn = $GLOBALS['payment_return'] ?? [];
    $payment = $paymentReturn['payment'] ?? null;
    $status = strtolower((string)($paymentReturn['status'] ?? 'pending'));
    $reference = (string)($paymentReturn['reference'] ?? '');
    $paymentMethodLabels = [
        'mobile_money' => 'Mobile Money',
        'visa' => 'Carte Visa',
    ];
    $paymentMethod = (string)($payment['payment_method'] ?? '');
    $paymentMethodLabel = $paymentMethodLabels[$paymentMethod] ?? ($paymentMethod !== '' ? $paymentMethod : 'Paiement en ligne');
    $amount = $payment ? number_format((float)$payment['amount'], 2, '.', ' ') : '';
    $currency = (string)($payment['currency'] ?? '');
    $transactionId = (string)($payment['freshpay_transaction_id'] ?? '');
    $description = (string)($payment['trans_status_description'] ?? "Votre paiement est en cours de traitement.");
?>
<div class="content_page">
    <section class="checkout-shell">
        <div class="checkout-main liquid-panel payment-return-panel" id="payment_tracking_page"
            data-reference="<?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?>"
            data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
            <div class="payment-tracking-card">
                <div class="payment-tracking-status" id="payment_tracking_status">
                    <div class="payment-tracking-status__icon is-pending" id="payment_tracking_icon">
                        <span class="payment-tracking-spinner"></span>
                        <i class="fa-solid fa-check"></i>
                        <i class="fa-solid fa-xmark"></i>
                        <i class="fa-solid fa-hourglass-end"></i>
                    </div>
                    <span class="checkout-eyebrow">Suivi du paiement</span>
                    <h1 id="payment_tracking_title">Paiement en cours</h1>
                    <p id="payment_tracking_message"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="payment-tracking-details">
                    <div class="checkout-summary__line">
                        <span>Montant</span>
                        <strong id="payment_tracking_amount"><?= htmlspecialchars(trim($amount . ' ' . $currency), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="checkout-summary__line">
                        <span>Mode de paiement</span>
                        <strong id="payment_tracking_method"><?= htmlspecialchars($paymentMethodLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="checkout-summary__line">
                        <span>Référence</span>
                        <strong id="payment_tracking_reference"><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="checkout-summary__line <?= $transactionId === '' ? 'is-muted' : '' ?>" id="payment_tracking_transaction_line">
                        <span>Transaction</span>
                        <strong id="payment_tracking_transaction"><?= htmlspecialchars($transactionId !== '' ? $transactionId : 'En attente', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>

                <div class="payment-tracking-progress" aria-hidden="true">
                    <span></span>
                </div>

                <div class="payment-return-card__actions" id="payment_tracking_actions">
                    <a href="/articles" class="btn_ohnous second" data-payment-action="shop">Continuer vos achats</a>
                    <a href="/checkout" class="btn_ohnous" data-payment-action="retry">Réessayer le paiement</a>
                    <a href="/checkout" class="btn_ohnous second" data-payment-action="checkout">Retour au checkout</a>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    window.ohnousPaymentTracking = {
        reference: <?= json_encode($reference, JSON_UNESCAPED_UNICODE) ?>,
        initialStatus: <?= json_encode($status, JSON_UNESCAPED_UNICODE) ?>,
        initialDescription: <?= json_encode($description, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="/asset/js/payment-return.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/payment-return.js") ?>" defer></script>
