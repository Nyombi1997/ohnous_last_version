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
    const articleNameMaxLength = 150;
    const articleNameWarningLength = 130;
    const articleNameInput = document.getElementById('nom_article');
    const articleNameLimitHint = document.getElementById('article_name_limit_hint');

    if(!form){
        return;
    }

    const images = [];
    const deletedFileIds = [];
    let croppieUploader = window.initCroppieUploader({
        container: '#croppieCropImage',
        mode: 'article'
    });
    let currentImageId = null;
    let selectedType = String(config.selectedType || '');
    let selectedTailles = Array.isArray(config.selectedTailles) ? config.selectedTailles.map(String) : [];
    let initialSnapshot = '';

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

    function showSuccess(message){
        Swal.fire({
            icon: 'success',
            title: message,
            confirmButtonColor: '#6775d6',
            timer: 1200,
            showConfirmButton: false
        });
    }

    function showInfo(message){
        Swal.fire({
            icon: 'info',
            title: message,
            confirmButtonColor: '#6775d6'
        });
    }

    function showSavingLoading(hasImagekitDelete){
        Swal.fire({
            title: hasImagekitDelete ? "Enregistrement et suppression des anciennes images..." : "Enregistrement de l’article...",
            text: "Merci de patienter.",
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function(){
                Swal.showLoading();
            }
        });
    }

    function updateArticleNameLimitHint(){
        if(!articleNameInput || !articleNameLimitHint){
            return;
        }

        const length = articleNameInput.value.trim().length;
        if(length < articleNameWarningLength){
            articleNameLimitHint.textContent = '';
            articleNameLimitHint.classList.remove('is-visible', 'is-limit');
            return;
        }

        articleNameLimitHint.textContent = length >= articleNameMaxLength
            ? "Limite atteinte : 150 caractères maximum."
            : "Le nom de l'article est limité à 150 caractères. " + (articleNameMaxLength - length) + " restants.";
        articleNameLimitHint.classList.add('is-visible');
        articleNameLimitHint.classList.toggle('is-limit', length >= articleNameMaxLength);
    }

    if(articleNameInput){
        articleNameInput.addEventListener('input', updateArticleNameLimitHint);
        updateArticleNameLimitHint();
    }

    function getFailedImagekitDeletes(results){
        const failed = [];
        Object.keys(results || {}).forEach(function(fileId){
            const result = results[fileId];
            if(!result || result.success !== true){
                failed.push({
                    fileId: fileId,
                    result: result
                });
            }
        });
        return failed;
    }

    function getArticleSnapshot(){
        return JSON.stringify({
            nom: document.getElementById('nom_article').value.trim(),
            prix: document.getElementById('prix_article').value.trim(),
            categorie: String(categorySelect.value || ''),
            types: String(selectedType || ''),
            tailles: selectedTailles.slice().map(String).sort(),
            reserve: document.getElementById('reserve_article').checked ? 0 : 1,
            promo_actif: promoActiveInput ? (promoActiveInput.checked ? 1 : 0) : 0,
            promo_prix: promoPriceInput ? promoPriceInput.value.trim() : '',
            description: document.getElementById('description_article').value.trim(),
            images: images.map(function(image, index){
                return {
                    dbId: Number(image.dbId || 0),
                    url: image.dataUrl,
                    fileId: image.fileId || '',
                    oldFileId: image.oldFileId || '',
                    style: image.style || '',
                    background: image.background || '',
                    needsUpload: image.needsUpload ? 1 : 0,
                    order: index
                };
            }),
            deletedFileIds: deletedFileIds.slice().sort()
        });
    }

    function rememberDeletedFileId(fileId){
        fileId = String(fileId || '').trim();
        if(fileId !== '' && deletedFileIds.indexOf(fileId) === -1){
            deletedFileIds.push(fileId);
        }
    }

    function hasArticleChanges(){
        return initialSnapshot !== '' && getArticleSnapshot() !== initialSnapshot;
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
        document.getElementById('cropModal').style.display = 'flex';

        setTimeout(function(){
            croppieUploader.init(dataUrl);
        }, 100);
    }

    window.closeCrop = function(){
        document.getElementById('cropModal').style.display = 'none';
        document.body.classList.remove('blocked_scroll');
        croppieUploader.destroy();
        currentImageId = null;
    };

    window.applyCrop = function(){
        if(!croppieUploader.hasImage() || !currentImageId){
            return;
        }

        const imageItem = images.find(function(item){ return item.id === currentImageId; });
        if(!imageItem){
            closeCrop();
            return;
        }

        croppieUploader.result().then(function(dataUrl){
            imageItem.dataUrl = dataUrl;
            imageItem.needsUpload = true;
            imageItem.isExisting = false;
            rememberDeletedFileId(imageItem.oldFileId || imageItem.fileId);
            imageItem.element.querySelector('img').src = dataUrl;

            recalculateImageStyle(dataUrl).then(function(style){
                imageItem.style = style;
                imageItem.element.querySelector('img').setAttribute('style', style);
            });
            getDominantColorFromDataUrl(dataUrl).then(function(color){
                imageItem.background = 'rgb('+color.r+', '+color.g+', '+color.b+')';
            });

            closeCrop();
            showSuccess("Image recadrée.");
        });
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
            openCrop(item.dataUrl, item.id);
        });

        element.querySelector('.btn-remove').addEventListener('click', function(){
            const index = images.findIndex(function(image){ return image.id === item.id; });
            if(index >= 0){
                rememberDeletedFileId(images[index].oldFileId || images[index].fileId);
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
        const selectedFiles = Array.from(files);

        selectedFiles.forEach(function(file){
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
                    needsUpload: true,
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
        if(!image.needsUpload){
            console.log('[OhNous edit article] Image conservée sans upload ImageKit', {
                dbId: image.dbId,
                fileId: image.fileId,
                oldFileId: image.oldFileId
            });
            return Promise.resolve({
                db_id: image.dbId,
                url: image.dataUrl,
                fileId: image.fileId,
                old_fileId: image.oldFileId || image.fileId,
                style: image.style,
                background: image.background,
                existing: true
            });
        }

        return new Promise(function(resolve, reject){
            console.log('[OhNous edit article] Upload ImageKit démarré', {
                dbId: image.dbId || 0,
                oldFileId: image.oldFileId || image.fileId || '',
                needsUpload: image.needsUpload
            });

            fetch('/fonctions/auth.php')
                .then(function(res){
                    console.log('[OhNous edit article] Réponse auth ImageKit', {
                        status: res.status,
                        ok: res.ok
                    });
                    return res.json();
                })
                .then(function(auth){
                    console.log('[OhNous edit article] Auth ImageKit JSON', auth);
                    imagekit.upload({
                        file: dataURLToBlob(image.dataUrl),
                        fileName: `${config.storeSlug || 'admin'}_${Date.now()}.webp`,
                        folder: '/OhNous/articles/',
                        token: auth.token,
                        signature: auth.signature,
                        expire: auth.expire
                    }, function(err, result){
                        if(err){
                            console.log('[OhNous edit article] Erreur upload ImageKit', err);
                            reject(err);
                            return;
                        }

                        console.log('[OhNous edit article] Upload ImageKit OK', {
                            url: result.url,
                            fileId: result.fileId,
                            oldFileId: image.oldFileId || image.fileId || ''
                        });

                        resolve({
                            db_id: image.dbId || 0,
                            url: result.url,
                            fileId: result.fileId,
                            old_fileId: image.oldFileId || image.fileId || '',
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
                oldFileId: item.fileId || '',
                isPrimary: !!item.isPrimary,
                isExisting: true,
                needsUpload: false,
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

    function setInitialSnapshot(){
        initialSnapshot = getArticleSnapshot();
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

    openFilePicker.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        fileInput.value = '';
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e){
        if(!e.target.files || e.target.files.length === 0){
            return;
        }
        handleFiles(e.target.files);
        e.target.value = '';
    });

    preloadExistingImages();

    if(Number(config.selectedCategory || 0) > 0){
        fetchTypes(config.selectedCategory, function(){
            if(Number(selectedType || 0) > 0){
                fetchTailles(selectedType, setInitialSnapshot);
            }else{
                setInitialSnapshot();
            }
        });
    }else{
        setInitialSnapshot();
    }

    form.addEventListener('submit', async function(e){
        e.preventDefault();

        if(images.length === 0){
            showError("Ajoutez au moins une image.");
            return;
        }
        const articleName = document.getElementById('nom_article').value.trim();
        if(articleName === ''){
            showError("Entrez le nom de l'article.");
            return;
        }
        if(articleName.length > articleNameMaxLength){
            showError("Le nom de l'article ne doit pas dépasser 150 caractères.");
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
        if(promoActiveInput && promoActiveInput.checked && promoPriceInput && promoPriceInput.value.trim() === ''){
            showError("Entrez le prix promotionnel de l'article.");
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
            return;
        }
        if(!hasArticleChanges()){
            showInfo("Aucune modification n'a été faite.");
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        const tempText = button.innerHTML;
        button.setAttribute('disabled', '');
        button.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;
        showSavingLoading(deletedFileIds.length > 0);

        try{
            const uploadedImages = [];
            for(const image of images){
                uploadedImages.push(await uploadSingleImage(image));
            }

            const ajaxPayload = {
                action: config.actionName || 'update_article',
                article_id: config.articleId,
                nom: articleName,
                prix: document.getElementById('prix_article').value.trim(),
                categorie: categorySelect.value,
                types: selectedType,
                tailles: selectedTailles.join(','),
                reserve: document.getElementById('reserve_article').checked ? 0 : 1,
                promo_actif: document.getElementById('promo_actif_article') ? (document.getElementById('promo_actif_article').checked ? 1 : 0) : 0,
                promo_prix: document.getElementById('promo_prix_article') ? document.getElementById('promo_prix_article').value.trim() : '',
                description: document.getElementById('description_article').value.trim(),
                product_images: JSON.stringify(uploadedImages),
                deleted_fileIds: JSON.stringify(deletedFileIds)
            };

            console.log('[OhNous edit article] Payload envoyé au serveur', {
                url: config.submitUrl || '/fonctions/admin_article_actions.php',
                images: uploadedImages,
                deletedFileIds: deletedFileIds,
                payload: ajaxPayload
            });

            $.ajax({
                url: config.submitUrl || '/fonctions/admin_article_actions.php',
                method: 'POST',
                dataType: 'json',
                data: ajaxPayload
            }).done(function(data){
                console.log('[OhNous edit article] Réponse serveur OK', data);
                console.log('[OhNous edit article] Suppression ImageKit serveur', {
                    deletedFileIds: data.imagekit_deleted_fileIds || [],
                    results: data.imagekit_delete_results || {}
                });
                const failedDeletes = getFailedImagekitDeletes(data.imagekit_delete_results || {});
                failedDeletes.forEach(function(item){
                    console.error('[OhNous edit article] Suppression ImageKit échouée', item);
                    console.error('[OhNous edit article] Détail suppression ImageKit', JSON.stringify(item, null, 2));
                });
                if(data.result !== 'ok'){
                    showError(data.msg || "Impossible de modifier l'article.");
                    return;
                }

                if(failedDeletes.length > 0){
                    Swal.fire({
                        icon: 'error',
                        title: "Article enregistré, mais suppression ImageKit échouée.",
                        text: "Vérifiez la console pour le détail ImageKit.",
                        confirmButtonColor: '#6775d6',
                        allowOutsideClick: false
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: data.msg || "Modification enregistrée.",
                    text: "Vos modifications ont été enregistrées.",
                    confirmButtonColor: '#6775d6',
                    allowOutsideClick: false
                }).then(function(){
                    window.location = data.redirect || config.redirectUrl || '/article/' + (config.articleSlug || '');
                });
            }).fail(function(xhr){
                var message = "L'enregistrement a échoué.";
                console.log('[OhNous edit article] Échec AJAX', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseJSON: xhr.responseJSON,
                    responseText: xhr.responseText
                });
                if(xhr.responseJSON && xhr.responseJSON.msg){
                    message = xhr.responseJSON.msg;
                }
                Swal.fire({
                    icon: 'error',
                    title: message,
                    confirmButtonColor: '#6775d6',
                    allowOutsideClick: false
                });
            }).always(function(){
                button.removeAttribute('disabled');
                button.innerHTML = tempText;
            });
        }catch(error){
            console.error(error);
            button.removeAttribute('disabled');
            button.innerHTML = tempText;
            Swal.fire({
                icon: 'error',
                title: "Une erreur est survenue pendant l'envoi des images.",
                confirmButtonColor: '#6775d6',
                allowOutsideClick: false
            });
        }
    });
})(jQuery);
