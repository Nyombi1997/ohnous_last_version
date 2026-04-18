<?php
    $paymentReturn = $GLOBALS['payment_return'] ?? [];
    $payment = $paymentReturn['payment'] ?? null;
    $status = strtolower((string)($paymentReturn['status'] ?? 'pending'));
    $callbackPayload = null;
    if (!empty($payment['callback_payload'])) {
        $decodedCallbackPayload = json_decode((string)($payment['callback_payload'] ?? ''), true);
        $callbackPayload = is_array($decodedCallbackPayload) ? $decodedCallbackPayload : $payment['callback_payload'];
    }
    $responsePayload = null;
    if (!empty($payment['response_payload'])) {
        $decodedResponsePayload = json_decode((string)($payment['response_payload'] ?? ''), true);
        $responsePayload = is_array($decodedResponsePayload) ? $decodedResponsePayload : $payment['response_payload'];
    }
    $isSuccess = in_array($status, ['success', 'successful', 'paid', 'completed'], true);
    $isFailed = in_array($status, ['failed', 'cancelled', 'canceled', 'rejected', 'error'], true);
    $title = $isSuccess ? '✅ Paiement réussi' : ($isFailed ? '❌ Paiement refusé' : 'Paiement en attente');
    $description = $payment['trans_status_description'] ?? ($isSuccess
        ? "Votre paiement a bien été confirmé."
        : ($isFailed ? "Le paiement n'a pas pu être confirmé." : "Votre paiement est en cours de confirmation."));
?>
<div class="content_page">
    <section class="checkout-shell">
        <div class="checkout-main liquid-panel payment-return-panel">
            <div class="checkout-main__head">
                <div>
                    <span class="checkout-eyebrow">FreshPay</span>
                    <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p><?= htmlspecialchars((string)$description, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <a href="/checkout" class="btn_ohnous second">Retourner au checkout</a>
            </div>

            <div class="payment-return-card">
                <div class="checkout-summary__line">
                    <span>Référence</span>
                    <strong><?= htmlspecialchars((string)($paymentReturn['reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <?php if($payment): ?>
                    <?php if(!empty($payment['freshpay_transaction_id'])): ?>
                        <div class="checkout-summary__line">
                            <span>Transaction ID</span>
                            <strong><?= htmlspecialchars((string)$payment['freshpay_transaction_id'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="checkout-summary__line">
                        <span>Montant</span>
                        <strong><?= number_format((float)$payment['amount'], 2, '.', ' ') ?> <?= htmlspecialchars((string)($payment['currency'] ?? 'USD'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="checkout-summary__line">
                        <span>Statut</span>
                        <strong><?= htmlspecialchars((string)$payment['trans_status'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                <?php endif; ?>

                <div class="payment-return-card__actions">
                    <button type="button" class="btn_ohnous" id="payment_verify_button" data-reference="<?= htmlspecialchars((string)($paymentReturn['reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">Vérifier maintenant</button>
                    <a href="/articles" class="btn_ohnous second">Continuer vos achats</a>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    (function ($) {
        var button = document.getElementById('payment_verify_button');
        var freshPayDebugData = {
            reference: <?= json_encode((string)($paymentReturn['reference'] ?? ''), JSON_UNESCAPED_UNICODE) ?>,
            callbackPayload: <?= json_encode($callbackPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            responsePayload: <?= json_encode($responsePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        };

        // Pour masquer ces logs plus tard, commente simplement ce bloc.
        console.log('[FreshPay][payment-return]', freshPayDebugData);
        if (freshPayDebugData.callbackPayload) {
            console.log('[FreshPay][callback_payload]', freshPayDebugData.callbackPayload);
        }
        if (freshPayDebugData.responsePayload) {
            console.log('[FreshPay][response_payload]', freshPayDebugData.responsePayload);
        }

        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            var reference = button.getAttribute('data-reference') || '';
            if (reference === '') {
                return;
            }

            button.setAttribute('disabled', '');
            $.get('/paiement-verifier', { reference: reference }, function (data) {
                var successStates = ['success', 'successful', 'paid', 'completed'];
                var failedStates = ['failed', 'rejected', 'error', 'cancelled', 'canceled'];
                var icon = 'info';
                // Pour masquer ce log plus tard, commente simplement cette ligne.
                console.log('[FreshPay][verifyPaymentStatus]', data);

                if (successStates.indexOf(data.trans_status) !== -1) {
                    icon = 'success';
                } else if (failedStates.indexOf(data.trans_status) !== -1) {
                    icon = 'error';
                }

                Swal.fire({
                    icon: icon,
                    title: data.msg || 'Vérification terminée.',
                    text: data.trans_status ? ('Statut : ' + data.trans_status) : '',
                    confirmButtonColor: '#6775d6'
                }).then(function () {
                    window.location.reload();
                });
            }, 'json').fail(function (xhr) {
                // Pour masquer ce log plus tard, commente simplement cette ligne.
                console.log('[FreshPay][verifyPaymentStatus][error]', {
                    status: xhr.status,
                    raw: xhr.responseText || ''
                });
            }, 'json').always(function () {
                button.removeAttribute('disabled');
            });
        });
    })(jQuery);
</script>
