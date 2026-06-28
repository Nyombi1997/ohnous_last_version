/* afficher panier */
let afficher_panier = document.querySelectorAll("#afficher_panier"),
    sortie_panier = document.querySelectorAll("#sortie_panier"),
    div_slide_panier = document.getElementById("div_slide_panier"),
    nombre_total_panier = document.getElementById("nombre_total_panier");

afficher_panier.forEach(function(element){
    element.addEventListener("click", function(e){
        e.preventDefault();
        div_slide_panier.classList.add("active");
    });
});

sortie_panier.forEach(function(element){
    element.addEventListener("click", function(){
        div_slide_panier.classList.remove("active");
    });
});

function onImageLoad() {
    document.querySelectorAll(".blur-up").forEach(function(img) {
        img.onload = function() {
            img.classList.add("blur-up-loaded");
        };

        if (img.complete) {
            img.classList.add("blur-up-loaded");
        }
    });
}
onImageLoad();

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

function escapeHtml(value) {
    return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function buildArticleUrl(produitSlug) {
    return "/article/" + encodeURIComponent(produitSlug || "");
}

function formatCartPrice(value) {
    var amount = parseFloat(value || 0);
    if (isNaN(amount)) {
        amount = 0;
    }
    return amount.toFixed(2);
}

function getCartKey(produitId, produitTaille) {
    return String(produitId) + "_" + String(produitTaille || "");
}

function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === "function") {
        return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
}

function getCartItemElement(cartKey) {
    return document.querySelector('.detail_panier[data-cart-key="' + cssEscape(cartKey) + '"]');
}

function indiceNombreArticlePanier() {
    if (!nombre_total_panier) {
        return;
    }

    nombre_total_panier.innerText = document.querySelectorAll(".detail_panier[data-cart-key]").length;
}

function calculPrixTotalPanier() {
    let prix_panier_exist = document.querySelectorAll(".prix-panier");
    let prix_total_panier = document.getElementById("prix_total_panier");
    let prix_total = 0;

    prix_panier_exist.forEach(function(element){
        prix_total += parseFloat(element.innerText || 0);
    });

    if (prix_total_panier) {
        prix_total_panier.innerText = prix_total.toFixed(2);
    }
}

function updateEmptyCartState() {
    let corps_detail_panier = document.getElementById("corps_detail_panier");

    if (!corps_detail_panier) {
        return;
    }

    if (document.querySelectorAll(".detail_panier[data-cart-key]").length === 0) {
        corps_detail_panier.innerHTML = '<h2 class="titre_panier">Votre panier est vide</h2>';
    }
}

function removeCartItemElement(cartKey) {
    let cartItem = getCartItemElement(cartKey);

    if (cartItem && cartItem.parentElement) {
        cartItem.parentElement.removeChild(cartItem);
    }

    indiceNombreArticlePanier();
    calculPrixTotalPanier();
    updateEmptyCartState();
}

function setCartButtonState(produitId, isActive) {
    document.querySelectorAll("#btn_panier_" + produitId).forEach(function(element){
        if (element.dataset.hasMultipleSizes === "1") {
            element.innerHTML = '<span class="icon-panier_plus"></span>';
            element.classList.remove("active");
            return;
        }

        element.innerHTML = isActive ? '<span class="icon-panier_moins"></span>' : '<span class="icon-panier_plus"></span>';
        element.classList.toggle("active", isActive);
    });
}

function editIconAjouterPanier(produitId = null, ajouter = true, retire = false, imgSrc = "", produitNom = "", produitSlug = "", produitTaille = "", produitPrix = "", produitStyle = "", produitBackground = "") {
    if (produitId == null) {
        return;
    }

    if (retire) {
        $.post("/fonctions/panier.php", {
            id: produitId,
            price: produitPrix,
            name: produitNom,
            size: produitTaille,
            image: imgSrc,
            retire: "ok",
            style: produitStyle,
            background: produitBackground,
            slug: produitSlug
        });
        setCartButtonState(produitId, false);
        return;
    }

    $.post("/fonctions/panier.php", {
        id: produitId,
        price: produitPrix,
        name: produitNom,
        size: produitTaille,
        image: imgSrc,
        ajout: "ok",
        style: produitStyle,
        background: produitBackground,
        slug: produitSlug
    });
    setCartButtonState(produitId, true);
}

function normalizeProductSizes(produitTailles, produitTaille) {
    if (Array.isArray(produitTailles)) {
        return produitTailles
            .map(function(item){
                return String(typeof item === "object" ? (item.nom || item.name || item.label || "") : item).trim();
            })
            .filter(function(item, index, list){
                return item !== "" && list.indexOf(item) === index;
            });
    }

    return String(produitTaille || "")
        .split(",")
        .map(function(item){
            return item.trim();
        })
        .filter(function(item, index, list){
            return item !== "" && list.indexOf(item) === index;
        });
}

function buildSizeChooserHtml(produitId, tailles) {
    let html = '<div class="cart-size-popup"><p>Choisissez la taille à ajouter au panier.</p><div class="cart-size-popup__grid">';
    let firstAvailableIndex = tailles.findIndex(function(taille){
        return !getCartItemElement(getCartKey(produitId, taille));
    });

    tailles.forEach(function(taille, index){
        let cartKey = getCartKey(produitId, taille);
        let alreadyInCart = !!getCartItemElement(cartKey);
        html += `
            <label class="cart-size-popup__choice ${alreadyInCart ? "is-in-cart" : ""} ${index === firstAvailableIndex ? "is-selected" : ""}">
                <input type="radio" name="cart_size_choice" value="${escapeHtml(taille)}" ${index === firstAvailableIndex ? "checked" : ""} ${alreadyInCart ? "disabled" : ""}>
                <span>${escapeHtml(taille)}</span>
                ${alreadyInCart ? '<small>Déjà au panier</small>' : ""}
            </label>`;
    });

    html += '</div></div>';
    return html;
}

function buildDirectSizeChooserHtml(tailles) {
    let html = '<div class="cart-size-popup"><p>Choisissez la taille à commander.</p><div class="cart-size-popup__grid">';

    tailles.forEach(function(taille, index){
        html += `
            <label class="cart-size-popup__choice ${index === 0 ? "is-selected" : ""}">
                <input type="radio" name="cart_size_choice" value="${escapeHtml(taille)}" ${index === 0 ? "checked" : ""}>
                <span>${escapeHtml(taille)}</span>
            </label>`;
    });

    html += '</div></div>';
    return html;
}

function ajouterTailleAuPanier(imgSrc, produitId, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground) {
    let corps_detail_panier = document.getElementById("corps_detail_panier");
    let cartKey = getCartKey(produitId, produitTaille);

    if (getCartItemElement(cartKey)) {
        Swal.fire({
            title: "Taille déjà au panier",
            text: "Choisissez une autre taille pour ce même article.",
            icon: "info",
            confirmButtonColor: "#6775d6",
            timer: 1600
        });
        return;
    }

    Swal.fire({
        title: "Produit ajouté au panier !",
        text: "Vous pouvez consulter votre panier pour finaliser votre achat.",
        icon: "success",
        confirmButtonColor: "#6775d6",
        timer: 1500
    });

    if (corps_detail_panier) {
        if (document.querySelectorAll(".detail_panier[data-cart-key]").length === 0) {
            corps_detail_panier.innerHTML = "";
        }
        corps_detail_panier.insertAdjacentHTML("beforeend", buildCartItemMarkup(imgSrc, produitId, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground, cartKey));
    }

    indiceNombreArticlePanier();
    calculPrixTotalPanier();
    onImageLoad();
    if (typeof window.initLiquidImages === "function") {
        window.initLiquidImages();
    }
    editIconAjouterPanier(produitId, true, false, imgSrc, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
}

function buildCartItemMarkup(imgSrc, produitId, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground, cartKey) {
    let liquidImage = buildLiquidImagePayload(imgSrc, "(max-width: 768px) 35vw, 180px");

    return `
        <div class="detail_panier" data-cart-key="${escapeHtml(cartKey)}">
            <div class="div_img_detail_panier" style="background: ${escapeHtml(produitBackground)}">
                <img
                    class="blur-up js-liquid-image"
                    src="${escapeHtml(liquidImage.placeholder)}"
                    data-image-base="${escapeHtml(imgSrc)}"
                    data-image-fallback="${escapeHtml(liquidImage.fallback)}"
                    data-image-high="${escapeHtml(liquidImage.high)}"
                    data-image-srcset="${escapeHtml(liquidImage.srcset)}"
                    data-image-sizes="${escapeHtml(liquidImage.sizes)}"
                    loading="lazy"
                    style="${escapeHtml(produitStyle)}"
                    alt="${escapeHtml(produitSlug)}"
                />
                <button
                    type="button"
                    class="div_supp_produit_panier js-remove-cart-item"
                    data-cart-key="${escapeHtml(cartKey)}"
                    data-product-id="${escapeHtml(produitId)}"
                    data-product-size="${escapeHtml(produitTaille)}"
                >
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <div class="infos_detail_panier">
                <a href="${buildArticleUrl(produitSlug)}" class="titre_produit_detail_panier_link">${escapeHtml(produitNom)}</a>
                <p class="prix_produit_detail_panier">$ <span class="prix-panier">${formatCartPrice(produitPrix)}</span></p>
                <p class="taille_produit_detail_panier">${escapeHtml(produitTaille || "Taille non pr\u00e9cis\u00e9e")}</p>
                <p class="taille_produit_detail_panier">Quantit\u00e9 : 1</p>
            </div>
        </div>`;
}

function ajouterAuPanier(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null, produitTailles = null) {
    let corps_detail_panier = document.getElementById("corps_detail_panier");
    let cartKey = getCartKey(produitId, produitTaille);
    let tailles = normalizeProductSizes(produitTailles, produitTaille);
    let shouldRemove = !!getCartItemElement(cartKey);

    if (tailles.length > 1) {
        Swal.fire({
            title: "Choisir une taille",
            html: buildSizeChooserHtml(produitId, tailles),
            showCancelButton: true,
            confirmButtonText: "Ajouter",
            cancelButtonText: "Annuler",
            confirmButtonColor: "#6775d6",
            cancelButtonColor: "#1f2640",
            customClass: {
                popup: "cart-size-popup-shell"
            },
            didOpen: function(){
                document.querySelectorAll('input[name="cart_size_choice"]').forEach(function(input){
                    input.addEventListener("change", function(){
                        document.querySelectorAll(".cart-size-popup__choice").forEach(function(label){
                            label.classList.remove("is-selected");
                        });
                        if (input.checked && input.closest(".cart-size-popup__choice")) {
                            input.closest(".cart-size-popup__choice").classList.add("is-selected");
                        }
                    });
                });
            },
            preConfirm: function(){
                let checked = document.querySelector('input[name="cart_size_choice"]:checked');
                if (!checked) {
                    Swal.showValidationMessage("Choisissez une taille disponible.");
                    return false;
                }
                return checked.value;
            }
        }).then(function(result){
            if (result.isConfirmed) {
                ajouterTailleAuPanier(imgSrc, produitId, produitNom, produitSlug, result.value, produitPrix, produitStyle, produitBackground);
            }
        });
        return;
    }

    document.querySelectorAll("#btn_panier_" + produitId).forEach(function(element){
        if (element.classList.contains("active")) {
            shouldRemove = true;
        }
    });

    if (shouldRemove) {
        Swal.fire({
            title: "Produit retir\u00e9 du panier !",
            text: "Cet article a \u00e9t\u00e9 retir\u00e9 de votre panier.",
            icon: "success",
            confirmButtonColor: "#6775d6",
            timer: 1500
        });
        removeCartItemElement(cartKey);
        editIconAjouterPanier(produitId, false, true, imgSrc, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
        return;
    }

    Swal.fire({
        title: "Produit ajout\u00e9 au panier !",
        text: "Vous pouvez consulter votre panier pour finaliser votre achat.",
        icon: "success",
        confirmButtonColor: "#6775d6",
        timer: 1500
    });

    if (corps_detail_panier) {
        if (document.querySelectorAll(".detail_panier[data-cart-key]").length === 0) {
            corps_detail_panier.innerHTML = "";
        }
        corps_detail_panier.insertAdjacentHTML("beforeend", buildCartItemMarkup(imgSrc, produitId, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground, cartKey));
    }

    indiceNombreArticlePanier();
    calculPrixTotalPanier();
    onImageLoad();
    if (typeof window.initLiquidImages === "function") {
        window.initLiquidImages();
    }
    editIconAjouterPanier(produitId, true, false, imgSrc, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
}

function retirerDuPanierDepuisVue(produitId = null, produitTaille = "", cartKey = null) {
    let resolvedCartKey = cartKey || getCartKey(produitId, produitTaille);

    $.post("/fonctions/panier.php", {
        id: produitId,
        size: produitTaille,
        retire: "ok"
    }, function(data){
        if (!data || data.result !== "ok") {
            Swal.fire({
                icon: "error",
                title: data && data.msg ? data.msg : "Impossible de retirer cet article.",
                confirmButtonColor: "#6775d6"
            });
            return;
        }

        removeCartItemElement(resolvedCartKey);
        setCartButtonState(produitId, false);

        Swal.fire({
            title: "Produit retir\u00e9 du panier !",
            text: "Cet article a \u00e9t\u00e9 retir\u00e9 de votre panier.",
            icon: "success",
            confirmButtonColor: "#6775d6",
            timer: 1500
        });
    }, "json").fail(function(){
        Swal.fire({
            icon: "error",
            title: "Impossible de retirer cet article.",
            confirmButtonColor: "#6775d6"
        });
    });
}

$(document).on("click", ".js-remove-cart-item", function(){
    retirerDuPanierDepuisVue($(this).data("product-id"), $(this).data("product-size"), $(this).data("cart-key"));
});

function envoyerCommandeDirecte(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null) {
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
        if (data.result !== "ok") {
            Swal.fire({
                icon: "error",
                title: data.msg || "Impossible de pr\u00e9parer cette commande directe.",
                confirmButtonColor: "#6775d6"
            });
            return;
        }

        window.location.href = data.redirect || "/checkout?mode=direct";
    }, "json").fail(function(){
        Swal.fire({
            icon: "error",
            title: "Impossible de pr\u00e9parer cette commande directe.",
            confirmButtonColor: "#6775d6"
        });
    });
}

function commanderDirectement(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null, produitTailles = null) {
    let tailles = normalizeProductSizes(produitTailles, produitTaille);

    if (tailles.length > 1) {
        Swal.fire({
            title: "Choisir une taille",
            html: buildDirectSizeChooserHtml(tailles),
            showCancelButton: true,
            confirmButtonText: "Commander",
            cancelButtonText: "Annuler",
            confirmButtonColor: "#6775d6",
            cancelButtonColor: "#1f2640",
            customClass: {
                popup: "cart-size-popup-shell"
            },
            didOpen: function(){
                document.querySelectorAll('input[name="cart_size_choice"]').forEach(function(input){
                    input.addEventListener("change", function(){
                        document.querySelectorAll(".cart-size-popup__choice").forEach(function(label){
                            label.classList.remove("is-selected");
                        });
                        if (input.checked && input.closest(".cart-size-popup__choice")) {
                            input.closest(".cart-size-popup__choice").classList.add("is-selected");
                        }
                    });
                });
            },
            preConfirm: function(){
                let checked = document.querySelector('input[name="cart_size_choice"]:checked');
                if (!checked) {
                    Swal.showValidationMessage("Choisissez une taille.");
                    return false;
                }
                return checked.value;
            }
        }).then(function(result){
            if (result.isConfirmed) {
                envoyerCommandeDirecte(imgSrc, produitId, produitNom, produitSlug, result.value, produitPrix, produitStyle, produitBackground);
            }
        });
        return;
    }

    envoyerCommandeDirecte(imgSrc, produitId, produitNom, produitSlug, produitTaille, produitPrix, produitStyle, produitBackground);
}

/* recherche catalogue */
let donnee_de_recherche = document.querySelectorAll("#donnee_de_recherche");
let input_search_bar_2 = document.getElementById("input_search_bar_2");

function fermerRechercheSuggestions() {
    donnee_de_recherche.forEach(function (element){
        element.innerHTML = "";
        element.classList.add("null");
    });
}

function ouvrirRechercheSuggestions(html) {
    donnee_de_recherche.forEach(function (element){
        element.innerHTML = html;
        element.classList.remove("null");
    });

    if (typeof window.initLiquidImages === "function") {
        window.initLiquidImages();
    }
}

function handleSearchSuggestionClick(event, source, value, slug) {
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

function handleShopSearchSubmit(event) {
    if (window.location.pathname === "/shop" && typeof window.ohnousShopSubmitSearch === "function") {
        event.preventDefault();
        window.ohnousShopSubmitSearch((input_search_bar_2 && input_search_bar_2.value) ? input_search_bar_2.value : "");
        fermerRechercheSuggestions();
        return false;
    }

    return true;
}

function buildSearchSuggestionHtml(item) {
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
        source = "Cat\u00e9gorie";
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

function rechercheArticles(value) {
    value = (value || "").trim();

    if (value === "") {
        fermerRechercheSuggestions();
        return;
    }

    $.post("/fonctions/recherche.php", { q: value }, function(data){
        let resultList = Array.isArray(data) ? data : (Array.isArray(data.results) ? data.results : []);
        let html = "";

        if (data.suggestion !== undefined) {
            html += `<div class="suggestion">Vous recherchez <a href="/shop?query=${encodeURIComponent(data.suggestion)}" onclick="return handleSearchSuggestionClick(event, 'search', ${JSON.stringify(data.suggestion)}, '')">${data.suggestion}</a> ?</div>`;
        }

        if (data.noResult !== undefined) {
            ouvrirRechercheSuggestions('<div class="no_result">Aucun article disponible.</div>');
            return;
        }

        let labels = [];
        resultList.forEach(function(item){
            if (labels.indexOf(item.source + ":" + item.label) !== -1) {
                return;
            }

            labels.push(item.source + ":" + item.label);
            html += buildSearchSuggestionHtml(item);
        });

        ouvrirRechercheSuggestions(html !== "" ? html : '<div class="no_result">Aucun article disponible.</div>');
    }, "json");
}

$(window).on("scroll", function () {
    fermerRechercheSuggestions();
});

document.addEventListener("click", function(e) {
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
