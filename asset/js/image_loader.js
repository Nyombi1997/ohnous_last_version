(function () {
    function appendRetryToken(url, attempt) {
        if (!url) {
            return '';
        }

        var separator = url.indexOf('?') === -1 ? '?' : '&';
        return url + separator + 'retryAttempt=' + attempt + '_' + Date.now();
    }

    function hydrateImage(img) {
        if (!img || img.dataset.imageHydrated === '1') {
            return;
        }

        var highSrc = img.getAttribute('data-image-high') || '';
        var fallbackSrc = img.getAttribute('data-image-fallback') || '';
        var srcset = img.getAttribute('data-image-srcset') || '';
        var sizes = img.getAttribute('data-image-sizes') || '';

        if (!highSrc) {
            return;
        }

        img.dataset.imageHydrated = '1';
        img.dataset.imageAttempt = '0';
        img.classList.add('blur-up');

        function tryLoad() {
            if (!document.body.contains(img)) {
                return;
            }

            var nextAttempt = parseInt(img.dataset.imageAttempt || '0', 10) + 1;
            img.dataset.imageAttempt = String(nextAttempt);

            var preload = new Image();
            preload.decoding = 'async';
            preload.loading = 'eager';

            /* On charge la version nette en dehors du DOM.
               Si elle échoue, on garde la version légère visible et on réessaie. */
            preload.onload = function () {
                if (!document.body.contains(img)) {
                    return;
                }

                if (sizes) {
                    img.setAttribute('sizes', sizes);
                }

                if (srcset) {
                    img.setAttribute('srcset', srcset);
                }

                img.src = highSrc;
                img.classList.add('blur-up-loaded');
                img.classList.remove('is-image-retrying');
                img.removeAttribute('data-image-retry-timer');
            };

            preload.onerror = function () {
                if (!document.body.contains(img)) {
                    return;
                }

                if (fallbackSrc && img.src !== fallbackSrc) {
                    img.src = fallbackSrc;
                }

                img.classList.remove('blur-up-loaded');
                img.classList.add('is-image-retrying');

                var delay = Math.min(15000, 1500 + (nextAttempt * 1200));
                var timer = window.setTimeout(function () {
                    tryLoad();
                }, delay);

                img.dataset.imageRetryTimer = String(timer);
            };

            preload.src = appendRetryToken(highSrc, nextAttempt);
        }

        if (img.loading === 'lazy' && 'IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        observer.disconnect();
                        tryLoad();
                    }
                });
            }, {
                rootMargin: '200px 0px'
            });

            observer.observe(img);
            img._liquidObserver = observer;
            return;
        }

        tryLoad();
    }

    function initLiquidImages() {
        var images = document.querySelectorAll('img.js-liquid-image');
        images.forEach(function (img) {
            hydrateImage(img);
        });
    }

    window.initLiquidImages = initLiquidImages;

    document.addEventListener('DOMContentLoaded', initLiquidImages);
    window.addEventListener('pageshow', initLiquidImages);
    window.addEventListener('online', initLiquidImages);
})();
