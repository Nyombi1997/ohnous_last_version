<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* SI ON EST CONNECTER */
    if(isset($_SESSION['store_ohnous_987654321']))
    {
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['store_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)!=0)
        {
            $boutique = $boutique[0];
            $backgrounds = "";
            if($boutique['backgrounds']!='')
            {
                $backgrounds = 'style="background : '.$boutique['backgrounds'].';"';
            }
            $profile = '<img src="'.ASSET.'images/profile/default.jpg" alt="" srcset="">';
            if($boutique['profile']!='')
            {
                $profile = '
                            <img 
                                class="blur-up"
                                src="'.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                                srcset="
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                                    '.$boutique['profile'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                                sizes="(max-width:768px) 90vw, 600px"
                                loading="lazy"
                                class="blur-up"
                            />';
            }
        }
        else
        {
            // Rediriger vers une page d'erreur ou afficher un message
            header("Location:/404");
            exit();
        }
    }
    else
    {
        // Rediriger vers une page d'erreur ou afficher un message
        header("Location:/404");
        exit();
    }
?>
<script>
    let home_page = true;
</script>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="<?=  ASSET ?>css/style_ajout_image.css?<?= filemtime("asset/css/style_ajout_image.css") ?>">
    <link rel="stylesheet" href="<?=  ASSET ?>css/style.css?<?= filemtime("asset/css/style.css") ?>">
    <link rel="stylesheet" href="<?=  ASSET ?>css/cropper.min.css">
    <script src="<?=  ASSET ?>js/cropper.min.js?<?= filemtime("asset/js/cropper.min.js") ?>"></script>

    <script src="https://unpkg.com/imagekit-javascript/dist/imagekit.min.js"></script>
    <!-- <script  src="https://unpkg.com/@imagekit/javascript@5.0.0/dist/imagekit.min.js"></script> -->
</head>
<body>
    <span id="fileId" data-file="<?= $boutique['fileId'] ?>"></span>
    <div class="container_ajout_image">
        <h1 class="titre_ajout_image">Modifier votre photo de profile</h1>
        
        <form id="productForm" enctype="multipart/form-data">
            <!-- ajout image -->
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Images du produit</label>
                
                <!-- Zone upload -->
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-content">
                        <span class="upload-icon"><i class="fa-solid fa-folder-open"></i></span>
                        <p>Glissez-déposez votre image ici</p>
                        <p class="upload-subtext">ou</p>
                        <button type="button" class="btn_ohnous btn-primary" onclick="document.getElementById('fileInput').click()">
                            Choisir cliquez ici
                        </button>
                        <input type="file" id="fileInput" multiple accept="image/*" class="input_ajout_image" style="display: none;">
                    </div>
                </div>
                
                <!-- Prévisualisation -->
                <div class="image-preview" id="imagePreview"></div>
            </div>
            
            <button type="submit" class="btn_ohnous btn-success" style="display: none;" id="valide_photo_profile" disabled>Modifier la photo de profile</button>
        </form>
    </div>

    <!-- Modal Crop -->
    <div class="modal" id="cropModal">
        <!-- background -->
        <div class="modal_background" onclick="closeCrop()"></div>
        <!-- content -->
        <div class="modal-content">
            <div class="modal-header">
                <h3>Recadrer l'image</h3>
                <button class="close" onclick="closeCrop()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="crop-container">
                    <img id="cropImage" src="">
                </div>
                <div class="crop-preview"></div>
            </div>
            <div class="modal-footer">
                <button class="btn_ohnous btn-secondary" onclick="closeCrop()">Annuler</button>
                <button class="btn_ohnous btn-primary" onclick="applyCrop()">Appliquer</button>
            </div>
        </div>
    </div>
    <script>

        /* imagekit */
        var imagekit = new ImageKit({
            publicKey: "public_RBnOctCZRQjH0d5pMKWrl8jQ/zI=",
            urlEndpoint: "https://ik.imagekit.io/nyombi1997"
        });


        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        /* les images en cours */
        const images = [];
        /* details du cropper */        
        let imageId = 'temp_' + Date.now();
        let cropper = null;
        let currentCropImage = null;
        let aspectRatio = 4/3;
        let firstOpen = false;
        let firstImage = '';
        let rgb = '';
        let styles = '',
        valide_photo_profile = document.getElementById("valide_photo_profile");
        
        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        /* quand on drop */
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            /* si c'est la première fois que l'on ouvre l'image */
            firstOpen = true;
            handleFiles(e.dataTransfer.files);
        });
        // Click sur zone
        uploadZone.addEventListener('click', () => {
            fileInput.click();
        });
        /* clique pour charger l'image */
        fileInput.addEventListener('change', (e) => {
            /* si c'est la première fois que l'on ouvre l'image */
            firstOpen = true;
            handleFiles(e.target.files);
        });
        /* ouvrir le crop */
        function openCrop(imageRef,imageId) {
            if (!imageId) return;

            /* blocker le scroll du body */
            document.body.classList.add('blocked_scroll');
        
            currentCropImage = imageRef;

            document.getElementById('cropImage').src = imageId;
            document.getElementById('cropModal').style.display = 'flex';
            
            // Initialiser cropper
            setTimeout(() => {
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(document.getElementById('cropImage'), {
                    aspectRatio: 1, // free crop
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
        /* fermer le croppe */
        function closeCrop() {
            /* si c'est la première fois que l'on ouvre l'image */
            if(firstOpen==true)
            {
                removeImage(firstImage);
                firstImage = '';
                firstOpen = false;
            }
            document.getElementById('cropModal').style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            currentCropImage = null;
            /* débloquer le scroll du body */
            document.body.classList.remove('blocked_scroll');
        }
        /* vérifier l'image */
        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (this.validateFile(file)) {
                    this.previewImage(file);
                }
            });
        }
        /* préparer la preview de l'image */
        function previewImage(file) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                imageId = 'temp_' + Date.now();
                const previewItem = this.createPreviewItem(imageId, e.target.result);
                /* si c'est la première fois que l'on ouvre l'image */
                if(firstOpen == true)
                {
                    firstImage = imageId;
                }
                openCrop(imageId , e.target.result);
                
                images.push({
                    id: imageId,
                    file: file,
                    element: previewItem,
                    originalDataUrl: e.target.result,
                    croppedDataUrl: null,
                    isCropped: false,
                    isPrimary: images.length === 0
                });
                
                //this.updatePrimaryBadge();
            };
            
            reader.readAsDataURL(file);
        }    
        
        /* vérifier si l'image */
        function validateFile(file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (file.size > maxSize) {
                Swal.fire({
                    icon: "error",
                    title: `❌ Fichier trop volumineux: ${file.name} (${(file.size/1024/1024).toFixed(1)}MB)`,
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6"
                })
                return false;
            }
            
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    icon: "error",
                    title: `❌ Type non supporté: ${file.name}`,
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6"
                })
                return false;
            }
            
            return true;
        }
        /* création de la prévisualisation */
        function createPreviewItem(imageId, dataUrl) {
            const preview = document.getElementById('imagePreview');
            const item = document.createElement('div');
            item.className = 'preview-item';
            item.dataset.imageId = imageId;
            item.id = imageId;
            item.innerHTML = `
                <img src="${dataUrl}" alt="Preview" id="${imageId}">
                <div class="preview-actions">
                    <button type="button" class="btn-crop" onclick="openCrop('${imageId}','${dataUrl}')"><i class="fa-solid fa-crop-simple"></i></button>
                    <button type="button" class="btn-remove" onclick="removeImage('${imageId}')"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="crop-indicator">✓</div>
                <div class="primary-badge" style="display: none;">Principale</div>
            `;
            // Obtenir la couleur dominante
            getDominantColorFromDataUrl(dataUrl).then(color => {
                rgb = 'rgb('+color.r+', '+color.g+', '+color.b+')';
            });

            preview.appendChild(item);
            return item;
        }
        /* donner la couleur dominante */
        function getDominantColorFromDataUrl(dataUrl) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.src = dataUrl;

                img.onload = () => {
                    const canvas = document.createElement("canvas");
                    const ctx = canvas.getContext("2d");

                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;

                    ctx.drawImage(img, 0, 0);

                    const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

                    let r = 0, g = 0, b = 0;
                    const count = data.length / 4;

                    for (let i = 0; i < data.length; i += 4) {
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


        /* Appliquer le croppe */
        function applyCrop() {
            if (!cropper || !currentCropImage) return;

            const canvas = cropper.getCroppedCanvas({
                width: 1067,
                height: 800
            });

            const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);

            // Sauvegarder l'id dans une variable locale pour le then
            const imgId = currentCropImage;

            // Mettre à jour preview immédiatement
            const imgElement = document.querySelector("img#" + imgId);
            if (imgElement) {
                imgElement.src = croppedDataUrl;
            }
            // Obtenir la couleur dominante
            getDominantColorFromDataUrl(croppedDataUrl).then(color => {
                rgb = 'rgb('+color.r+', '+color.g+', '+color.b+')';
            });

            // Reset pour le prochain crop
            if (firstOpen === true) {
                firstImage = '';
                firstOpen = false;
            }
            currentCropImage = null;

            closeCrop();
            /* afficher chargement profile */
            let loadingActive = false;
            let loadingTimer;
            let elapsed = 0;
            const maxTime = 120000; // 120 secondes indicatives

            function startLoading(message = "Veuillez patienter...") {
                loadingActive = true;
                elapsed = 0;

                Swal.fire({
                    title: "Chargement de votre photo de profile",
                    html: `
                        <p>${message}</p>
                        <small>Temps écoulé : <b><span id="swal-timer">0</span>s</b></small>
                    `,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: "Annuler",
                    didOpen: () => {
                        Swal.showLoading();

                        const timerSpan = document.getElementById("swal-timer");
                        const progressBar = Swal.getTimerProgressBar();

                        loadingTimer = setInterval(() => {
                            elapsed += 100;
                            timerSpan.textContent = Math.floor(elapsed / 1000);

                            const percent = Math.min((elapsed / maxTime) * 100, 100);
                            progressBar.style.width = percent + "%";
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(loadingTimer);
                        loadingActive = false;
                    }
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        stopLoading("Chargement annulé", "L’opération a été interrompue.", "warning");
                    }
                });
            }

            startLoading(message = "Chargement en cours...");
            /* fonction pour arrêter le chargement */
            function stopLoading(title = "Succès", message = "Chargement La photo de profile a été changer avec succès !", type = "success") {
                loadingActive = false;

                clearInterval(loadingTimer);
                Swal.fire({
                    icon: type,
                    title: title,
                    text: message,
                    confirmButtonText: "OK",
                    confirmButtonColor: '#6775d6',
                    timer: 1500
                }).then(() => {
                    window.location = "/editer-boutique";
                });
            }
            /* si on veut quitter la page ou recharger */
            window.addEventListener("beforeunload", function (e) {
                if (loadingActive) {
                    e.preventDefault();
                    e.returnValue = "";
                }
            });

            document.querySelectorAll('#imagePreview div img').forEach( function(element){
                dataURLToBlob(element.src).then( (blob) => {
                    // Récupère le token depuis ton PHP
                    fetch("/fonctions/auth.php")
                    .then(res => res.json())
                    .then(auth => {
                        imagekit.upload({
                            file: blob,
                            fileName: "<?= $boutique['slug'] ?>_" + Date.now() + ".webp",
                            folder: "/OhNous/profile/",
                            token: auth.token,
                            signature: auth.signature,
                            expire: auth.expire,
                        }, function(err, result) {
                            if(err){
                                Swal.fire({
                                    title: "Une erreur inattendue s'est produite",
                                    text: "Veuillez réessayer...",
                                    icon: "error",
                                    confirmButtonColor: '#6775d6'
                                });
                                return;
                            }
                            else{
                            }
                            $.post("/fonctions/upload_profile_boutique.php", {
                                product_image_url: result.url,
                                fileId: result.fileId,
                                style: styles,
                                background: rgb,
                            }, function(data){
                                if(data.result === "ok")
                                {
                                    let 
                                    fileId = document.getElementById("fileId");
                                    if(fileId!=undefined)
                                    {
                                        if(fileId.getAttribute("data-file")!='')
                                        {
                                            deleteImage(fileId.getAttribute("data-file"));
                                        }
                                    }
                                    stopLoading(title = "Succès", message = "La photo de profile a été changer avec succès !", type = "success", timer = 1500);
                                }
                                else
                                {
                                    Swal.fire({
                                        title: "Erreur",
                                        text: data.msg,
                                        icon: "error",
                                        confirmButtonColor: '#6775d6'
                                    });
                                    window.location.reload();
                                }
                            });
                        });
                    })
                    .catch(err => {
                    });
                });
            })
        }

    
        function removeImage(imageId) {
            let index = document.getElementById(imageId);
            index.parentElement.removeChild(index);
        }

        let form = document.getElementById('productForm');
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            /* afficher chargement profile */
            let loadingActive = false;
            let loadingTimer;
            let elapsed = 0;
            const maxTime = 120000; // 120 secondes indicatives

            function startLoading(message = "Veuillez patienter...") {
                loadingActive = true;
                elapsed = 0;

                Swal.fire({
                    title: "Chargement de votre photo de profile",
                    html: `
                        <p>${message}</p>
                        <small>Temps écoulé : <b><span id="swal-timer">0</span>s</b></small>
                    `,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: "Annuler",
                    didOpen: () => {
                        Swal.showLoading();

                        const timerSpan = document.getElementById("swal-timer");
                        const progressBar = Swal.getTimerProgressBar();

                        loadingTimer = setInterval(() => {
                            elapsed += 100;
                            timerSpan.textContent = Math.floor(elapsed / 1000);

                            const percent = Math.min((elapsed / maxTime) * 100, 100);
                            progressBar.style.width = percent + "%";
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(loadingTimer);
                        loadingActive = false;
                    }
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        stopLoading("Chargement annulé", "L’opération a été interrompue.", "warning");
                    }
                });
            }

            startLoading(message = "Chargement en cours...");
            /* fonction pour arrêter le chargement */
            function stopLoading(title = "Succès", message = "Chargement La photo de profile a été changer avec succès !", type = "success") {
                loadingActive = false;

                clearInterval(loadingTimer);
                Swal.fire({
                    icon: type,
                    title: title,
                    text: message,
                    confirmButtonText: "OK",
                    confirmButtonColor: '#6775d6',
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
            /* si on veut quitter la page ou recharger */
            window.addEventListener("beforeunload", function (e) {
                if (loadingActive) {
                    e.preventDefault();
                    e.returnValue = "";
                }
            });

            document.querySelectorAll('#imagePreview div img').forEach( function(element){
                dataURLToBlob(element.src).then( (blob) => {
                    // Récupère le token depuis ton PHP
                    fetch("/fonctions/auth.php")
                    .then(res => res.json())
                    .then(auth => {
                        imagekit.upload({
                            file: blob,
                            fileName: "<?= $boutique['slug'] ?>_" + Date.now() + ".webp",
                            folder: "/OhNous/profile/",
                            token: auth.token,
                            signature: auth.signature,
                            expire: auth.expire,
                        }, function(err, result) {
                            if(err){
                                Swal.fire({
                                    title: "Une erreur inattendue s'est produite",
                                    text: "Veuillez réessayer...",
                                    icon: "error",
                                    confirmButtonColor: '#6775d6'
                                });
                                return;
                            }
                            else{
                            }
                            $.post("/fonctions/upload_profile_boutique.php", {
                                product_image_url: result.url,
                                fileId: result.fileId,
                                style: styles,
                                background: rgb,
                            }, function(data){
                                if(data.result === "ok")
                                {
                                    let 
                                    fileId = document.getElementById("fileId");
                                    if(fileId!=undefined)
                                    {
                                        if(fileId.getAttribute("data-file")!='')
                                        {
                                            deleteImage(fileId.getAttribute("data-file"));
                                        }
                                    }
                                    stopLoading(title = "Succès", message = "La photo de profile a été changer avec succès !", type = "success", timer = 1500);
                                }
                                else
                                {
                                    Swal.fire({
                                        title: "Erreur",
                                        text: data.msg,
                                        icon: "error",
                                        confirmButtonColor: '#6775d6'
                                    });
                                    window.location.reload();
                                }
                            });
                        });
                    })
                    .catch(err => {
                    });
                });
            })
        })
        
        /* to blob */
        function dataURLToBlob(dataURL) {
            return new Promise((resolve) => {
                const byteString = atob(dataURL.split(',')[1]);
                const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
                const ab = new ArrayBuffer(byteString.length);
                const ia = new Uint8Array(ab);
                
                for (let i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                
                resolve(new Blob([ab], { type: mimeString }));
            });
        }

        function deleteImage(fileId) {
            $.post("/fonctions/delete.php", {
                fileId: fileId,
            }, function(data){
                if(data.success){
                } else {
                }
            });
        }


    </script>
</body>
</html>