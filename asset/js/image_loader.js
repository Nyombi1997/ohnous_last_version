(function () {
    function appendRetryToken(url, attempt) {
        if (!url) {
            return "";
        }

        var separator = url.indexOf("?") === -1 ? "?" : "&";
        return url + separator + "retryAttempt=" + attempt + "_" + Date.now();
    }

    function clearRetryTimer(img) {
        var timer = parseInt(img.dataset.imageRetryTimer || "0", 10);
        if (timer) {
            window.clearTimeout(timer);
            delete img.dataset.imageRetryTimer;
        }
    }

    function markAsLoaded(img) {
        img.classList.add("blur-up-loaded");
        img.classList.remove("is-image-retrying");
        clearRetryTimer(img);
    }

    function hydrateImage(img) {
        if (!img || img.dataset.imageHydrated === "1") {
            return;
        }

        var highSrc = img.getAttribute("data-image-high") || "";
        var fallbackSrc = img.getAttribute("data-image-fallback") || "";
        var srcset = img.getAttribute("data-image-srcset") || "";
        var sizes = img.getAttribute("data-image-sizes") || "";

        if (!highSrc) {
            return;
        }

        img.dataset.imageHydrated = "1";
        img.dataset.imageAttempt = "0";
        img.classList.add("blur-up");

        function tryLoad() {
            if (!document.body.contains(img)) {
                clearRetryTimer(img);
                return;
            }

            var nextAttempt = parseInt(img.dataset.imageAttempt || "0", 10) + 1;
            img.dataset.imageAttempt = String(nextAttempt);

            var preload = new Image();
            preload.decoding = "async";
            preload.loading = "eager";

            preload.onload = function () {
                if (!document.body.contains(img)) {
                    return;
                }

                if (sizes) {
                    img.setAttribute("sizes", sizes);
                }

                if (srcset) {
                    img.setAttribute("srcset", srcset);
                }

                img.src = highSrc;
                markAsLoaded(img);
            };

            preload.onerror = function () {
                if (!document.body.contains(img)) {
                    clearRetryTimer(img);
                    return;
                }

                if (fallbackSrc && img.src !== fallbackSrc) {
                    img.src = fallbackSrc;
                }

                img.classList.remove("blur-up-loaded");
                img.classList.add("is-image-retrying");

                var delay = Math.min(15000, 1500 + (nextAttempt * 1200));
                var timer = window.setTimeout(tryLoad, delay);
                img.dataset.imageRetryTimer = String(timer);
            };

            preload.src = appendRetryToken(highSrc, nextAttempt);
        }

        if (img.loading === "lazy" && "IntersectionObserver" in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        observer.disconnect();
                        tryLoad();
                    }
                });
            }, {
                rootMargin: "200px 0px"
            });

            observer.observe(img);
            img._liquidObserver = observer;
            return;
        }

        tryLoad();
    }

    function initLiquidImages(scope) {
        var root = scope && scope.querySelectorAll ? scope : document;
        var images = root.querySelectorAll("img.js-liquid-image");
        images.forEach(function (img) {
            hydrateImage(img);
            if (img.complete && img.currentSrc) {
                markAsLoaded(img);
            }
        });
    }

    function observeDynamicImages() {
        if (!("MutationObserver" in window) || !document.body) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!node || node.nodeType !== 1) {
                        return;
                    }

                    if (node.matches && node.matches("img.js-liquid-image")) {
                        initLiquidImages(node.parentNode || document);
                        return;
                    }

                    if (node.querySelectorAll) {
                        initLiquidImages(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    window.initLiquidImages = initLiquidImages;

    document.addEventListener("DOMContentLoaded", function () {
        initLiquidImages();
        observeDynamicImages();
    });
    window.addEventListener("pageshow", function () {
        initLiquidImages();
    });
    window.addEventListener("online", function () {
        initLiquidImages();
    });
})();
