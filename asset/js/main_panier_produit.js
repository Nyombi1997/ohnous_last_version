/* afficher panier */
let
afficher_panier = document.querySelectorAll("#afficher_panier"),
sortie_panier = document.querySelectorAll("#sortie_panier"),
div_slide_panier = document.getElementById("div_slide_panier");

afficher_panier.forEach(function(element){
    element.addEventListener("click",function(e){
        e.preventDefault();
        div_slide_panier.classList.add("active");
    })
})
sortie_panier.forEach(function(element){
    element.addEventListener("click",function(e){
        div_slide_panier.classList.remove("active");
    })
})

/* afficher les images après le floutage */
function onImageLoad() {
    document.querySelectorAll(".blur-up").forEach(function(img) {
        img.onload = () => {
            img.classList.add('blur-up-loaded');
        }
    });
}
onImageLoad();

/* préparer les URLs ImageKit côté JS pour le HTML injecté dynamiquement */
function buildImageKitUrl(url, transformations) {
    if (!url) {
        return "";
    }

    if (!transformations || transformations.length === 0) {
        return url;
    }

    var separator = url.indexOf("?") === -1 ? "?" : "&";
    return url + separator + "tr=" + transformations.join(",");
}

function buildLiquidImagePayload(url, sizes) {
    return {
        placeholder: buildImageKitUrl(url, ["w-80", "q-20"]),
        fallback: buildImageKitUrl(url, ["w-400", "q-45"]),
        high: buildImageKitUrl(url, ["w-800", "q-82"]),
        srcset: [
            buildImageKitUrl(url, ["w-400", "q-82"]) + " 400w",
            buildImageKitUrl(url, ["w-800", "q-82"]) + " 800w",
            buildImageKitUrl(url, ["w-1200", "q-85"]) + " 1200w"
        ].join(", "),
        sizes: sizes || "(max-width: 768px) 35vw, 180px"
    };
}

/* éditer icone ajouter au panier */
function editIconAjouterPanier(produitId = null, ajouter = true, retire = false, imgSrc = "", produitNom = "", produitSlug = "", produitTaille = "", produitPrix = "", produitStyle = "", produitBackground = "") {
    if(produitId!=null)
    {
        /* retirer du panier */
        if(retire)
        {
            $.post(
                "/fonctions/panier.php",
                {
                    id : produitId,
                    price : produitPrix,
                    name : produitNom,
                    size : produitTaille,
                    image : imgSrc,
                    retire : "ok",
                    style : produitStyle,
                    background : produitBackground,
                    slug : produitSlug,
                },
                function(data){
                }
            ); 
            document.querySelectorAll("#btn_panier_"+produitId).forEach(function(element){
                element.innerHTML = `<span class="icon-panier_plus"></span>`;
                element.classList.remove("active");
            });
            return;
        }
        /* ajouter au panier */
        $.post(
            "/fonctions/panier.php",
            {
                id : produitId,
                price : produitPrix,
                name : produitNom,
                size : produitTaille,
                image : imgSrc,
                ajout : "ok",
                style : produitStyle,
                background : produitBackground,
                slug : produitSlug,
            },
            function(data){
            }
        ); 
        document.querySelectorAll("#btn_panier_"+produitId).forEach(function(element){
            element.innerHTML = `<span class="icon-panier_moins"></span>`;
            element.classList.add("active");
        })
    }
}

/* indice nombre article panier */
function indiceNombreArticlePanier()
{
    let prix_panier_exist = document.querySelectorAll(".prix-panier");
    nombre_total_panier.innerText = prix_panier_exist.length;
}

/* calcul prix total panier */
function calculPrixTotalPanier()
{
    let prix_panier_exist = document.querySelectorAll(".prix-panier");
    let prix_total_panier = document.getElementById("prix_total_panier");
    let prix_total = 0;
    prix_panier_exist.forEach(function(element){
        prix_total += parseFloat(element.innerText);
    });
    prix_total_panier.innerText = prix_total.toFixed(2);
}

function getCartKey(produitId, produitTaille)
{
    return String(produitId) + "_" + String(produitTaille || "");
}

function cssEscape(value)
{
    if(window.CSS && typeof window.CSS.escape === "function")
    {
        return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
}

function getCartItemElement(cartKey)
{
    return document.querySelector('.detail_panier[data-cart-key="' + cssEscape(cartKey) + '"]');
}

function updateEmptyCartState()
{
    let prix_panier_exist = document.querySelector(".prix-panier");
    let corps_detail_panier = document.getElementById("corps_detail_panier");

    if(prix_panier_exist == null && corps_detail_panier)
    {
        corps_detail_panier.innerHTML = '<h2 class="titre_panier">Votre panier est vide</h2>';
    }
}

function removeCartItemElement(cartKey)
{
    let cartItem = getCartItemElement(cartKey);

    if(cartItem && cartItem.parentElement)
    {
        cartItem.parentElement.removeChild(cartItem);
    }

    indiceNombreArticlePanier();
    calculPrixTotalPanier();
    updateEmptyCartState();
    onImageLoad();
}

/* ajouter au panier */
function ajouterAuPanier(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null) {
    /* retrouver le bouton d'ajoout au panier */
    let prix_panier_exist = document.querySelector(".prix-panier");
    let retirer_du_panier = false;
    let corps_detail_panier = document.getElementById("corps_detail_panier");
    let cartKey = getCartKey(produitId, produitTaille);
    document.querySelectorAll("#btn_panier_"+produitId).forEach(function(element){
        if(element.classList.contains("active"))
        {
            Swal.fire({
                title: "Produit retiré du panier !",
                text: "Cet article a été retiré de votre panier.",
                icon: "success",
                confirmButtonColor: '#6775d6',
                timer: 1500
            });
            document.getElementById("detail_panier_"+produitId).parentElement.removeChild(document.getElementById("detail_panier_"+produitId));
            /* mettre à jour l'indice du nombre d'articles au panier */
            indiceNombreArticlePanier();
            /* calcul prix total panier */
            calculPrixTotalPanier();
            /* afficher les images après le floutage */
            onImageLoad();
            /* ajouter au panier */
            editIconAjouterPanier(produitId = produitId, ajouter = false, retire = true, imgSrc = imgSrc, produitNom = produitNom, produitSlug = produitSlug, produitTaille = produitTaille, produitPrix = produitPrix, produitStyle = produitStyle, produitBackground = produitBackground);
            retirer_du_panier = true;
            /* vérifier s'il y'a des elements dans le panier */
            prix_panier_exist = document.querySelector(".prix-panier");
            if(prix_panier_exist == null)
            {
                corps_detail_panier.innerHTML = '<h2 class="titre_panier">Votre panier est vide</h2>';
            }
        }
    })
    if(retirer_du_panier)
    {
        return;
    }
    /* ajouter au panier */
    Swal.fire({
        title: "Produit ajouté au panier !",
        text: "Vous pouvez consulter votre panier pour finaliser votre achat.",
        icon: "success",
        confirmButtonColor: '#6775d6',
        timer: 1500
    });
    let liquidImage = buildLiquidImagePayload(imgSrc, "(max-width: 768px) 35vw, 180px");
    /* ajouter au panier */
    let detail = `
                <!-- images -->
                <div class="div_img_detail_panier" style="background: ${produitBackground}">
                    <img
                        class="blur-up js-liquid-image"
                        src="${liquidImage.placeholder}"
                        data-image-base="${imgSrc}"
                        data-image-fallback="${liquidImage.fallback}"
                        data-image-high="${liquidImage.high}"
                        data-image-srcset="${liquidImage.srcset}"
                        data-image-sizes="${liquidImage.sizes}"
                        loading="lazy"
                        style="${produitStyle}"
                        alt="${produitSlug}"
                    />
                    <div class="div_supp_produit_panier" onclick="ajouterAuPanier('${imgSrc}','${produitId}','${produitNom}','${produitSlug}','${produitTaille}','${produitPrix}','${produitStyle}','${produitBackground}')">
                        <i class="fa fa-trash"></i>
                    </div>
                </div>
                <!-- details -->
                <div class="infos_detail_panier">
                    <p class="titre_produit_detail_panier">${produitNom}</p>
                    <p class="prix_produit_detail_panier">$ <span class="prix-panier">${produitPrix}</span></p>
                    <p class="taille_produit_detail_panier">${produitTaille}</p>
                </div>`;
    let corps_article = document.createElement("div");
    corps_article.classList.add("detail_panier");
    corps_article.id = "detail_panier_"+produitId;
    corps_article.innerHTML = detail;
    /* vérifier s'il y'a des elements dans le panier */
    if(prix_panier_exist)
    {
        corps_detail_panier.appendChild(corps_article);
    }
    else
    {
        corps_detail_panier.innerHTML = "";
        corps_detail_panier.appendChild(corps_article);
    }
    /* mettre à jour l'indice du nombre d'articles au panier */
    indiceNombreArticlePanier();
    /* calcul prix total panier */
    calculPrixTotalPanier();
    /* afficher les images après le floutage */
    onImageLoad();
    if (typeof window.initLiquidImages === "function") {
        window.initLiquidImages();
    }
    /* ajouter au panier */
    editIconAjouterPanier(produitId = produitId, ajouter = true, retire = false, imgSrc = imgSrc, produitNom = produitNom, produitSlug = produitSlug, produitTaille = produitTaille, produitPrix = produitPrix, produitStyle = produitStyle, produitBackground = produitBackground);
}
/* recherche catalogue */
function ajouterAuPanier(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null) {
    let prix_panier_exist = document.querySelector(".prix-panier");
    let retirer_du_panier = false;
    let corps_detail_panier = document.getElementById("corps_detail_panier");
    let cartKey = getCartKey(produitId, produitTaille);

    document.querySelectorAll("#btn_panier_"+produitId).forEach(function(element){
        if(element.classList.contains("active"))
        {
            Swal.fire({
                title: "Produit retiré du panier !",
                text: "Cet article a été retiré de votre panier.",
                icon: "success",
                confirmButtonColor: '#6775d6',
                timer: 1500
            });
            removeCartItemElement(cartKey);
            editIconAjouterPanier(produitId, false, true, imgSrc, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
            retirer_du_panier = true;
        }
    });

    if(retirer_du_panier)
    {
        return;
    }

    Swal.fire({
        title: "Produit ajouté au panier !",
        text: "Vous pouvez consulter votre panier pour finaliser votre achat.",
        icon: "success",
        confirmButtonColor: '#6775d6',
        timer: 1500
    });

    let liquidImage = buildLiquidImagePayload(imgSrc, "(max-width: 768px) 35vw, 180px");
    let detail = `
                <!-- images -->
                <div class="div_img_detail_panier" style="background: ${produitBackground}">
                    <img
                        class="blur-up js-liquid-image"
                        src="${liquidImage.placeholder}"
                        data-image-base="${imgSrc}"
                        data-image-fallback="${liquidImage.fallback}"
                        data-image-high="${liquidImage.high}"
                        data-image-srcset="${liquidImage.srcset}"
                        data-image-sizes="${liquidImage.sizes}"
                        loading="lazy"
                        style="${produitStyle}"
                        alt="${produitSlug}"
                    />
                    <div class="div_supp_produit_panier" onclick="retirerDuPanierDepuisVue(${JSON.stringify(imgSrc)},${JSON.stringify(produitId)},${JSON.stringify(produitNom)},${JSON.stringify(produitSlug)},${JSON.stringify(produitTaille)},${JSON.stringify(produitPrix)},${JSON.stringify(produitStyle)},${JSON.stringify(produitBackground)},${JSON.stringify(cartKey)})">
                        <i class="fa fa-trash"></i>
                    </div>
                </div>
                <!-- details -->
                <div class="infos_detail_panier">
                    <p class="titre_produit_detail_panier">${produitNom}</p>
                    <p class="prix_produit_detail_panier">$ <span class="prix-panier">${produitPrix}</span></p>
                    <p class="taille_produit_detail_panier">${produitTaille}</p>
                </div>`;
    let corps_article = document.createElement("div");
    corps_article.classList.add("detail_panier");
    corps_article.setAttribute("data-cart-key", cartKey);
    corps_article.innerHTML = detail;

    if(prix_panier_exist)
    {
        corps_detail_panier.appendChild(corps_article);
    }
    else
    {
        corps_detail_panier.innerHTML = "";
        corps_detail_panier.appendChild(corps_article);
    }

    indiceNombreArticlePanier();
    calculPrixTotalPanier();
    onImageLoad();
    if (typeof window.initLiquidImages === "function") {
        window.initLiquidImages();
    }
    editIconAjouterPanier(produitId, true, false, imgSrc, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
}

function retirerDuPanierDepuisVue(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null, cartKey = null)
{
    let resolvedCartKey = cartKey || getCartKey(produitId, produitTaille);

    Swal.fire({
        title: "Produit retiré du panier !",
        text: "Cet article a été retiré de votre panier.",
        icon: "success",
        confirmButtonColor: '#6775d6',
        timer: 1500
    });

    removeCartItemElement(resolvedCartKey);
    editIconAjouterPanier(produitId, false, true, imgSrc, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
}

function commanderDirectement(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null)
{
    $.post("/fonctions/checkout_actions.php", {
        action: "prepare_direct_checkout",
        id: produitId,
        name: produitNom,
        price: produitPrix,
        size: produitTaille,
        image: imgSrc,
        style: produitStyle,
        background: produitBackground,
        slug: produitSlug
    }, function(data){
        if(data.result !== "ok")
        {
            Swal.fire({
                icon: "error",
                title: data.msg || "Impossible de préparer cette commande directe.",
                confirmButtonColor: '#6775d6'
            });
            return;
        }

        window.location.href = data.redirect || "/checkout?mode=direct";
    }, "json").fail(function(){
        Swal.fire({
            icon: "error",
            title: "Impossible de préparer cette commande directe.",
            confirmButtonColor: '#6775d6'
        });
    });
}

/* recherche catalogue */
let donnee_de_recherche = document.querySelectorAll("#donnee_de_recherche");
let input_search_bar_2 = document.getElementById("input_search_bar_2");

function fermerRechercheSuggestions()
{
    donnee_de_recherche.forEach(function (element){
        element.innerHTML = "";
        element.classList.add("null");
    });
}

function ouvrirRechercheSuggestions(html)
{
    donnee_de_recherche.forEach(function (element){
        element.innerHTML = html;
        element.classList.remove("null");
    });

    if (typeof window.initLiquidImages === "function") {
        window.initLiquidImages();
    }
}

function handleSearchSuggestionClick(event, source, value, slug)
{
    if (typeof window.ohnousShopApplySearchResult !== "function" || window.location.pathname !== "/shop") {
        return true;
    }

    if (source === "categorie") {
        event.preventDefault();
        window.ohnousShopApplySearchResult("categorie", value, slug);
        fermerRechercheSuggestions();
        return false;
    }

    if (source === "types") {
        event.preventDefault();
        window.ohnousShopApplySearchResult("type", value, slug);
        fermerRechercheSuggestions();
        return false;
    }

    if (source === "tailles") {
        event.preventDefault();
        window.ohnousShopApplySearchResult("taille", value, slug);
        fermerRechercheSuggestions();
        return false;
    }

    if (source === "search") {
        event.preventDefault();
        window.ohnousShopApplySearchResult("search", value, "");
        fermerRechercheSuggestions();
        return false;
    }

    return true;
}

function handleShopSearchSubmit(event)
{
    if (window.location.pathname === "/shop" && typeof window.ohnousShopSubmitSearch === "function") {
        event.preventDefault();
        window.ohnousShopSubmitSearch((input_search_bar_2 && input_search_bar_2.value) ? input_search_bar_2.value : "");
        fermerRechercheSuggestions();
        return false;
    }

    return true;
}

function buildSearchSuggestionHtml(item)
{
    if (item.source === "articles") {
        let thumb = "";
        if (item.image) {
            let liquid = buildLiquidImagePayload(item.image, "62px");
            thumb = `
                <span class="search-result-thumb" style="background:${item.background || "rgba(255,255,255,.35)"};">
                    <img
                        class="blur-up js-liquid-image"
                        src="${liquid.placeholder}"
                        data-image-base="${item.image}"
                        data-image-fallback="${liquid.fallback}"
                        data-image-high="${liquid.high}"
                        data-image-srcset="${liquid.srcset}"
                        data-image-sizes="${liquid.sizes}"
                        loading="lazy"
                        style="${item.style || ""}"
                        alt="${item.label}"
                    />
                </span>`;
        }

        return `
            <a href="${item.url}" class="link search-result-card">
                ${thumb}
                <span class="search-result-meta">
                    <strong>${item.label}</strong>
                    <span>Article</span>
                </span>
                <span class="search-result-price">${item.price_label || ""}</span>
            </a>`;
    }

    let icon = '<i class="fa-solid fa-magnifying-glass"></i>';
    let source = "Recherche";
    let clickHandler = "";

    if (item.source === "boutiques") {
        icon = '<i class="fa-solid fa-store"></i>';
        source = "Boutique";
    } else if (item.source === "categorie") {
        icon = '<i class="fa-solid fa-layer-group"></i>';
        source = "Catégorie";
        clickHandler = ` onclick="return handleSearchSuggestionClick(event, 'categorie', ${JSON.stringify(item.label)}, ${JSON.stringify(item.slug)})"`;
    } else if (item.source === "types") {
        icon = '<i class="fa-solid fa-list"></i>';
        source = "Type";
        clickHandler = ` onclick="return handleSearchSuggestionClick(event, 'types', ${JSON.stringify(item.label)}, ${JSON.stringify(item.slug)})"`;
    } else if (item.source === "tailles") {
        icon = '<i class="fa-solid fa-ruler"></i>';
        source = "Taille";
        clickHandler = ` onclick="return handleSearchSuggestionClick(event, 'tailles', ${JSON.stringify(item.label)}, ${JSON.stringify(item.slug)})"`;
    }

    return `<a href="${item.url}" class="link"${clickHandler}>${icon} ${item.label} <span>${source}</span></a>`;
}

function rechercheArticles(value)
{
    value = (value || "").trim();

    if(value === ""){
        fermerRechercheSuggestions();
        return;
    }

    $.post("/fonctions/recherche.php",{q : value },function(data){
        let resultList = Array.isArray(data) ? data : (Array.isArray(data.results) ? data.results : []);
        let html = "";

        if(data.suggestion !== undefined)
        {
            html += `<div class="suggestion">Vous recherchez <a href="/shop?query=${encodeURIComponent(data.suggestion)}" onclick="return handleSearchSuggestionClick(event, 'search', ${JSON.stringify(data.suggestion)}, '')">${data.suggestion}</a> ?</div>`;
        }

        if(data.noResult !== undefined)
        {
            ouvrirRechercheSuggestions(`<div class="no_result">Aucun article disponible.</div>`);
            return;
        }
        let labels = [];

        resultList.forEach(function(item){
            if(labels.indexOf(item.source + ":" + item.label) !== -1)
            {
                return;
            }

            labels.push(item.source + ":" + item.label);
            html += buildSearchSuggestionHtml(item);
        });

        ouvrirRechercheSuggestions(html !== "" ? html : `<div class="no_result">Aucun article disponible.</div>`);
    }, "json");
}

$(window).on("scroll", function () {
    fermerRechercheSuggestions();
});

document.addEventListener("click", (e) => {
    if (input_search_bar_2 && input_search_bar_2.contains(e.target)) {
        return;
    }

    donnee_de_recherche.forEach(function (element){
        if (!element.contains(e.target)) {
            element.innerHTML = "";
            element.classList.add("null");
        }
    });
});
