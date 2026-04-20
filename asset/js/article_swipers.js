(function(){
    function updateGalleryCounter(swiper){
        const galleryElement = swiper && swiper.el ? swiper.el : null;
        if(!galleryElement){
            return;
        }

        const currentNode = galleryElement.querySelector('.article-gallery-counter .current');
        if(currentNode){
            currentNode.textContent = String(swiper.realIndex + 1);
        }
    }

    function initGallery(galleryElement){
        if(!galleryElement || galleryElement.dataset.swiperReady === '1'){
            return;
        }

        const slides = galleryElement.querySelectorAll('.swiper-slide');
        if(slides.length < 2){
            return;
        }

        galleryElement.dataset.swiperReady = '1';

        new Swiper(galleryElement, {
            loop: true,
            slidesPerView: 1,
            spaceBetween: 0,
            autoplay: {
                delay: 2500,
                disableOnInteraction: true,
                pauseOnMouseEnter: true
            },
            on: {
                init: function(){
                    updateGalleryCounter(this);
                },
                slideChange: function(){
                    updateGalleryCounter(this);
                }
            }
        });
    }

    function initAllArticleGalleries(root){
        const scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('.js-product-gallery-swiper').forEach(function(galleryElement){
            initGallery(galleryElement);
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        initAllArticleGalleries(document);

        /* réinitialiser les swipers des cartes injectées en AJAX sans recoder chaque vue */
        if(document.body && window.MutationObserver){
            const observer = new MutationObserver(function(mutations){
                mutations.forEach(function(mutation){
                    mutation.addedNodes.forEach(function(node){
                        if(!(node instanceof HTMLElement)){
                            return;
                        }

                        if(node.matches('.js-product-gallery-swiper')){
                            initGallery(node);
                            return;
                        }

                        initAllArticleGalleries(node);
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    });

    window.ohnousInitProductGalleries = initAllArticleGalleries;
})();
