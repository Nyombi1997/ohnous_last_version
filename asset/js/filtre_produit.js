let categorie_en_cours = 0;
let types_en_cours = 0;
let taille_en_cours = 0;
let boutique_en_cours = 0;
let recherche_en_cours = "";
let page = 1;
let offset = 0;
/* gestion de scroll afficher article */
let loading = false;
/* checking du scroll */
function checkingScroll()
{
    let scrollBottom = $(window).scrollTop() + $(window).height();
    let docHeight = $(document).height();

    // on déclenche AVANT la fin (anticipation)
    if(loading == false)
    {
        if (scrollBottom > docHeight - 600) {
            page = 2;
            loading = true;
            gestionAffichageArticle();
        }
    }
}
checkingScroll();

$(window).on("scroll", function () {
    checkingScroll();
});
$(window).on("load", function () {
    checkingScroll();
});
/* gerer l'affichage des articles */
function gestionAffichageArticle()
{
    $.post("fonctions/filtre_article.php",{
        categorie : categorie_en_cours,
        types : types_en_cours,
        taille : taille_en_cours,
        boutique : boutique_en_cours,
        recherche : recherche_en_cours,
        page : page,
        offset : offset,
    },
    function(data){
        if(data.nombre != 0)
        {
            if(page == 1)
            {
                document.getElementById("afficher_article").innerHTML = data.msg;
                onImageLoad();
                loading = false;
            }
            else
            {
                document.getElementById("afficher_article").innerHTML += data.msg;
                onImageLoad();
                loading = false;
            }
            /* informer qu'il faut afficher d'autres articles */
            if(offset==0)
            {
                offset = 12;
            }
            else
            {
                offset *= page;
            }
        }
        else
        {
            document.getElementById("afficher_article").innerHTML += `<!-- HTML !-->
                                                                        <div class="div_btn_voir_plus">
                                                                            <a href="articles" class="btn_voir_plus" role="button">Decouvrez plus d'articles  <i class="fa-solid fa-arrow-right-long"></i></a>
                                                                        </div>`;
        }
    })
}
/* filtrer les catégories */
function filtre_categorie(id = "", nom = "", slug = "")
{
    let div_filtre_types = document.getElementById("div_filtre_types");
    let details_filtre_types = document.getElementById("details_filtre_types");
    let div_filtre_tailles = document.getElementById("div_filtre_tailles");
    let details_filtre_tailles = document.getElementById("details_filtre_tailles");
    let categoreiFiltre = document.querySelector(".js_detail_liste_filtre_produit_"+id);
    let changingWord = document.getElementById("changing-word");
    /* si c'est une anullation de filtre */
    if(categoreiFiltre.classList.contains("active"))
    {
        /* retirer l'indice sur le choix de la categories filtre et retirer le nom */
        categoreiFiltre.classList.remove("active");
        changingWord.innerText = "Articles";
        setUrl("articles");

        categorie_en_cours = 0;
        types_en_cours = 0;
        taille_en_cours = 0;
        recherche_en_cours = "";
        page = 1;
        offset = 0;
        /* vider les tailles */
        div_filtre_tailles.classList.add("null");
        details_filtre_tailles.innerHTML = "";
        /* vider les types */
        div_filtre_types.classList.add("null");
        details_filtre_tailles.innerHTML = "";
        /* ajuster le filtre */
        gestionAffichageArticle();
        return;
    }
    /* vider la recherche et filtre*/
    types_en_cours = 0;
    taille_en_cours = 0;
    recherche_en_cours = "";
    page = 1;
    offset = 0;
    /* retirer tout les indices de choix de filtre catégiorie */
    document.querySelectorAll(".js_detail_liste_filtre_produit").forEach(function (element){
        element.classList.remove("active");
    })
    $.post("fonctions/filtre_fetch_types.php", {id : id}, function(data){
        /* placer l'indice sur le choix de la categorie filtre et placer le nom */
        categoreiFiltre.classList.add("active");
        changingWord.innerText = nom;
        setUrl(slug);
        /* vider les tailles par defaut */
        div_filtre_tailles.classList.add("null");
        details_filtre_tailles.innerHTML = "";
        /* afficher les types */
        if(data.result == "ok")
        {
            div_filtre_types.classList.remove("null");
            details_filtre_types.innerHTML = data.msg;
        }
        else
        {
            div_filtre_types.classList.add("null");
            details_filtre_types.innerHTML = "";
        }
        /* ajuster le filtre */
        categorie_en_cours = id;
        gestionAffichageArticle();
    })
}
/* filtrer les types */
function filtre_types(id = "", nom = "", slug = "")
{
    let div_filtre_tailles = document.getElementById("div_filtre_tailles");
    let details_filtre_tailles = document.getElementById("details_filtre_tailles");
    let typesFiltre = document.querySelector(".js_detail_liste_filtre_produit_types"+id);
    /* si c'est une anullation de filtre */
    if(typesFiltre.classList.contains("active"))
    {
        /* retirer l'indice sur le choix des types filtre et placer le nom */
        typesFiltre.classList.remove("active");

        types_en_cours = 0;
        taille_en_cours = 0;
        recherche_en_cours = "";
        page = 1;
        offset = 0;
        /* vider les tailles */
        div_filtre_tailles.classList.add("null");
        details_filtre_tailles.innerHTML = "";
        /* ajuster le filtre */
        gestionAffichageArticle();
        return;
    }
    /* vider la recherche et filtre*/
    taille_en_cours = 0;
    recherche_en_cours = "";
    page = 1;
    offset = 0;
    /* retirer tout les indices de choix de filtre types */
    document.querySelectorAll(".js_detail_liste_filtre_produit_types").forEach(function (element){
        element.classList.remove("active");
    })
    $.post("fonctions/filtre_fetch_tailles.php", {id : id, categorie_id : categorie_en_cours}, function(data){
        /* placer l'indice sur le choix des types filtre*/
        typesFiltre.classList.add("active");
        /* afficher les tailles */
        if(data.result == "ok")
        {
            div_filtre_tailles.classList.remove("null");
            details_filtre_tailles.innerHTML = data.msg;
        }
        else
        {
            div_filtre_tailles.classList.add("null");
            details_filtre_tailles.innerHTML = "";
        }
        /* ajuster le filtre */
        types_en_cours = id;
        gestionAffichageArticle();
    })
}
/* filtrer les tailles */
function filtre_tailles(id = "", nom = "", slug = "")
{
    let taillesFiltre = document.querySelector(".js_detail_liste_filtre_produit_tailles_"+id);
    /* si c'est une anullation de filtre */
    if(taillesFiltre.classList.contains("active"))
    {
        /* retirer l'indice sur le choix de la taille filtre et placer le nom */
        taillesFiltre.classList.remove("active");

        taille_en_cours = 0;
        recherche_en_cours = "";
        page = 1;
        offset = 0;
        /* ajuster le filtre */
        gestionAffichageArticle();
        return;
    }
    /* vider la recherche */
    recherche_en_cours = "";
    page = 1;
    offset = 0;
    /* retirer tout les indices de choix de filtre taille */
    document.querySelectorAll(".js_detail_liste_filtre_produit_tailles").forEach(function (element){
        element.classList.remove("active");
    })
    /* placer l'indice sur le choix de la taille filtre*/
    taillesFiltre.classList.add("active");
    taille_en_cours = id;
    gestionAffichageArticle();
}
/* changer l'url */
function setUrl(slug) {
    history.pushState(
        { slug },
        "",
        "/" + slug
    );
}