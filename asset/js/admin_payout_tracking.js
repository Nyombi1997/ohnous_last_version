(function ($) {
    var root = document.getElementById('payout_tracking');
    if (!root || root.dataset.final === '1') return;
    var reference = root.dataset.reference, timer = null, attempts = 0, maxAttempts = 120;
    var labels = {submitted:'Soumis',pending:'En attente',processing:'En attente',success:'Réussi',successful:'Réussi',paid:'Réussi',completed:'Réussi',failed:'Échoué',error:'Échoué',expired:'Expiré',cancelled:'Annulé',canceled:'Annulé',rejected:'Échoué',refused:'Échoué',declined:'Échoué'};
    function poll() {
        attempts++;
        $.get('/payout-verifier', {reference: reference}, function (data) {
            if (data.result !== 'ok') return;
            var status = String(data.status || 'pending').toLowerCase();
            $('#payout_tracking_status').text(labels[status] || status);
            $('#payout_tracking_message').text(data.description || 'Traitement en cours.');
            $('#payout_tracking_transaction').text(data.transaction_id || '—');
            $('#payout_tracking_freshpay').text(data.freshpay_reference || '—');
            $('#payout_tracking_operator').text(data.operator_reference || '—');
            if (data.final) {
                clearInterval(timer);
                $('#payout_tracking_title').text(['success','successful','paid','completed'].indexOf(status) >= 0 ? 'PayOut réussi' : 'PayOut terminé');
                $('#payout_tracking_icon').removeClass('is-pending').html('<i class="fa-solid fa-circle-check"></i>');
            }
        }, 'json');
        if (attempts >= maxAttempts && timer) clearInterval(timer);
    }
    timer = setInterval(poll, 5000); poll();
})(jQuery);
