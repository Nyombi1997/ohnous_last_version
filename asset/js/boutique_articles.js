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

        $.post('/fonctions/filtre_article.php', {
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

    $(document).ready(function () {
        cacheDom();

        if (!isPageReady()) {
            return;
        }

        requestArticles(true);
        window.addEventListener('scroll', loadMoreIfNeeded);
        window.addEventListener('resize', loadMoreIfNeeded);
    });
})(jQuery);
