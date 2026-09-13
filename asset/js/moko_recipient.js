(function ($) {
    window.OhnousRecipientForm = function (form) {
        var phone = form.querySelector('#payout_phone');
        var iti = window.intlTelInput(phone, {initialCountry:'cd',onlyCountries:['cd'],separateDialCode:true});
        var button = $(form).find('#register_recipient'), fields = $(form).find('#recipient_fields');
        var summary = $(form).find('#recipient_summary'), profile = null, busy = false, available = true;
        function registered() { return !!(profile && profile.existing_recipient_id); }
        function sync() {
            var locked = !!(profile && (profile.existing_recipient_id || profile.recipient_status));
            fields.prop('hidden', registered()).prop('disabled', busy || locked || !available);
            button.prop('disabled', busy || !available);
            $(form).find('#payout_boutique').prop('disabled', busy);
            $(form).find('[type="submit"]').prop('disabled', busy || !available || !registered() || profile.recipient_status !== 'ACTIVE');
        }
        function setProfile(value) {
            profile = value || null;
            ['beneficiary','operator','kyc_reference'].forEach(function (name) { form.elements[name].value = profile && profile[name] || ''; });
            iti.setNumber(profile && profile.phone_number || '');
            summary.empty();
            if (registered()) {
                $('<strong>').text(profile.beneficiary).appendTo(summary);
                $('<span>').text(profile.phone_number + ' · ' + profile.operator).appendTo(summary);
                $('<span>').text('Identifiant Moko : ' + profile.existing_recipient_id).appendTo(summary);
                $('<span>').text(profile.recipient_status === 'ACTIVE' ? 'Bénéficiaire actif.' : 'Validation Moko nécessaire avant le versement.').appendTo(summary);
            } else if (profile && profile.recipient_status) {
                $('<span>').text('Enregistrement à finaliser. Réessayez avec les coordonnées déjà transmises.').appendTo(summary);
            }
            button.find('span').text(registered() ? 'Actualiser le bénéficiaire' : 'Enregistrer le bénéficiaire');
            sync();
        }
        function register() {
            if (busy || !available) return;
            var shop = form.querySelector('#payout_boutique');
            if (shop && !shop.reportValidity()) return;
            if (!fields.prop('disabled')) {
                var valid = true;
                fields.find('input[name]:not([type="hidden"]),select[name]').each(function () { if (valid && !this.reportValidity()) valid = false; });
                if (!valid) return;
                try {
                    if (!iti.isValidNumber()) { Swal.fire({icon:'error',title:'Numéro Mobile Money invalide.'}); return; }
                    form.elements.phone_number.value = iti.getNumber();
                } catch (error) {
                    Swal.fire({icon:'error',title:'Vérification du numéro indisponible',text:'Rechargez la page puis réessayez.'});
                    return;
                }
            }
            var payload = $(form).serialize();
            busy = true; sync();
            button.attr('aria-busy', 'true').find('span').text('Enregistrement en cours…');
            $.ajax({url:'/payout-beneficiaire',type:'POST',data:payload,dataType:'json',timeout:60000}).done(function (data) {
                if (data.recipient_csrf) form.elements.recipient_csrf.value = data.recipient_csrf;
                if (data.result === 'ok' && data.profile) {
                    setProfile(data.profile);
                    if (data.registered) $('#store_recipient_reminder').prop('hidden', true);
                    Swal.fire({icon:'success',title:data.registered ? 'Bénéficiaire enregistré' : 'Coordonnées enregistrées',text:data.msg});
                } else {
                    Swal.fire({icon:data.result === 'ok' ? 'info' : 'error',title:data.msg || 'Enregistrement impossible.'});
                }
            }).fail(function (xhr) {
                Swal.fire({icon:'error',title:'Enregistrement impossible',text:(xhr.responseJSON || {}).msg || 'Réessayez avec les mêmes coordonnées.'});
            }).always(function () {
                busy = false;
                button.removeAttr('aria-busy').find('span').text(registered() ? 'Actualiser le bénéficiaire' : 'Enregistrer le bénéficiaire');
                sync();
            });
        }
        button.on('click', register);
        return {
            setProfile:setProfile,
            setAvailable:function (value) { available = value; sync(); },
            setBusy:function (value) { busy = value; sync(); },
            canPay:function () { return !busy && available && registered() && profile.recipient_status === 'ACTIVE'; },
            register:register
        };
    };
    var storeForm = document.getElementById('store_recipient_form');
    if (storeForm) {
        var recipient = new window.OhnousRecipientForm(storeForm);
        recipient.setProfile(JSON.parse(storeForm.getAttribute('data-profile') || 'null'));
        $(storeForm).on('submit', function (event) { event.preventDefault(); recipient.register(); });
    }
})(jQuery);
