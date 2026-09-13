(function ($) {
    var form = document.getElementById('admin_payout_form');
    if (!form) return;
    var recipient = new window.OhnousRecipientForm(form);
    var shop = document.getElementById('payout_boutique'), request = null;
    var report = $('#payout_boutique_report');
    $(shop).on('change', function () {
        if (request) request.abort();
        recipient.setProfile(null);
        recipient.setAvailable(false);
        report.prop('hidden', !shop.value).attr('href', '/admin-payouts?boutique_id=' + encodeURIComponent(shop.value));
        if (!shop.value) return;
        var selected = shop.value;
        request = $.getJSON('/payout-boutique', {boutique_id:selected}).done(function (data) {
            if (shop.value !== selected) return;
            recipient.setProfile(data.profile);
            recipient.setAvailable(true);
        }).fail(function (xhr, status) {
            if (status !== 'abort') Swal.fire({icon:'error',title:'Coordonnées indisponibles',text:(xhr.responseJSON || {}).msg || 'Réessayez en sélectionnant la boutique.'});
        });
    });
    $(shop).trigger('change');
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
        $.post('/payout-demarrer', payload, null, 'json').done(function (data) {
            if (data.result === 'ok') window.location.href = data.redirect || '/admin-payout-suivi?reference=' + encodeURIComponent(data.reference);
            else failure(data);
        }).fail(function (xhr) { failure(xhr.responseJSON || {}); }).always(function () {
            button.innerHTML = old;
            recipient.setBusy(false);
        });
    });
})(jQuery);
