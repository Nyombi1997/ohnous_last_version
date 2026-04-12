(function($){
    function setAuthRedirect(path){
        localStorage.setItem('ohnous_after_auth_redirect', path || (window.location.pathname + window.location.search + window.location.hash));
    }

    function openAccountRequiredPopup(){
        Swal.fire({
            icon: 'info',
            title: 'Connexion requise',
            html: `
                <div class="swal-review-auth">
                    <p>Connectez-vous ou inscrivez-vous avant d’ajouter un article à vos favoris.</p>
                    <div class="swal-review-auth__actions">
                        <button type="button" class="btn_ohnous js-global-auth-link" data-href="/connexion">Se connecter</button>
                        <button type="button" class="btn_ohnous second js-global-auth-link" data-href="/choix-compte">S’inscrire</button>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            customClass: {
                popup: 'swal-liquid-popup'
            },
            didOpen: function(){
                $('.js-global-auth-link').off('click').on('click', function(){
                    setAuthRedirect(window.location.pathname + window.location.search + window.location.hash);
                    window.location = $(this).data('href');
                });
            }
        });
    }

    function updateLikeButton($button, payload){
        if(!$button || !payload){
            return;
        }

        $button.toggleClass('is-liked', payload.liked === true || payload.liked === 1);
        $button.attr('data-liked', payload.liked ? '1' : '0');
        $button.find('i')
            .toggleClass('fa-solid', payload.liked)
            .toggleClass('fa-regular', !payload.liked);
        $button.find('[data-like-count]').text(payload.count_formatted || '0');
        const articleId = Number($button.data('article-id') || 0);
        if(articleId > 0){
            $('[data-like-total-label="'+articleId+'"]').text(payload.count_formatted || '0');
        }
    }

    $(document).on('click', '.js-like-button', function(){
        const $button = $(this);
        const articleId = Number($button.data('article-id') || 0);

        if(articleId <= 0){
            return;
        }

        $.post('/fonctions/article_likes.php', {
            action: 'toggle',
            article_id: articleId
        }, function(data){
            if(data.result === 'auth_required'){
                openAccountRequiredPopup();
                return;
            }

            if(data.result !== 'ok'){
                Swal.fire({
                    icon: 'error',
                    title: data.msg || "Une erreur est survenue.",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }

            updateLikeButton($button, data);

            $('.js-like-button[data-article-id="'+articleId+'"]').not($button).each(function(){
                updateLikeButton($(this), data);
            });
        }, 'json');
    });
})(jQuery);
