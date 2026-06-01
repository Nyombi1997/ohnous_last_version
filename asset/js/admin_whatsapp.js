(function ($) {
    var form = document.getElementById('admin_whatsapp_test_form');

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

        $.post('/fonctions/whatsapp_admin_actions.php', $(form).serialize() + '&action=send_test', function (data) {
            Swal.fire({
                icon: data.result === 'ok' ? 'success' : 'error',
                title: data.msg,
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
