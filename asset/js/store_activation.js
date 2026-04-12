const activationRequestButton = document.getElementById('send_store_activation_request');

if(activationRequestButton){
    activationRequestButton.addEventListener('click', function(){
        const tempText = activationRequestButton.innerHTML;
        activationRequestButton.setAttribute('disabled', '');
        activationRequestButton.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;

        $.post('/fonctions/store_activation_request.php', {}, function(data){
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
                confirmButtonColor: '#6775d6'
            });
        }, 'json').always(function(){
            activationRequestButton.removeAttribute('disabled');
            activationRequestButton.innerHTML = tempText;
        });
    });
}
