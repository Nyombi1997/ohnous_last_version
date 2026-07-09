(function ($) {
    var config = window.ohnousPaymentTracking || {};
    var page = document.getElementById('payment_tracking_page');
    var title = document.getElementById('payment_tracking_title');
    var message = document.getElementById('payment_tracking_message');
    var icon = document.getElementById('payment_tracking_icon');
    var amount = document.getElementById('payment_tracking_amount');
    var method = document.getElementById('payment_tracking_method');
    var transaction = document.getElementById('payment_tracking_transaction');
    var transactionLine = document.getElementById('payment_tracking_transaction_line');
    var actions = document.getElementById('payment_tracking_actions');
    var progress = document.querySelector('.payment-tracking-progress');
    var reference = (config.reference || (page ? page.getAttribute('data-reference') : '') || '').trim();
    var pollTimer = null;
    var attempts = 0;
    var maxAttempts = 90;
    var intervalDelay = 4000;
    var successStates = ['success', 'successful', 'paid', 'completed'];
    var failedStates = ['failed', 'rejected', 'error', 'cancelled', 'canceled', 'refused', 'declined'];
    var expiredStates = ['expired', 'timeout', 'timed_out'];

    if (!page || reference === '') {
        return;
    }

    function normalize(value) {
        return String(value || '').toLowerCase();
    }

    function setActions(state) {
        if (!actions) {
            return;
        }

        actions.classList.remove('is-visible');
        actions.querySelectorAll('[data-payment-action]').forEach(function (item) {
            item.style.display = 'none';
        });

        if (state === 'success') {
            showAction('shop');
        } else if (state === 'failed' || state === 'expired' || state === 'timeout') {
            showAction('retry');
            showAction('checkout');
        }
    }

    function showAction(action) {
        var item = actions ? actions.querySelector('[data-payment-action="' + action + '"]') : null;
        if (item) {
            item.style.display = '';
            actions.classList.add('is-visible');
        }
    }

    function setState(state, text) {
        page.setAttribute('data-payment-state', state);
        icon.className = 'payment-tracking-status__icon is-' + state;

        if (state === 'success') {
            title.innerText = 'Paiement reçu';
            message.innerText = text || 'Votre paiement a bien été confirmé.';
        } else if (state === 'failed') {
            title.innerText = 'Paiement échoué';
            message.innerText = text || "Le paiement n'a pas pu être confirmé.";
        } else if (state === 'expired') {
            title.innerText = 'Paiement expiré';
            message.innerText = text || 'La transaction a expiré. Vous pouvez recommencer le paiement.';
        } else if (state === 'timeout') {
            title.innerText = 'Vérification interrompue';
            message.innerText = text || 'Le statut prend plus de temps que prévu. Vous pouvez réessayer.';
        } else {
            title.innerText = 'Paiement en cours';
            message.innerText = text || 'Nous vérifions la confirmation du paiement.';
        }

        if (progress) {
            progress.style.display = state === 'pending' ? '' : 'none';
        }
        setActions(state);
    }

    function updateDetails(data) {
        if (data.amount && amount) {
            amount.innerText = parseFloat(data.amount).toFixed(2) + ' ' + (data.currency || '');
        }

        if (data.payment_method && method) {
            method.innerText = data.payment_method === 'mobile_money' ? 'Mobile Money' : data.payment_method;
        }

        var transactionValue = data.transaction_id || data.provider_reference || data.transaction_number || '';
        if (transactionValue && transaction) {
            transaction.innerText = transactionValue;
            if (transactionLine) {
                transactionLine.classList.remove('is-muted');
            }
        }
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function applyStatus(data) {
        var status = normalize(data.trans_status || data.status);
        var text = data.description || data.msg || '';

        updateDetails(data);

        if (data.result === 'error' && status === '') {
            stopPolling();
            setState('failed', text || "Le statut du paiement n'a pas pu être récupéré.");
            return;
        }

        if (successStates.indexOf(status) !== -1) {
            stopPolling();
            setState('success', text);
            return;
        }

        if (failedStates.indexOf(status) !== -1) {
            stopPolling();
            setState('failed', text);
            return;
        }

        if (expiredStates.indexOf(status) !== -1) {
            stopPolling();
            setState('expired', text);
            return;
        }

        setState('pending', text || 'Paiement en cours de traitement.');
    }

    function verifyStatus() {
        attempts += 1;

        if (attempts > maxAttempts) {
            stopPolling();
            setState('timeout');
            return;
        }

        $.get('/paiement-verifier', { reference: reference }, function (data) {
            applyStatus(data || {});
        }, 'json').fail(function () {
            if (attempts >= 3) {
                stopPolling();
                setState('timeout', 'La vérification est momentanément indisponible. Vous pouvez réessayer.');
            }
        });
    }

    setState('pending', config.initialDescription);
    verifyStatus();
    pollTimer = setInterval(verifyStatus, intervalDelay);
})(jQuery);
