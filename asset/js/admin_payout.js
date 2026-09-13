(function ($) {
    var form = document.getElementById('admin_payout_form');
    if (!form) return;
    var recipient = new window.OhnousRecipientForm(form);
    var shop = document.getElementById('payout_boutique'), request = null;
    var report = $('#payout_boutique_report');
    function technical(value) {
        $('#moko_recipient_details').prop('hidden', !value).find('pre').text(value ? JSON.stringify(value, null, 2) : '');
    }
    $(shop).on('change', function () {
        if (request) request.abort();
        recipient.setProfile(null);
        recipient.setAvailable(false);
        technical(null);
        report.prop('hidden', !shop.value).attr('href', '/admin-payouts?boutique_id=' + encodeURIComponent(shop.value));
        if (!shop.value) return;
        var selected = shop.value;
        request = $.ajax({url:'/payout-boutique',data:{boutique_id:selected},dataType:'json',timeout:15000}).done(function (data) {
            if (shop.value !== selected) return;
            recipient.setProfile(data.profile);
            recipient.setAvailable(true);
            technical(data.technical);
        }).fail(function (xhr, status) {
            if (status !== 'abort') Swal.fire({icon:'error',title:'Coordonnées indisponibles',text:(xhr.responseJSON || {}).msg || 'Réessayez en sélectionnant la boutique.'});
        });
    });
    $(shop).trigger('change');
    $(form).on('recipient:updated', function () {
        var selected = shop.value;
        if (!selected) return;
        $.ajax({url:'/payout-boutique',data:{boutique_id:selected},dataType:'json',timeout:15000}).done(function (data) {
            if (shop.value !== selected) return;
            technical(data.technical);
            if (data.profile) recipient.setProfile(data.profile);
        });
    });
    $('#moko_diagnostic').on('click', function () {
        var button = $(this), old = button.html();
        if (button.prop('disabled')) return;
        button.prop('disabled', true).attr('aria-busy', 'true').text('Vérification en cours…');
        recipient.setBusy(true);
        $('#moko_connection_status').text('Connexion à Moko…');
        $('#moko_balances').empty();
        $('#moko_connection_details').prop('hidden', true).find('pre').empty();
        $.ajax({url:'/payout-diagnostic',type:'POST',dataType:'json',timeout:150000,data:{recipient_csrf:form.elements.recipient_csrf.value}}).done(function (data) {
            if (data.recipient_csrf) form.elements.recipient_csrf.value = data.recipient_csrf;
            if (data.result !== 'ok') { $('#moko_connection_status').text(data.msg || 'Diagnostic indisponible.'); return; }
            var health = data.diagnostic.health, balance = data.diagnostic.balance;
            $('#moko_connection_status').text(health.message + ' ' + balance.message);
            $('#moko_connection_details').prop('hidden', false).find('pre').text(JSON.stringify(data.diagnostic, null, 2));
            if (balance.ok && Array.isArray(balance.data.wallets)) {
                balance.data.wallets.forEach(function (wallet) {
                    var card = $('<div>');
                    $('<dt>').text(wallet.method + ' · ' + wallet.currency).appendTo(card);
                    $('<dd>').text('Disponible : ' + wallet.available).appendTo(card);
                    card.appendTo('#moko_balances');
                });
            } else if (balance.ok) {
                $('#moko_connection_status').append(document.createTextNode(' Format du solde non reconnu ; consultez le détail technique.'));
            }
        }).fail(function (xhr) { $('#moko_connection_status').text((xhr.responseJSON || {}).msg || 'Connexion Moko indisponible. Réessayez.'); }).always(function () {
            button.prop('disabled', false).removeAttr('aria-busy').html(old);
            recipient.setBusy(false);
        });
    });
    $(form).on('submit', function (event) {
        event.preventDefault();
        if (!recipient.canPay()) return;
        var payload = $(form).serialize();
        var button = form.querySelector('button[type="submit"]'), old = button.innerHTML;
        recipient.setBusy(true);
        button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';
        function failure(data) {
            var redirect = data.redirect || (data.reference ? '/admin-payout-suivi?reference=' + encodeURIComponent(data.reference) : '');
            Swal.fire({icon:'error',title:'PayOut impossible',text:data.msg || 'Le serveur ne répond pas. Conservez la même référence avant de réessayer.'}).then(function () { if (redirect) window.location.href = redirect; });
        }
        $.ajax({url:'/payout-demarrer',type:'POST',data:payload,dataType:'json',timeout:120000}).done(function (data) {
            if (data.result === 'ok') window.location.href = data.redirect || '/admin-payout-suivi?reference=' + encodeURIComponent(data.reference);
            else failure(data);
        }).fail(function (xhr) { failure(xhr.responseJSON || {}); }).always(function () {
            button.innerHTML = old;
            recipient.setBusy(false);
        });
    });
})(jQuery);
