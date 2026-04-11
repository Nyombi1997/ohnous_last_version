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

/* ajouter au panier */
function ajouterAuPanier(imgSrc = null, produitId = null, produitNom = null, produitSlug = null, produitTaille = null, produitPrix = null, produitStyle = null, produitBackground = null) {
    /* retrouver le bouton d'ajoout au panier */
    let prix_panier_exist = document.querySelector(".prix-panier");
    let retirer_du_panier = false;
    let corps_detail_panier = document.getElementById("corps_detail_panier");
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



/* fonction pour nfaire la recherche d'articles */
let donnee_de_recherche = document.querySelectorAll("#donnee_de_recherche");
let input_search_bar_2 = document.getElementById("input_search_bar_2");
function rechercheArticles(value)
{
    $.post("/fonctions/recherche.php",{q : value },function(data){
        const result = data;
        /* si on a une suggestion */
        if(result.suggestion != undefined)
        {
            donnee_de_recherche.forEach(function (element){
                element.innerHTML = "";
                element.classList.remove("null");
                element.innerHTML += `<div class="suggestion">Vous recherchez <a href="/q?query=${result.suggestion}" onclick="suggestionRecherche(this)">${result.suggestion}</a> ?</div>`;
            })
        }
        else if(result.noResult == undefined)
        {
            donnee_de_recherche.forEach(function (element){
                element.innerHTML = "";
                element.classList.remove("null");
            })
            let tableau = [];
            result.forEach(function(item){
                if(tableau.includes(item.label))
                {
                    return;
                }
                else
                {
                    tableau.push(item.label);
                }
                /* si c'est une article */
                if(item.source == "articles")
                {
                    donnee_de_recherche.forEach(function (element){
                        /* si c'est un prix */
                        let prix_article_depuis_recherche = '';
                        let icone_article_depuis_recherche = '';
                        if (/(\d+)\s*(\$|dollars?|fcfa?|euros?|francs?|\w*)?/i.test(value))
                        {
                            prix_article_depuis_recherche = '| '+item.prix ? '- ' + item.prix + ' USD' : '';
                            icone_article_depuis_recherche = '<i class="fa-solid fa-magnifying-glass-dollar"></i>';
                        }
                        element.innerHTML += `<a href="/article/${item.slug}" class="link"> ${icone_article_depuis_recherche} ${item.label} ${prix_article_depuis_recherche}</a>`;
                    })
                }
                /* si c'est une boutique */
                else if(item.source == "boutiques")
                {
                    donnee_de_recherche.forEach(function (element){
                        element.innerHTML += `<a href="/boutique/${item.slug}" class="link"><i class="fa-solid fa-store"></i> ${item.label}</a>`;
                    })
                }
                /* si c'est une categorie */
                else if(item.source == "categorie")
                {
                    donnee_de_recherche.forEach(function (element){
                        element.innerHTML += `<a href="/categorie/${item.slug}" class="link" onclick="filtre_categorie('${item.id}', '${item.label}', '${item.slug}', event, 'ok')"><i class="fa-solid fa-layer-group"></i> ${item.label}</a>`;
                    })
                }
                /* si c'est un type */
                else if(item.source == "types")
                {
                    donnee_de_recherche.forEach(function (element){
                        element.innerHTML += `<a href="/type/${item.slug}" class="link" onclick="filtre_types('${item.id}', '${item.label}', '${item.slug}', event, 'ok')"><i class="fa-solid fa-list"></i> ${item.label}</a>`;
                    })
                }
                /* si c'est une taille */
                else if(item.source == "tailles")
                {
                    donnee_de_recherche.forEach(function (element){
                        element.innerHTML += `<a href="/taille/${item.slug}" class="link" onclick="filtre_tailles('${item.id}', '${item.label}', '${item.slug}', event, 'ok')"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> ${item.label}</a>`;
                    })
                }
            })
        }
        else
        {
            donnee_de_recherche.forEach(function (element){
                element.innerHTML = "";
                element.classList.remove("null");
                element.innerHTML += `<div class="no_result">Aucun resultat</div>`;
            })
        }
    })
}
/* lire le scroll */
$(window).on("scroll", function () {
    donnee_de_recherche.forEach(function (element){
        element.innerHTML = "";
        element.classList.add("null");
    })
});
// clic partout sur le document
document.addEventListener("click", (e) => {
    // si le clic est en dehors de la div
    donnee_de_recherche.forEach(function (element){
        if (!element.contains(e.target)) {
            element.innerHTML = "";
            element.classList.add("null");
        }
    })
});
