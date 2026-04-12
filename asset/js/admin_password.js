(function($){
    const form = document.getElementById('admin_password_request_form');
    if(!form){
        return;
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;
        const email = document.getElementById('admin_password_email').value.trim();

        if(email === ''){
            Swal.fire({
                icon: 'error',
                title: 'Entrez l’email admin.',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        button.setAttribute('disabled', '');
        button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

        $.post('/fonctions/admin_password_request.php', {
            email: email
        }, function(data){
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
                confirmButtonColor: '#6775d6'
            }).then(function(){
                if(data.result === 'ok'){
                    window.location = '/admin-login';
                }
            });
        }, 'json').always(function(){
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        });
    });
})(jQuery);
