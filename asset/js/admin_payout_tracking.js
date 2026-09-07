(function ($) {
    var root = document.getElementById('payout_tracking');
    if (!root || root.dataset.final === '1') return;
    var reference = root.dataset.reference, timer = null, attempts = 0, maxAttempts = 120;
    var labels = {pending_send:'À envoyer',send_unknown:'Résultat incertain',manual_review:'À rapprocher',send_rejected:'Envoi refusé',submitted:'Soumis',pending:'En attente',processing:'En attente',success:'Réussi',successful:'Réussi',paid:'Réussi',completed:'Réussi',failed:'Échoué',error:'Échoué',expired:'Expiré',cancelled:'Annulé',canceled:'Annulé',rejected:'Échoué',refused:'Échoué',declined:'Échoué'};
    function poll() {
        attempts++;
        $.get('/payout-verifier', {reference: reference}, function (data) {
            if (data.result !== 'ok') {
                $('#payout_tracking_message').text(data.msg || data.description || 'Vérification impossible.');
                if (data.final) clearInterval(timer);
                return;
            }
            var status = String(data.status || 'pending').toLowerCase();
            $('#payout_tracking_status').text(labels[status] || status);
            $('#payout_tracking_message').text(data.description || 'Traitement en cours.');
            $('#payout_tracking_transaction').text(data.transaction_id || '—');
            $('#payout_tracking_freshpay').text(data.freshpay_reference || '—');
            $('#payout_tracking_operator').text(data.operator_reference || '—');
            if (data.final) {
                clearInterval(timer);
                var successful = ['success','successful','paid','completed'].indexOf(status) >= 0;
                $('#payout_tracking_title').text(status === 'manual_review' ? 'PayOut à rapprocher' : (successful ? 'PayOut réussi' : 'PayOut non abouti'));
                $('#payout_tracking_icon').removeClass('is-pending').html('<i class="fa-solid ' + (successful ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i>');
            }
        }, 'json');
        if (attempts >= maxAttempts && timer) clearInterval(timer);
    }
    timer = setInterval(poll, 5000); poll();
})(jQuery);
