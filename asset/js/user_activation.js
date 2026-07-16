const userActivationForm = document.getElementById('user_activation_form');

if(userActivationForm){
    const phoneInputs = {};

    document.querySelectorAll('.js-intl-phone').forEach(function(input){
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

    userActivationForm.addEventListener('submit', function(e){
        e.preventDefault();

        const whatsapp = getPhoneValue('activation_whatsapp');
        const telephone = getPhoneValue('activation_telephone');
        const instagram = document.getElementById('activation_instagram').value.trim();
        const facebook = document.getElementById('activation_facebook').value.trim();
        const tiktok = document.getElementById('activation_tiktok').value.trim();
        const button = userActivationForm.querySelector('button[type="submit"]');
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

        $.post('/fonctions/user_activation_request.php', $(userActivationForm).serialize()
            + '&whatsapp=' + encodeURIComponent(whatsapp || '')
            + '&telephone=' + encodeURIComponent(telephone || '')
            + '&instagram=' + encodeURIComponent(instagram)
            + '&facebook=' + encodeURIComponent(facebook)
            + '&tiktok=' + encodeURIComponent(tiktok), function(data){
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
        });
    });
}
