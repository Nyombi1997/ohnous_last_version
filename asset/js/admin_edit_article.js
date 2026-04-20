(function($){
    const config = window.ohnousAdminEditProductConfig || {};
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('imagePreview');
    const form = document.getElementById('admin_edit_article_form');
    const openFilePicker = document.getElementById('open_file_picker');
    const categorySelect = document.getElementById('category_select');
    const typesContainer = document.getElementById('types_container');
    const taillesContainer = document.getElementById('tailles_container');
    const promoPriceInput = document.getElementById('promo_prix_article');
    const promoActiveInput = document.getElementById('promo_actif_article');

    if(!form){
        return;
    }

    const images = [];
    let cropper = null;
    let currentImageId = null;
    let selectedType = String(config.selectedType || '');
    let selectedTailles = Array.isArray(config.selectedTailles) ? config.selectedTailles.map(String) : [];

    const imagekit = new ImageKit({
        publicKey: "public_RBnOctCZRQjH0d5pMKWrl8jQ/zI=",
        urlEndpoint: "https://ik.imagekit.io/nyombi1997"
    });

    function showError(message){
        Swal.fire({
            icon: 'error',
            title: message,
            confirmButtonColor: '#6775d6'
        });
    }

    function recalculateImageStyle(imgUrl){
        return new Promise(function(resolve){
            const img = new Image();
            img.src = imgUrl;
            img.onload = function(){
                if(img.width > img.height){
                    resolve('width: 100%; height: auto;');
                }else if(img.width < img.height){
                    resolve('width: auto; height: 100%;');
                }else{
                    resolve('width: 100%; height: auto;');
                }
            };
        });
    }

    function getDominantColorFromDataUrl(dataUrl){
        return new Promise(function(resolve, reject){
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = dataUrl;
            img.onload = function(){
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                ctx.drawImage(img, 0, 0);
                const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                let r = 0, g = 0, b = 0;
                const count = data.length / 4;
                for(let i = 0; i < data.length; i += 4){
                    r += data[i];
                    g += data[i + 1];
                    b += data[i + 2];
                }
                resolve({
                    r: Math.round(r / count),
                    g: Math.round(g / count),
                    b: Math.round(b / count)
                });
            };
            img.onerror = reject;
        });
    }

    function syncPrimaryIndicators(){
        images.forEach(function(item, index){
            item.isPrimary = index === 0;
            if(item.element){
                const indicator = item.element.querySelector('.crop-indicator');
                if(indicator){
                    indicator.textContent = index === 0 ? 'Principale' : 'Image';
                }
            }
        });
    }

    function openCrop(dataUrl, imageId){
        currentImageId = imageId;
        document.body.classList.add('blocked_scroll');
        document.getElementById('cropImage').src = dataUrl;
        document.getElementById('cropModal').style.display = 'flex';

        setTimeout(function(){
            if(cropper){
                cropper.destroy();
            }

            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: NaN,
                viewMode: 1,
                autoCropArea: 0.9,
                responsive: true,
                preview: '.crop-preview'
            });
        }, 100);
    }

    window.closeCrop = function(){
        document.getElementById('cropModal').style.display = 'none';
        document.body.classList.remove('blocked_scroll');
        if(cropper){
            cropper.destroy();
            cropper = null;
        }
        currentImageId = null;
    };

    window.applyCrop = function(){
        if(!cropper || !currentImageId){
            return;
        }

        const imageItem = images.find(function(item){ return item.id === currentImageId; });
        if(!imageItem || imageItem.isExisting){
            closeCrop();
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 1067,
            height: 800
        });
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        imageItem.dataUrl = dataUrl;
        imageItem.element.querySelector('img').src = dataUrl;

        recalculateImageStyle(dataUrl).then(function(style){
            imageItem.style = style;
            imageItem.element.querySelector('img').setAttribute('style', style);
        });
        getDominantColorFromDataUrl(dataUrl).then(function(color){
            imageItem.background = 'rgb('+color.r+', '+color.g+', '+color.b+')';
        });

        closeCrop();
    };

    function renderImagePreview(item){
        const element = document.createElement('div');
        element.className = 'preview-item';
        element.id = item.id;
        element.innerHTML = `
            <img src="${item.dataUrl}" alt="Aperçu">
            <div class="preview-actions">
                <button type="button" class="btn-crop"><i class="fa-solid fa-crop-simple"></i></button>
                <button type="button" class="btn-remove"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="crop-indicator">${item.isPrimary ? 'Principale' : 'Image'}</div>
        `;

        element.querySelector('.btn-crop').addEventListener('click', function(){
            if(item.isExisting){
                Swal.fire({
                    icon: 'info',
                    title: "Ajoutez une nouvelle image si vous souhaitez la recadrer.",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }
            openCrop(item.dataUrl, item.id);
        });

        element.querySelector('.btn-remove').addEventListener('click', function(){
            const index = images.findIndex(function(image){ return image.id === item.id; });
            if(index >= 0){
                images.splice(index, 1);
                element.remove();
                syncPrimaryIndicators();
            }
        });

        item.element = element;
        imagePreview.appendChild(element);
    }

    function validateFile(file){
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if(!allowed.includes(file.type)){
            showError(`Le format ${file.type} n'est pas supporté.`);
            return false;
        }
        if(file.size > 10 * 1024 * 1024){
            showError(`L'image ${file.name} dépasse 10 Mo.`);
            return false;
        }
        return true;
    }

    function handleFiles(files){
        Array.from(files).forEach(function(file){
            if(!validateFile(file)){
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e){
                const item = {
                    id: 'temp_' + Date.now() + '_' + Math.floor(Math.random() * 99999),
                    dbId: 0,
                    dataUrl: e.target.result,
                    style: '',
                    background: '',
                    fileId: '',
                    isPrimary: images.length === 0,
                    isExisting: false,
                    element: null
                };

                images.push(item);
                renderImagePreview(item);
                recalculateImageStyle(item.dataUrl).then(function(style){
                    item.style = style;
                    item.element.querySelector('img').setAttribute('style', style);
                });
                getDominantColorFromDataUrl(item.dataUrl).then(function(color){
                    item.background = 'rgb('+color.r+', '+color.g+', '+color.b+')';
                });
                syncPrimaryIndicators();
                openCrop(item.dataUrl, item.id);
            };
            reader.readAsDataURL(file);
        });
    }

    function dataURLToBlob(dataURL){
        const byteString = atob(dataURL.split(',')[1]);
        const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for(let i = 0; i < byteString.length; i++){
            ia[i] = byteString.charCodeAt(i);
        }
        return new Blob([ab], {type: mimeString});
    }

    function uploadSingleImage(image){
        if(image.isExisting){
            return Promise.resolve({
                db_id: image.dbId,
                url: image.dataUrl,
                fileId: image.fileId,
                style: image.style,
                background: image.background,
                existing: true
            });
        }

        return new Promise(function(resolve, reject){
            fetch('/fonctions/auth.php')
                .then(function(res){ return res.json(); })
                .then(function(auth){
                    imagekit.upload({
                        file: dataURLToBlob(image.dataUrl),
                        fileName: `${config.storeSlug || 'admin'}_${Date.now()}.webp`,
                        folder: '/OhNous/articles/',
                        token: auth.token,
                        signature: auth.signature,
                        expire: auth.expire
                    }, function(err, result){
                        if(err){
                            reject(err);
                            return;
                        }

                        resolve({
                            db_id: 0,
                            url: result.url,
                            fileId: result.fileId,
                            style: image.style,
                            background: image.background,
                            existing: false
                        });
                    });
                })
                .catch(reject);
        });
    }

    function preloadExistingImages(){
        (config.existingImages || []).forEach(function(item){
            images.push({
                id: item.id,
                dbId: item.dbId,
                dataUrl: item.dataUrl,
                style: item.style || '',
                background: item.background || '',
                fileId: item.fileId || '',
                isPrimary: !!item.isPrimary,
                isExisting: true,
                element: null
            });
        });

        images.forEach(function(item){
            renderImagePreview(item);
            if(item.style && item.element){
                item.element.querySelector('img').setAttribute('style', item.style);
            }
        });
        syncPrimaryIndicators();
    }

    function markSelectedType(){
        document.querySelectorAll('.choix_type').forEach(function(element){
            element.classList.toggle('active', element.id === String(selectedType));
        });
    }

    function markSelectedTailles(){
        document.querySelectorAll('.choix_taille').forEach(function(element){
            element.classList.toggle('active', selectedTailles.includes(String(element.id)));
        });
    }

    function fetchTypes(categoryId, callback){
        $('#table_types').html('');
        $('#table_tailles').html('');
        taillesContainer.classList.add('null');

        if(Number(categoryId) <= 0){
            typesContainer.classList.add('null');
            if(typeof callback === 'function'){
                callback();
            }
            return;
        }

        $.post('/fonctions/fetch_types.php', {category_id: categoryId}, function(data){
            if(data.result === 'ok' && data.msg.trim() !== ''){
                typesContainer.classList.remove('null');
                $('#table_types').html(data.msg);
                markSelectedType();
            }else{
                typesContainer.classList.add('null');
            }

            if(typeof callback === 'function'){
                callback();
            }
        }, 'json');
    }

    function fetchTailles(typesId, callback){
        $('#table_tailles').html('');

        if(Number(typesId) <= 0){
            taillesContainer.classList.add('null');
            if(typeof callback === 'function'){
                callback();
            }
            return;
        }

        $.post('/fonctions/fetch_tailles.php', {types_id: typesId}, function(data){
            if(data.result === 'ok' && data.msg.trim() !== ''){
                taillesContainer.classList.remove('null');
                $('#table_tailles').html(data.msg);
                markSelectedTailles();
            }else{
                taillesContainer.classList.add('null');
            }

            if(typeof callback === 'function'){
                callback();
            }
        }, 'json');
    }

    window.choixTypes = function(id){
        selectedType = String(id);
        selectedTailles = [];
        markSelectedType();
        fetchTailles(id);
    };

    window.choixTailles = function(id){
        const value = String(id);
        const index = selectedTailles.indexOf(value);
        if(index >= 0){
            selectedTailles.splice(index, 1);
        }else{
            selectedTailles.push(value);
        }
        markSelectedTailles();
    };

    categorySelect.addEventListener('change', function(){
        selectedType = '';
        selectedTailles = [];
        fetchTypes(this.value);
    });

    uploadZone.addEventListener('dragover', function(e){
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', function(){
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', function(e){
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    openFilePicker.addEventListener('click', function(){
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e){
        handleFiles(e.target.files);
    });

    preloadExistingImages();

    if(Number(config.selectedCategory || 0) > 0){
        fetchTypes(config.selectedCategory, function(){
            if(Number(selectedType || 0) > 0){
                fetchTailles(selectedType);
            }
        });
    }

    form.addEventListener('submit', async function(e){
        e.preventDefault();

        if(images.length === 0){
            showError("Ajoutez au moins une image.");
            return;
        }
        if(document.getElementById('nom_article').value.trim() === ''){
            showError("Entrez le nom de l'article.");
            return;
        }
        if(document.getElementById('prix_article').value.trim() === ''){
            showError("Entrez le prix de l'article.");
            return;
        }
        if(Number(categorySelect.value) <= 0){
            showError("Choisissez une catégorie.");
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;
        button.setAttribute('disabled', '');
        button.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;

        try{
            const uploadedImages = [];
            for(const image of images){
                uploadedImages.push(await uploadSingleImage(image));
            }

            if(promoActiveInput && promoActiveInput.checked && promoPriceInput && promoPriceInput.value.trim() === ''){
                showError("Entrez le prix promotionnel de l'article.");
                button.removeAttribute('disabled');
                button.innerHTML = tempText;
                return;
            }

            if(
                promoActiveInput
                && promoActiveInput.checked
                && promoPriceInput
                && promoPriceInput.value.trim() !== ''
                && Number(promoPriceInput.value) >= Number(document.getElementById('prix_article').value)
            ){
                showError("Le prix promotionnel doit être inférieur au prix normal.");
                button.removeAttribute('disabled');
                button.innerHTML = tempText;
                return;
            }

            $.post(config.submitUrl || '/fonctions/admin_article_actions.php', {
                action: config.actionName || 'update_article',
                article_id: config.articleId,
                nom: document.getElementById('nom_article').value.trim(),
                prix: document.getElementById('prix_article').value.trim(),
                categorie: categorySelect.value,
                types: selectedType,
                tailles: selectedTailles.join(','),
                reserve: document.getElementById('reserve_article').checked ? 1 : 0,
                promo_actif: document.getElementById('promo_actif_article') ? (document.getElementById('promo_actif_article').checked ? 1 : 0) : 0,
                promo_prix: document.getElementById('promo_prix_article') ? document.getElementById('promo_prix_article').value.trim() : '',
                description: document.getElementById('description_article').value.trim(),
                product_images: JSON.stringify(uploadedImages)
            }, function(data){
                if(data.result !== 'ok'){
                    showError(data.msg || "Impossible de modifier l'article.");
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: data.msg,
                    confirmButtonColor: '#6775d6'
                }).then(function(){
                    window.location = data.redirect || config.redirectUrl || '/article/' + (config.articleSlug || '');
                });
            }, 'json').always(function(){
                button.removeAttribute('disabled');
                button.innerHTML = tempText;
            });
        }catch(error){
            console.error(error);
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
            showError("Une erreur est survenue pendant l'envoi des images.");
        }
    });
})(jQuery);
