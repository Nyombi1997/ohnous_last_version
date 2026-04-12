const adminStoreActivationForm = document.getElementById('admin_store_activation_form');

if(adminStoreActivationForm){
    adminStoreActivationForm.addEventListener('submit', function(e){
        e.preventDefault();

        const token = document.getElementById('activation_token').value;
        const months = document.getElementById('activation_months').value.trim();
        const days = document.getElementById('activation_days').value.trim();
        const button = adminStoreActivationForm.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;

        button.setAttribute('disabled', '');
        button.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;

        $.post('/fonctions/admin_store_activation.php', {
            token: token,
            months: months,
            days: days
        }, function(data){
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
                confirmButtonColor: '#6775d6'
            });
        }, 'json').always(function(){
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        });
    });
}
