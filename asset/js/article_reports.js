(function($){
    const form = document.getElementById('article_report_form');
    const openButton = document.querySelector('.js-article-report-open');
    const closeButton = document.querySelector('.js-article-report-close');

    if(!form || !openButton){
        return;
    }

    function openForm(){
        form.classList.add('is-visible');
        form.querySelector('select').focus();
    }

    function closeForm(){
        form.classList.remove('is-visible');
    }

    openButton.addEventListener('click', openForm);

    if(closeButton){
        closeButton.addEventListener('click', closeForm);
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;
        button.setAttribute('disabled', '');
        button.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';

        $.post('/fonctions/article_reports.php', $(form).serialize(), function(data){
            if(data.result !== 'ok'){
                Swal.fire({
                    icon: 'error',
                    title: data.msg || "Impossible d’envoyer le signalement.",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: data.msg,
                confirmButtonColor: '#6775d6'
            });
            form.reset();
            closeForm();
        }, 'json').fail(function(){
            Swal.fire({
                icon: 'error',
                title: "Erreur réseau.",
                confirmButtonColor: '#6775d6'
            });
        }).always(function(){
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
        });
    });
})(jQuery);
