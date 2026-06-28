const storeActivationForm = document.getElementById('store_activation_form');

if(storeActivationForm){
    const phoneInputs = {};

    document.querySelectorAll('.js-store-intl-phone').forEach(function(input){
        phoneInputs[input.id] = window.intlTelInput(input, {
            initialCountry: 'cd',
            separateDialCode: true,
            nationalMode: false,
            strictMode: true,
            autoPlaceholder: 'aggressive',
            countrySearch: true,
        });
    });

    function getPhoneValue(id)
    {
        const input = document.getElementById(id);
        if(!input || input.value.trim() === ''){
            return '';
        }
        const iti = phoneInputs[id];
        if(!iti || !iti.isValidNumber()){
            return null;
        }
        return iti.getNumber();
    }

    storeActivationForm.addEventListener('submit', function(e){
        e.preventDefault();

        const whatsapp = getPhoneValue('store_activation_whatsapp');
        const telephone = getPhoneValue('store_activation_telephone');
        const instagram = document.getElementById('store_activation_instagram').value.trim();
        const facebook = document.getElementById('store_activation_facebook').value.trim();
        const tiktok = document.getElementById('store_activation_tiktok').value.trim();
        const button = document.getElementById('send_store_activation_request');
        const tempText = button.innerHTML;

        if(whatsapp === null){
            Swal.fire({icon:'error', title:'Le numéro WhatsApp est invalide.', confirmButtonColor:'#6775d6'});
            return;
        }

        if(telephone === null){
            Swal.fire({icon:'error', title:'Le numéro d’appel est invalide.', confirmButtonColor:'#6775d6'});
            return;
        }

        if((whatsapp || '') === '' && (telephone || '') === '' && instagram === '' && facebook === '' && tiktok === ''){
            Swal.fire({icon:'error', title:'Renseignez au moins une information de contact.', confirmButtonColor:'#6775d6'});
            return;
        }

        button.setAttribute('disabled', '');
        button.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;

        $.post('/fonctions/store_activation_request.php', {
            whatsapp: whatsapp || '',
            telephone: telephone || '',
            instagram: instagram,
            facebook: facebook,
            tiktok: tiktok,
        }, function(data){
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
                confirmButtonColor: '#6775d6'
            }).then(function(){
                if(data.result === 'ok'){
                    window.location.reload();
                }
            });
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        }, 'json').fail(function(){
            Swal.fire({
                icon: 'error',
                title: "Impossible d’envoyer la demande.",
                confirmButtonColor: '#6775d6'
            });
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        });
    });
}
