(function($){
    $('.js-admin-delete-reported-article').on('click', function(){
        const button = $(this);
        const articleId = Number(button.data('article-id') || 0);
        const articleName = String(button.data('article-name') || 'cet article');

        Swal.fire({
            icon: 'warning',
            title: 'Supprimer l’article ?',
            html: '<p>Article : <strong>'+articleName+'</strong></p>',
            input: 'textarea',
            inputPlaceholder: 'Raison envoyée à la boutique...',
            inputAttributes: {
                maxlength: 1200
            },
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#d94f64',
            cancelButtonColor: '#6775d6',
            inputValidator: function(value){
                if(!value || value.trim().length < 8){
                    return 'Écrivez la raison de suppression.';
                }
            }
        }).then(function(result){
            if(!result.isConfirmed){
                return;
            }

            Swal.fire({
                title: 'Suppression en cours...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function(){
                    Swal.showLoading();
                }
            });

            $.post('/fonctions/admin_article_actions.php', {
                action: 'delete_reported_article',
                article_id: articleId,
                reason: result.value.trim()
            }, function(data){
                if(data.result !== 'ok'){
                    Swal.fire({
                        icon: 'error',
                        title: data.msg || "Suppression impossible.",
                        confirmButtonColor: '#6775d6'
                    });
                    return;
                }

                $('[data-admin-article-row="'+articleId+'"]').remove();
                Swal.fire({
                    icon: 'success',
                    title: data.msg,
                    confirmButtonColor: '#6775d6'
                });
            }, 'json').fail(function(){
                Swal.fire({
                    icon: 'error',
                    title: "Erreur réseau.",
                    confirmButtonColor: '#6775d6'
                });
            });
        });
    });
})(jQuery);
