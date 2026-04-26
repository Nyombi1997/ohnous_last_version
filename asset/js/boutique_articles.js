(function ($) {
    var PAGE_SIZE = 12;
    var config = window.storeArticlesConfig || {};
    var state = {
        offset: 0,
        loading: false,
        hasMore: true
    };

    var els = {};

    function cacheDom() {
        els.page = document.getElementById('store_articles_page');
        els.results = document.getElementById('store_articles_results');
        els.loader = document.getElementById('store_articles_loader');
        els.empty = document.getElementById('store_articles_empty');
    }

    function isPageReady() {
        return !!els.page && !!els.results && parseInt(config.storeId || 0, 10) > 0;
    }

    function setLoading(active) {
        state.loading = !!active;
        if (els.loader) {
            els.loader.classList.toggle('is-visible', !!active);
        }
    }

    function showEmptyState(show) {
        if (els.empty) {
            els.empty.classList.toggle('null', !show);
        }
    }

    function getEndpoint() {
        return config.isOwner ? '/fonctions/store_articles_fetch.php' : '/fonctions/filtre_article.php';
    }

    function requestArticles(reset) {
        if (!isPageReady() || state.loading || (!state.hasMore && !reset)) {
            return;
        }

        if (reset) {
            state.offset = 0;
            state.hasMore = true;
            els.results.innerHTML = '';
            showEmptyState(false);
        }

        setLoading(true);

        $.post(getEndpoint(), {
            categorie: 0,
            types: 0,
            taille: 0,
            boutique: parseInt(config.storeId || 0, 10),
            prix: '',
            recherche: config.query || '',
            order: 'date_desc',
            offset: state.offset,
            limit: PAGE_SIZE
        }, function (data) {
            setLoading(false);

            if (!data || data.result !== 'ok') {
                if (reset) {
                    showEmptyState(true);
                }
                state.hasMore = false;
                return;
            }

            if (reset && parseInt(data.total, 10) === 0) {
                showEmptyState(true);
                state.hasMore = false;
                return;
            }

            if (data.msg) {
                els.results.insertAdjacentHTML('beforeend', data.msg);
                state.offset += parseInt(data.nombre, 10) || 0;
            }

            state.hasMore = !!data.has_more;

            if (typeof window.initLiquidImages === 'function') {
                window.initLiquidImages(els.results);
            }
        }, 'json').fail(function () {
            setLoading(false);
            if (reset) {
                showEmptyState(true);
            }
            state.hasMore = false;
        });
    }

    function loadMoreIfNeeded() {
        if (!isPageReady() || state.loading || !state.hasMore) {
            return;
        }

        var bottomTrigger = document.documentElement.scrollHeight - 360;
        var currentBottom = window.scrollY + window.innerHeight;

        if (currentBottom >= bottomTrigger) {
            requestArticles(false);
        }
    }

    function bindOwnerActions() {
        if (!config.isOwner || !els.results) {
            return;
        }

        $(els.results).off('click.storeDelete').on('click.storeDelete', '.js-store-delete-article', function () {
            var $button = $(this);
            var articleId = parseInt($button.data('article-id') || 0, 10);
            var articleName = String($button.data('article-name') || 'cet article');

            if (articleId <= 0) {
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Supprimer l’article ?',
                text: '“' + articleName + '” sera supprimé définitivement.',
                showCancelButton: true,
                confirmButtonText: 'Supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d94b4b'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $button.prop('disabled', true);
                Swal.fire({
                    title: "Suppression de l’article...",
                    text: "Merci de patienter.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.post('/fonctions/store_article_actions.php', {
                    action: 'delete_article',
                    article_id: articleId
                }, function (data) {
                    if (!data || data.result !== 'ok') {
                        Swal.fire({
                            icon: 'error',
                            title: data && data.msg ? data.msg : "Impossible de supprimer l'article.",
                            confirmButtonColor: '#6775d6'
                        });
                        $button.prop('disabled', false);
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: data.msg || "L'article a été supprimé.",
                        confirmButtonColor: '#6775d6'
                    }).then(function () {
                        requestArticles(true);
                    });
                }, 'json').fail(function () {
                    Swal.fire({
                        icon: 'error',
                        title: "Impossible de supprimer l'article.",
                        confirmButtonColor: '#6775d6'
                    });
                    $button.prop('disabled', false);
                });
            });
        });
    }

    $(document).ready(function () {
        cacheDom();

        if (!isPageReady()) {
            return;
        }

        bindOwnerActions();
        requestArticles(true);
        window.addEventListener('scroll', loadMoreIfNeeded);
        window.addEventListener('resize', loadMoreIfNeeded);
    });
})(jQuery);
