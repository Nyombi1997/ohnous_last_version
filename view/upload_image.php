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
    <div class="container_ajout_image">
        <h1 class="titre_ajout_image">Ajouter un article</h1>
        
        <form id="productForm" enctype="multipart/form-data">
            <!-- ajout image -->
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Images du produit</label>
                
                <!-- Zone upload -->
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-content">
                        <span class="upload-icon"><i class="fa-solid fa-folder-open"></i></span>
                        <p>Glissez-déposez vos images ici</p>
                        <p class="upload-subtext">ou</p>
                        <button type="button" class="btn_ohnous btn-primary" onclick="document.getElementById('fileInput').click()">
                            Choisir des fichiers
                        </button>
                        <input type="file" id="fileInput" multiple accept="image/*" class="input_ajout_image" style="display: none;">
                    </div>
                </div>
                
                <!-- Prévisualisation -->
                <div class="image-preview" id="imagePreview"></div>
            </div>

            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Nom *</label>
                <input type="text" id="nom_article" name="product_name" class="input_ajout_image" required>
            </div>
            
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Prix * ($)</label>
                <input type="number" id="prix_article" name="product_price" step="0.01" min="0" class="input_ajout_image" required>
            </div>
            
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Catégories <span>*</span></label>
                <select name="product_taille" class="input_ajout_image" id="category_select">
                    <option value="0">--Choisir une catégorie--</option>
                    <?php
                        $boutique = select_bdd($bdd, "categorie", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
                        foreach($boutique as $boutique_item)
                        {
                            echo '<option value="'.$boutique_item['id'].'">'.$boutique_item['nom'].'</option>';
                        }
                    ?>
                </select>
            </div>
            
            <div class="form_group_ajout_image null" id="types_container">
                <label class="label_ajout_image">Choisir un type <span>*</span></label>
                <div class="table" id="table_types">

                </div>
            </div>
            
            <div class="form_group_ajout_image null" id="tailles_container">
                <label class="label_ajout_image">Choisir une taille <span>*</span></label>
                <div class="table" id="table_tailles">

                </div>
            </div>
            
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Boutique <span>*</span></label>
                <select name="product_boutique" id="boutique_article" class="input_ajout_image">
                    <option value="">--Choisir une boutique--</option>
                    <?php
                        $boutique = select_bdd($bdd, "boutiques", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
                        foreach($boutique as $boutique_item)
                        {
                            echo '<option value="'.$boutique_item['id'].'">'.$boutique_item['nom'].'</option>';
                        }
                    ?>
                </select>
            </div>
            
            <div class="form_group_ajout_image">
                <label class="label_ajout_image">Description (facultatif)</label>
                <textarea name="product_description" id="description_article" rows="4" class="input_ajout_image"></textarea>
            </div>
            
            <button type="submit" class="btn_ohnous btn-success">Créer l'article</button>
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
        /* input */
        let
        nom = '',
        prix = '',
        categorie = '',
        types = '',
        tailles = [],
        boutique = '',
        description = '';
        /* lire les choix de categories */
        let 
        category_select = document.getElementById('category_select'),
        types_container = document.getElementById('types_container'),
        tailles_container = document.getElementById('tailles_container');
        category_select.addEventListener('change', function(){
            let value = this.value;
            if(value == 0)
            {
                types_container.classList.add('null');
                document.querySelector('#table_types').innerHTML = "";
            }
            else
            {
                $.post('fonctions/fetch_types.php', {category_id: value}, function(data){
                    if(data.result === "ok")
                    {
                        if(data.msg.trim() == ""){
                            types_container.classList.add('null');
                            document.querySelector('#table_types').innerHTML = "";
                        }
                        else
                        {
                            types_container.classList.remove('null');
                            document.querySelector('#table_types').innerHTML = data.msg;
                        }
                    }
                    else
                    {
                        types_container.classList.add('null');
                        document.querySelector('#table_types').innerHTML = "";
                    }
                })
            }
            /* reset les tails */
            tailles_container.classList.add('null');
            document.querySelector('#table_tailles').innerHTML = "";
        });
        /* choix types */
        function choixTypes(id){
            document.querySelectorAll(".choix_type").forEach( function(element){
                if(element.id == id){
                    element.classList.add('active');
                    types = id;
                    /* trouver les tailles */
                    $.post('fonctions/fetch_tailles.php', {types_id: id}, function(data){
                        if(data.result === "ok")
                        {
                            if(data.msg.trim() == ""){
                                tailles_container.classList.add('null');
                                document.querySelector('#table_tailles').innerHTML = "";
                            }
                            else
                            {
                                tailles_container.classList.remove('null');
                                document.querySelector('#table_tailles').innerHTML = data.msg;
                            }
                        }
                        else
                        {
                            tailles_container.classList.add('null');
                            document.querySelector('#table_tailles').innerHTML = "";
                        }
                    })
                }
                else{
                    element.classList.remove('active');
                }
            })
        }
        /* choix tailles */
        function choixTailles(id){
            document.querySelectorAll(".choix_taille").forEach( function(element){
                if(element.id == id){
                    if(element.classList.contains('active')){
                        element.classList.remove('active');
                        tailles = tailles.filter(taille => taille !== id);
                    }
                    else{
                        element.classList.add('active');
                        tailles.push(id);
                    }
                }
            })
        }

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
        let styles = '';
        //const previewItem = this.createPreviewItem(imageId, e.target.result);
        
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
                    aspectRatio: NaN, // free crop
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
                alert(`❌ Fichier trop volumineux: ${file.name} (${(file.size/1024/1024).toFixed(1)}MB)`);
                return false;
            }
            
            if (!allowedTypes.includes(file.type)) {
                alert(`❌ Type non supporté: ${file.name}`);
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
            // Appliquer le style une fois l'image chargée
            recalculateImageStyle(dataUrl).then(style => {
                const img = document.querySelector("img#" + imageId);
                if (img) img.setAttribute("style", style);
                styles = style;
            });
            // Obtenir la couleur dominante
            getDominantColorFromDataUrl(dataUrl).then(color => {
                rgb = 'rgb('+color.r+', '+color.g+', '+color.b+')';
            });

            preview.appendChild(item);
            return item;
        }
        /* recalculer la taille de l'image pour lui donner un style */
        function recalculateImageStyle(imgUrl) {
            return new Promise((resolve) => {
                const img = new Image();
                img.src = imgUrl;

                img.onload = () => {
                    if (img.width > img.height) {
                        resolve('width: 100%; height: auto;');
                    } else if (img.width < img.height) {
                        resolve('width: auto; height: 100%;');
                    } else {
                        resolve('width: 100%; height: auto;');
                    }
                };
            });
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

            // Appliquer le style une fois l'image chargée
            recalculateImageStyle(croppedDataUrl).then(style => {
                const img = document.querySelector("img#" + imgId);
                if (img) img.setAttribute("style", style);
                styles = style;
            });
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
        }

    
        function removeImage(imageId) {
            let index = document.getElementById(imageId);
            index.parentElement.removeChild(index);
        }

        let form = document.getElementById('productForm');
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            /* si le nom est vide */
            if(document.getElementById("nom_article").value.trim() == "")
            {
                Swal.fire({
                    title: "Le nom est vide",
                    text: "Veuillez entrer le nom de l'article.",
                    icon: "error",
                    confirmButtonColor: '#6775d6'
                });
                document.getElementById("nom_article").focus();
                return;
            }
            /* si le prix est vide */
            else if(document.getElementById("prix_article").value.trim() == "")
            {
                Swal.fire({
                    title: "Le prix est vide",
                    text: "Veuillez entrer le prix de l'article.",
                    icon: "error",
                    confirmButtonColor: '#6775d6'
                });
                document.getElementById("prix_article").focus();
                return;
            }
            /* si le prix n'est pas valide */
            else if (document.getElementById("prix_article").value.trim() == "" && !/^\d+(\.\d{1,2})?$/.test(document.getElementById("prix_article").value.trim()))
            {
                Swal.fire({
                    title: "Le prix n'est pas valide",
                    text: "Veuillez entrer un prix valide pour l'article.",
                    icon: "error",
                    confirmButtonColor: '#6775d6'
                });
                document.getElementById("prix_article").focus();
                return;
            }
            /* si on a choisi une catégorie */
            else if (category_select.value == 0)
            {
                Swal.fire({
                    title: "Aucune catégorie sélectionnée",
                    text: "Veuillez choisir une catégorie pour l'article.",
                    icon: "error",
                    confirmButtonColor: '#6775d6'
                });
                category_select.focus();
                return;
            }
            /* si il existe des types il faudra en choisir */
            else if(types_container.classList.contains('null') == false && types == '')
            {
                types_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                Swal.fire({
                    title: "Aucun type d'article sélectionné",
                    text: "Veuillez choisir un type pour l'article.",
                    icon: "error",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }
            /* si il existe des tailles il faudra en choisir */
            else if(tailles_container.classList.contains('null') == false && tailles.length == 0)
            {
                tailles_container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                Swal.fire({
                    title: "Aucune taille sélectionné",
                    text: "Veuillez choisir une taille pour l'article.",
                    icon: "error",
                    confirmButtonColor: '#6775d6'
                });
                return;
            }
            /* afficher chargement article */
            let loadingActive = false;
            let loadingTimer;
            let elapsed = 0;
            const maxTime = 120000; // 120 secondes indicatives

            function startLoading(message = "Veuillez patienter...") {
                loadingActive = true;
                elapsed = 0;

                Swal.fire({
                    title: "Chargement de votre article",
                    html: `
                        <p>${message}</p>
                        <small>Temps écoulé : <b><span id="swal-timer">0</span>s</b></small>
                    `,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
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
            function stopLoading(title = "Succès", message = "Chargement article ajouté avec succès !", type = "success") {
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
                    fetch("<?= $lien_actuel?>/fonctions/auth.php")
                    .then(res => res.json())
                    .then(auth => {
                        imagekit.upload({
                            file: blob,
                            fileName: "crop_" + Date.now() + ".webp",
                            folder: "/OhNous/articles/",
                            token: auth.token,
                            signature: auth.signature,
                            expire: auth.expire,
                            /* onProgress: (event) => {
                                const percent = (event.loaded / event.total) * 100;
                                //console.log(`Progress: ${percent}%`);
                            }, */
                        }, function(err, result) {
                            if(err){
                                //console.error("ImageKit error:", err);
                                //alert("Upload échoué");
                                Swal.fire({
                                    title: "Une erreur inattendue s'est produite",
                                    text: "Veuillez réessayer...",
                                    icon: "error",
                                    confirmButtonColor: '#6775d6'
                                });
                                return;
                            }
                            else{
                                //console.log("Image uploaded successfully:", result);
                                //alert("Image chargé avec succès !");
                            }
                            let
                            tailles_ = '';
                            for(i=0; i<tailles.length; i++)
                            {
                                tailles_ += tailles[i];
                                if(i < tailles.length - 1)
                                {
                                    tailles_ += ",";
                                }
                            }
                            $.post("fonctions/upload_product.php", {
                                product_name: document.getElementById("nom_article").value.trim(),
                                product_price: document.getElementById("prix_article").value.trim(),
                                product_category: category_select.value,
                                product_types: types,
                                product_tailles: tailles_,
                                product_boutique: document.getElementById("boutique_article").value,
                                product_description: document.getElementById("description_article").value.trim(),
                                product_image_url: result.url,
                                style: styles,
                                background: rgb,
                            }, function(data){
                                if(data.result === "ok")
                                {
                                    stopLoading(title = "Succès", message = "Article ajouté avec succès !", type = "success", timer = 1500);
                                }
                                else
                                {
                                    Swal.fire({
                                        title: "Erreur",
                                        text: data.msg,
                                        icon: "error",
                                        confirmButtonColor: '#6775d6'
                                    });
                                }
                            });
                            //document.getElementById("preview").src = result.url + "?tr=w-600,q-80";
                        });
                    })
                    .catch(err => {
                        //console.error("Erreur fetch auth:", err);
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
    </script>
</body>
</html>