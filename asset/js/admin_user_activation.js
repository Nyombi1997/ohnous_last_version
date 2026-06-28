document.querySelectorAll('.admin-user-activation-action').forEach(function(button){
    button.addEventListener('click', function(){
        const tempText = button.innerHTML;
        button.setAttribute('disabled', '');
        button.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;

        $.post('/fonctions/admin_user_activation.php', {
            id: button.dataset.id,
            action: button.dataset.action
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
        });
    });
});
