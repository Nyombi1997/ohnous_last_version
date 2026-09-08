(function ($) {
    var form=document.getElementById('admin_payout_form'), input=document.getElementById('payout_phone'), hidden=document.getElementById('payout_phone_international');
    if(!form||!input) return;
    var iti=window.intlTelInput(input,{initialCountry:'cd',preferredCountries:['cd'],separateDialCode:true});
    var shop = document.getElementById('payout_boutique'), phones = [], request = null;
    var suggestions = $('#payout_phone_suggestions'), report = $('#payout_boutique_report');
    function fillProfile(profile) {
        ['beneficiary','merchant_recipient_id','kyc_reference','existing_recipient_id','operator'].forEach(function (name) {
            form.elements[name].value = profile && profile[name] || '';
        });
        iti.setNumber(profile && profile.phone_number || '');
        hidden.value = '';
        suggestions.empty().prop('hidden', true);
    }
    $(shop).on('change', function () {
        if (request) request.abort();
        phones = [];
        fillProfile(null);
        report.prop('hidden', !shop.value).attr('href', '/admin-payouts?boutique_id=' + encodeURIComponent(shop.value));
        var submit = $(form).find('[type="submit"]').prop('disabled', true);
        if (!shop.value) return;
        var selected = shop.value;
        request = $.getJSON('/payout-boutique', {boutique_id: selected}).done(function (data) {
            if (shop.value !== selected) return;
            phones = data.phones || [];
            fillProfile(data.profile);
            if (!data.profile) {
                form.elements.beneficiary.value = shop.options[shop.selectedIndex].text;
                form.elements.merchant_recipient_id.value = 'boutique_' + selected;
            }
            submit.prop('disabled', false);
        }).fail(function (xhr, status) {
            if (status !== 'abort') Swal.fire({icon:'error',title:'Coordonnées indisponibles',text:(xhr.responseJSON || {}).msg || 'Réessayez en sélectionnant la boutique.'});
        });
    });
    $(input).on('input', function () {
        var digits = input.value.replace(/\D/g, '');
        suggestions.empty().prop('hidden', true);
        if (!digits) return;
        phones.forEach(function (phone) {
            var international = phone.phone_number.replace(/\D/g, '');
            var national = international.indexOf('243') === 0 ? international.slice(3) : international;
            if (![international, national, '0' + national].some(function (number) { return number.indexOf(digits) === 0; })) return;
            $('<button>', {type:'button',class:'payout-phone-suggestion',text:phone.phone_number + ' · ' + phone.operator}).on('click', function () {
                fillProfile(phone);
                input.focus();
            }).appendTo(suggestions);
        });
        suggestions.prop('hidden', !suggestions.children().length);
    });
    $(shop).trigger('change');
    form.addEventListener('submit',function(e){e.preventDefault(); if(!iti.isValidNumber()){Swal.fire({icon:'error',title:'Numéro Mobile Money invalide.'});return;} hidden.value=iti.getNumber(); var button=form.querySelector('button[type="submit"]'), old=button.innerHTML; button.disabled=true; button.innerHTML='<i class="fa-solid fa-circle-notch rotate"></i>';
        $.post('/payout-demarrer',$(form).serialize(),function(data){var redirect=data.redirect||(data.reference?'/admin-payout-suivi?reference='+encodeURIComponent(data.reference):'');if(data.result==='ok'){window.location.href=redirect;}else{Swal.fire({icon:'error',title:'PayOut refusé',text:data.msg||'PayOut impossible.'}).then(function(){if(redirect)window.location.href=redirect;});}},'json').fail(function(xhr){var data=xhr.responseJSON||{},redirect=data.redirect||(data.reference?'/admin-payout-suivi?reference='+encodeURIComponent(data.reference):'');Swal.fire({icon:'error',title:'PayOut impossible',text:data.msg||data.technical_error||'Le serveur ne répond pas. Conservez la même référence avant de réessayer.'}).then(function(){if(redirect)window.location.href=redirect;});}).always(function(){button.disabled=false;button.innerHTML=old;});
    });
})(jQuery);
