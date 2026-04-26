(function(window){
    function getBoundaryWidth(mode){
        if(mode === 'article'){
            return Math.max(280, Math.min(360, window.innerWidth - 30));
        }

        return Math.max(270, Math.min(360, window.innerWidth - 30));
    }

    function getViewport(mode, boundaryWidth){
        if(mode === 'article'){
            var width = Math.min(180, boundaryWidth - 40);
            width = Math.max(150, width);
            return {
                width: width,
                height: Math.round(width * 16 / 9),
                type: 'square'
            };
        }

        var size = Math.min(250, boundaryWidth - 20);
        return {
            width: size,
            height: size,
            type: 'circle'
        };
    }

    window.initCroppieUploader = function(options){
        var settings = options || {};
        var mode = settings.mode || 'article';
        var container = typeof settings.container === 'string' ? document.querySelector(settings.container) : settings.container;
        var instance = null;
        var currentUrl = '';

        function destroy(){
            if(instance){
                instance.destroy();
                instance = null;
            }

            if(container){
                container.innerHTML = '';
            }
        }

        function init(dataUrl){
            if(!container || typeof Croppie === 'undefined'){
                return;
            }

            destroy();
            currentUrl = dataUrl;

            var boundaryWidth = getBoundaryWidth(mode);
            var viewport = getViewport(mode, boundaryWidth);

            instance = new Croppie(container, {
                viewport: viewport,
                boundary: {
                    width: boundaryWidth,
                    height: mode === 'article' ? Math.max(260, viewport.height + 110) : Math.max(300, viewport.height + 90)
                },
                enableExif: true,
                enableOrientation: true,
                enableResize: mode === 'article',
                enableZoom: true,
                showZoomer: true,
                mouseWheelZoom: 'ctrl',
                enforceBoundary: true
            });

            return instance.bind({
                url: dataUrl
            }).then(function(){
                setTimeout(function(){
                    var zoomer = container.querySelector('.cr-slider');
                    if(zoomer && typeof instance.setZoom === 'function'){
                        var minZoom = parseFloat(zoomer.min || zoomer.value || '1');
                        instance.setZoom(minZoom);
                        zoomer.value = String(minZoom);
                        zoomer.dispatchEvent(new Event('input', {bubbles: true}));
                        zoomer.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                }, 80);
            });
        }

        function result(){
            if(!instance){
                return Promise.resolve('');
            }

            if(mode === 'profile'){
                return instance.result({
                    type: 'base64',
                    size: {
                        width: 500,
                        height: 500
                    },
                    format: 'jpeg',
                    quality: 0.9,
                    circle: false
                });
            }

            return instance.result({
                type: 'base64',
                size: 'viewport',
                format: 'jpeg',
                quality: 0.9
            });
        }

        return {
            init: init,
            result: result,
            destroy: destroy,
            hasImage: function(){
                return !!instance && currentUrl !== '';
            }
        };
    };
})(window);
