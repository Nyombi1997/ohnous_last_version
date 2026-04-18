(function($){
    var imagekit = new ImageKit({
        publicKey: "public_RBnOctCZRQjH0d5pMKWrl8jQ/zI=",
        urlEndpoint: "https://ik.imagekit.io/nyombi1997"
    });

    var config = window.ohnousAdminStoreProfileConfig || {};
    var uploadZone = document.getElementById('uploadZone');
    var fileInput = document.getElementById('fileInput');
    var imagePreview = document.getElementById('imagePreview');
    var validateButton = document.getElementById('valide_photo_profile');
    var openPickerButton = document.getElementById('open_store_profile_picker');
    var cropper = null;
    var currentCropImage = null;
    var currentPreviewId = '';
    var currentCroppedDataUrl = '';
    var currentBackground = '';

    if(!uploadZone || !fileInput || !imagePreview || !validateButton){
        return;
    }

    function showError(message){
        Swal.fire({
            icon: "error",
            title: message,
            confirmButtonColor: '#6775d6'
        });
    }

    function showSuccess(message){
        Swal.fire({
            icon: "success",
            title: message,
            confirmButtonColor: '#6775d6',
            timer: 1500
        });
    }

    function validateFile(file) {
        var maxSize = 10 * 1024 * 1024;
        var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if(file.size > maxSize) {
            showError("Le fichier sélectionné dépasse 10 Mo.");
            return false;
        }

        if(allowedTypes.indexOf(file.type) === -1) {
            showError("Le type d'image sélectionné n'est pas supporté.");
            return false;
        }

        return true;
    }

    function removeImage(imageId) {
        var index = document.getElementById(imageId);
        if(index && index.parentElement){
            index.parentElement.removeChild(index);
        }
        currentPreviewId = '';
        currentCroppedDataUrl = '';
        validateButton.style.display = 'none';
        validateButton.setAttribute('disabled', '');
    }

    function getDominantColorFromDataUrl(dataUrl) {
        return new Promise(function(resolve, reject){
            var img = new Image();
            img.crossOrigin = "anonymous";
            img.src = dataUrl;

            img.onload = function(){
                var canvas = document.createElement("canvas");
                var ctx = canvas.getContext("2d");
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                ctx.drawImage(img, 0, 0);

                var data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                var r = 0;
                var g = 0;
                var b = 0;
                var count = data.length / 4;

                for (var i = 0; i < data.length; i += 4) {
                    r += data[i];
                    g += data[i + 1];
                    b += data[i + 2];
                }

                resolve('rgb(' + Math.round(r / count) + ', ' + Math.round(g / count) + ', ' + Math.round(b / count) + ')');
            };

            img.onerror = reject;
        });
    }

    function createPreviewItem(imageId, dataUrl) {
        imagePreview.innerHTML = '';
        var item = document.createElement('div');
        item.className = 'preview-item';
        item.id = imageId;
        item.innerHTML = `
            <img src="${dataUrl}" alt="Preview" id="preview_${imageId}">
            <div class="preview-actions">
                <button type="button" class="btn-crop"><i class="fa-solid fa-crop-simple"></i></button>
                <button type="button" class="btn-remove"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="crop-indicator">✓</div>
        `;

        item.querySelector('.btn-crop').addEventListener('click', function(){
            openCrop(imageId, currentCroppedDataUrl || dataUrl);
        });

        item.querySelector('.btn-remove').addEventListener('click', function(){
            removeImage(imageId);
        });

        imagePreview.appendChild(item);
        return item;
    }

    function openCrop(imageRef, imageUrl) {
        if(!imageUrl){
            return;
        }

        document.body.classList.add('blocked_scroll');
        currentCropImage = imageRef;
        document.getElementById('cropImage').src = imageUrl;
        document.getElementById('cropModal').style.display = 'flex';

        setTimeout(function(){
            if(cropper){
                cropper.destroy();
            }

            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 0.8,
                responsive: true,
                preview: '.crop-preview',
                cropBoxResizable: true,
                movable: true,
                zoomable: true,
                scalable: true
            });
        }, 100);
    }

    window.closeCrop = function() {
        document.getElementById('cropModal').style.display = 'none';
        if(cropper){
            cropper.destroy();
            cropper = null;
        }
        currentCropImage = null;
        document.body.classList.remove('blocked_scroll');
    };

    window.applyCrop = function() {
        if(!cropper || !currentCropImage){
            return;
        }

        var canvas = cropper.getCroppedCanvas({
            width: 1067,
            height: 800
        });

        currentCroppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
        var previewImg = document.querySelector('#' + currentCropImage + ' img');
        if(previewImg){
            previewImg.src = currentCroppedDataUrl;
        }

        getDominantColorFromDataUrl(currentCroppedDataUrl).then(function(color){
            currentBackground = color;
        });

        validateButton.style.display = 'inline-flex';
        validateButton.removeAttribute('disabled');
        closeCrop();
    };

    function handleFiles(files) {
        Array.from(files).forEach(function(file){
            if(validateFile(file)){
                previewImageFile(file);
            }
        });
    }

    function previewImageFile(file) {
        var reader = new FileReader();
        reader.onload = function(e){
            currentPreviewId = 'temp_' + Date.now();
            createPreviewItem(currentPreviewId, e.target.result);
            currentCroppedDataUrl = e.target.result;
            openCrop(currentPreviewId, e.target.result);
        };
        reader.readAsDataURL(file);
    }

    function dataURLToBlob(dataURL) {
        var byteString = atob(dataURL.split(',')[1]);
        var mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
        var ab = new ArrayBuffer(byteString.length);
        var ia = new Uint8Array(ab);

        for (var i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }

        return new Blob([ab], { type: mimeString });
    }

    function deleteImage(fileId) {
        $.post("/fonctions/delete.php", {
            fileId: fileId
        });
    }

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

    uploadZone.addEventListener('click', function(){
        fileInput.click();
    });

    if(openPickerButton){
        openPickerButton.addEventListener('click', function(e){
            e.preventDefault();
            fileInput.click();
        });
    }

    fileInput.addEventListener('change', function(e){
        handleFiles(e.target.files);
    });

    document.getElementById('productForm').addEventListener('submit', function(e){
        e.preventDefault();

        if(currentCroppedDataUrl === ''){
            showError("Sélectionnez et recadrez une image avant de continuer.");
            return;
        }

        var loadingTimer = null;
        var elapsed = 0;
        var loadingActive = false;

        function startLoading(){
            loadingActive = true;
            Swal.fire({
                title: "Chargement de la photo de profil",
                html: '<p>Chargement en cours...</p><small>Temps écoulé : <b><span id="swal-timer">0</span>s</b></small>',
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function(){
                    Swal.showLoading();
                    loadingTimer = setInterval(function(){
                        elapsed += 100;
                        var timerSpan = document.getElementById("swal-timer");
                        if(timerSpan){
                            timerSpan.textContent = Math.floor(elapsed / 1000);
                        }
                    }, 100);
                },
                willClose: function(){
                    clearInterval(loadingTimer);
                    loadingActive = false;
                }
            });
        }

        function stopLoading(title, message, type){
            clearInterval(loadingTimer);
            loadingActive = false;
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                confirmButtonColor: '#6775d6',
                timer: type === 'success' ? 1500 : undefined
            }).then(function(){
                if(type === 'success'){
                    window.location = config.redirectUrl || '/admin-boutiques';
                }
            });
        }

        window.addEventListener("beforeunload", function (event) {
            if (loadingActive) {
                event.preventDefault();
                event.returnValue = "";
            }
        });

        startLoading();

        fetch("/fonctions/auth.php")
            .then(function(res){ return res.json(); })
            .then(function(auth){
                imagekit.upload({
                    file: dataURLToBlob(currentCroppedDataUrl),
                    fileName: (config.storeSlug || 'boutique') + "_" + Date.now() + ".webp",
                    folder: "/OhNous/profile/",
                    token: auth.token,
                    signature: auth.signature,
                    expire: auth.expire
                }, function(err, result){
                    if(err){
                        stopLoading("Erreur", "Une erreur est survenue pendant l'envoi de l'image.", "error");
                        return;
                    }

                    $.post("/fonctions/admin_upload_store_profile.php", {
                        store_id: config.storeId || 0,
                        product_image_url: result.url,
                        fileId: result.fileId,
                        background: currentBackground
                    }, function(data){
                        if(data.result === "ok")
                        {
                            if(config.fileId){
                                deleteImage(config.fileId);
                            }
                            stopLoading("Succès", "La photo de profil a été changée avec succès.", "success");
                        }
                        else
                        {
                            stopLoading("Erreur", data.msg || "Impossible de mettre à jour la boutique.", "error");
                        }
                    }, "json");
                });
            })
            .catch(function(){
                stopLoading("Erreur", "Impossible d'initialiser l'upload ImageKit.", "error");
            });
    });
})(jQuery);
