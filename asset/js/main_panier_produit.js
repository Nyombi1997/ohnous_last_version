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

/* éditer icone ajouter au panier */
function editIconAjouterPanier(produitId = null, ajouter = true, retire = false, imgSrc = "", produitNom = "", produitSlug = "", produitTaille = "", produitPrix = "", produitStyle = "", produitBackground = "") {
    if(produitId!=null)
    {
        /* retirer du panier */
        if(retire)
        {
            $.post(
                "fonctions/panier.php",
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
            "fonctions/panier.php",
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
    /* ajouter au panier */
    let detail = `
                <!-- images -->
                <div class="div_img_detail_panier" style="background: ${produitBackground}">
                    <img
                        class="blur-up"
                        src="${imgSrc}?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
                        srcset="
                            ${imgSrc}?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
                            ${imgSrc}?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
                            ${imgSrc}?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
                        sizes="(max-width:768px) 90vw, 600px"
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
    /* ajouter au panier */
    editIconAjouterPanier(produitId = produitId, ajouter = true, retire = false, imgSrc = imgSrc, produitNom = produitNom, produitSlug = produitSlug, produitTaille = produitTaille, produitPrix = produitPrix, produitStyle = produitStyle, produitBackground = produitBackground);
}