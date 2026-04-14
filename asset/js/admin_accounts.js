(function ($) {
    var form = document.getElementById('admin_account_form');
    var autoPassword = document.getElementById('admin_auto_password');
    var manualWrapper = document.getElementById('admin_manual_password_wrapper');
    var manualPassword = document.getElementById('admin_account_password');

    function syncPasswordMode() {
        var automatic = !autoPassword || autoPassword.checked;
        if (manualWrapper) {
            manualWrapper.classList.toggle('is-disabled', automatic);
        }
        if (manualPassword) {
            manualPassword.disabled = automatic;
            if (automatic) {
                manualPassword.value = '';
            }
        }
    }

    if (autoPassword) {
        autoPassword.addEventListener('change', syncPasswordMode);
        syncPasswordMode();
    }

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var submitButton = form.querySelector('button[type="submit"]');
        var buttonHtml = submitButton ? submitButton.innerHTML : '';
        if (submitButton) {
            submitButton.setAttribute('disabled', '');
            submitButton.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';
        }

        $.post('/fonctions/admin_accounts.php', $(form).serialize() + '&action=create_admin', function (data) {
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
                html: data.generated_password
                    ? '<p style="font-size:16px;">Mot de passe généré : <strong>' + data.generated_password + '</strong></p>'
                    : '',
                confirmButtonColor: '#6775d6'
            }).then(function () {
                if (data.result === 'ok') {
                    window.location.reload();
                }
            });
        }, 'json').always(function () {
            if (submitButton) {
                submitButton.removeAttribute('disabled');
                submitButton.innerHTML = buttonHtml;
            }
        });
    });
})(jQuery);
