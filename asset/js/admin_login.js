(function($){
    const form = document.getElementById('admin_login_form');
    if(!form){
        return;
    }

    const email = document.getElementById('admin_email');
    const password = document.getElementById('admin_password');

    form.addEventListener('submit', function(e){
        e.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;

        if(email.value.trim() === '' || password.value.trim() === ''){
            Swal.fire({
                icon: 'error',
                title: 'Renseignez vos identifiants admin.',
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        button.setAttribute('disabled', '');
        button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

        $.post('/fonctions/admin_login.php', $(form).serialize()
            + '&email=' + encodeURIComponent(email.value.trim())
            + '&mdp=' + encodeURIComponent(password.value), function(data){
            if(data.result !== 'ok'){
                Swal.fire({
                    icon: 'error',
                    title: data.msg || 'Connexion admin impossible.',
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
