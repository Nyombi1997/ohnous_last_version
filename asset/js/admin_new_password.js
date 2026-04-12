(function($){
    const form = document.getElementById('admin_new_password_form');
    if(!form){
        return;
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();

        const token = form.getAttribute('data-token');
        const password = document.getElementById('admin_new_password');
        const confirmPassword = document.getElementById('admin_confirm_password');

        if(token === ''){
            Swal.fire({
                icon: 'error',
                title: 'Lien de réinitialisation invalide.',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        if(password.value.length < 6){
            Swal.fire({
                icon: 'error',
                title: 'Le mot de passe doit contenir au moins 6 caractères.',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        if(password.value !== confirmPassword.value){
            Swal.fire({
                icon: 'error',
                title: 'Les mots de passe ne correspondent pas.',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;
        button.setAttribute('disabled', '');
        button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

        $.post('/fonctions/admin_password_reset.php', {
            token: token,
            mdp: password.value
        }, function(data){
            if(data.result !== 'ok'){
                Swal.fire({
                    icon: 'error',
                    title: data.msg || 'Impossible de réinitialiser le mot de passe.',
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            window.location = data.redirect || '/admin';
        }, 'json').always(function(){
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        });
    });

    document.querySelectorAll('.vu_password_form_ohnous').forEach(function(element){
        element.addEventListener('click', function(){
            const input = element.parentElement.querySelector('input');
            element.classList.toggle('fa-eye-slash');
            element.classList.toggle('fa-eye');
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
})(jQuery);
