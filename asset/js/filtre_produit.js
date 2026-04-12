/* variable pour recherche */
if(donnee_de_recherche==undefined)
{
    let donnee_de_recherche = document.querySelectorAll("#donnee_de_recherche");
}
if(input_search_bar_2==undefined)
{
    let input_search_bar_2 = document.getElementById("input_search_bar_2");
}
const page_pour_filtre = true;
/* variables pour filtre */
let categorie_en_cours = 0;
let categorie_en_cours_filtre = 0;
let categorie_en_cours_nom = "";
let categorie_en_cours_slug = "";
let types_en_cours = 0;
let types_en_cours_filtre = 0;
let types_en_cours_nom = "";
let types_en_cours_slug = "";
let taille_en_cours = 0;
let taille_en_cours_filtre = 0;
let taille_en_cours_nom = "";
let taille_en_cours_slug = "";
let boutique_en_cours = 0;
let boutique_en_cours_filtre = 0;
let boutique_en_cours_nom = "";
let boutique_en_cours_slug = "";
let promotion_en_cours = 0;
let recherche_en_cours = "";
let page = 1;
let offset = 0;
let div_filtre_categories = document.getElementById("div_filtre_categories");
let details_filtre_categories = document.getElementById("details_filtre_categories");
let div_filtre_tailles = document.getElementById("div_filtre_tailles");
let details_filtre_tailles = document.getElementById("details_filtre_tailles");
let div_filtre_types = document.getElementById("div_filtre_types");
let details_filtre_types = document.getElementById("details_filtre_types");
let changingWord = document.getElementById("changing-word");
/* trouver les tailles via le type */
function fetchTaillesViaTypes(id = 0)
{
    $.post("/fonctions/filtre_fetch_tailles.php", {id : id, categorie : categorie_en_cours, taille: taille_en_cours_filtre}, function(data){
        /* afficher les tailles */
        if(data.result == "ok")
        {
            if(data.msg!="")
            {
                div_filtre_tailles.classList.remove("null");
                details_filtre_tailles.innerHTML = data.msg;
            }
        }
        else
        {
            div_filtre_tailles.classList.add("null");
            details_filtre_tailles.innerHTML = "";
        }
        gestionAffichageArticle();
    })
}
/* trouver les types via la categorie */
function fetchTypesViaCategorie(id = 0)
{
    $.post("/fonctions/filtre_fetch_types.php", {id : id, types : types_en_cours, taille : taille_en_cours}, function(data){
        /* afficher les types */
        if(data.result == "ok" && data.msg!='')
        {
            div_filtre_types.classList.remove("null");
            details_filtre_types.innerHTML = data.msg;
        }
        else
        {
            div_filtre_types.classList.add("null");
            details_filtre_types.innerHTML = "";
        }
        gestionAffichageArticle();
    })
}
/* prevaloriser la recherhe */
function prevalueRecherche(query)
{
    recherche_en_cours = query;
    gestionAffichageArticle();
}
/* prevaloriser les types */
function prevalueTypes(id = 0, nom = "", slug = "")
{
    types_en_cours = id;
    types_en_cours_nom = nom;
    types_en_cours_slug = slug;
}
/* prevaloriser les tailles */
function prevalueTailles(id = 0, nom = "", slug = "")
{
    taille_en_cours = id;
    taille_en_cours_nom = nom;
    taille_en_cours_slug = slug;
}
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
    
    /* si on est pas à la page d'accueuil */
    if(typeof home_page === "undefined")
    {
        /* informer qu'il faut afficher d'autres articles */
        $.post("/fonctions/filtre_article.php",{
            categorie : categorie_en_cours,
            types : types_en_cours,
            taille : taille_en_cours,
            boutique : boutique_en_cours,
            promotion : promotion_en_cours,
            recherche : recherche_en_cours,
            page : page,
            offset : offset,
        },
        function(data){
            /* si c'est la première fois qu'on charge des images mais qu'il n'y aucun produit disponible */
            if(page==1 && data.msg=="")
            {
                document.getElementById("afficher_article").innerHTML = `
                    <!-- HTML !-->
                    <div class="div_btn_voir_plus">
                        <a class="btn_voir_plus error" role="button">Aucun article disponible <i class="fa-solid fa-triangle-exclamation"></i></a>
                    </div>
                `;
                if(document.getElementById("div_btn_voir_plus")==undefined)
                {
                    document.getElementById("afficher_article").innerHTML += `<!-- HTML !-->
                                                                                <div class="div_btn_voir_plus" id="div_btn_voir_plus">
                                                                                    <a href="/articles" class="btn_voir_plus" role="button">Decouvrez plus d'articles  <i class="fa-solid fa-arrow-right-long"></i></a>
                                                                                </div>`;
                }
            }
            else if(data.nombre != 0)
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
                if(document.getElementById("div_btn_voir_plus")==undefined)
                {
                    document.getElementById("afficher_article").innerHTML += `<!-- HTML !-->
                                                                                <div class="div_btn_voir_plus" id="div_btn_voir_plus">
                                                                                    <a href="/articles" class="btn_voir_plus" role="button">Decouvrez plus d'articles  <i class="fa-solid fa-arrow-right-long"></i></a>
                                                                                </div>`;
                }
            }
        })
    }
}
/* filtrer les catégories */
function filtre_promotion()
{
    let promoFilter = document.querySelector(".js_promo_filter");
    promotion_en_cours = promotion_en_cours === 1 ? 0 : 1;
    page = 1;
    offset = 0;

    if(promoFilter)
    {
        promoFilter.classList.toggle("active", promotion_en_cours === 1);
    }

    gestionAffichageArticle();
}
function filtre_categorie(id = "", nom = "", slug = "", event = null, recherche = null, autofiltre = null)
{
    /* si on est à la page d'accueuil */
    if(typeof home_page !== "undefined")
    {
        window.location.href = "/categorie/"+slug;
        return;
    }
    /* si on a un evenement envoyer */
    if(event)
    {
        event.preventDefault();
        donnee_de_recherche.forEach(function (element){
            element.innerHTML = "";
            element.classList.add("null");
        })
        input_search_bar_2.value = nom;
    }
    else
    {
        input_search_bar_2.value = "";
    }
    let categoreiFiltre = document.querySelector(".js_detail_liste_filtre_produit_"+id);
    /* si c'est une anullation de filtre */
    if(categoreiFiltre!=undefined)
    {
        if(categoreiFiltre.classList.contains("active"))
        {
            /* retirer l'indice sur le choix de la categories filtre et retirer le nom */
            categoreiFiltre.classList.remove("active");

            categorie_en_cours = 0;
            categorie_en_cours_nom = "";
            categorie_en_cours_slug = "";
            types_en_cours = 0;
            types_en_cours_nom = "";
            types_en_cours_slug = "";
            taille_en_cours = 0;
            taille_en_cours_filtre = 0;
            taille_en_cours_nom = "";
            taille_en_cours_slug = "";
            recherche_en_cours = "";
            page = 1;
            offset = 0;
            /* vider les tailles */
            div_filtre_tailles.classList.add("null");
            details_filtre_tailles.innerHTML = "";
            /* vider les types */
            div_filtre_types.classList.add("null");
            details_filtre_tailles.innerHTML = "";
            /* recuprrer toutes les catégories */
            $.post("/fonctions/filtre_fetch_all_categories.php", {found : "ok"}, function(data){
                /* afficher les categories */
                if(data.result == "ok")
                {
                    div_filtre_categories.classList.remove("null");
                    details_filtre_categories.innerHTML = data.msg;
                }
                else
                {
                    Swal.fire({
                        title: "Une erreur s'est produite",
                        text: "Veuillez réessailler...",
                        icon: "error",
                        confirmButtonColor: '#6775d6',
                        timer: 2000
                    });
                }
                gestionAffichageArticle();
            })
            /* ajuster le filtre */
            gestionAffichageArticle();
            return;
        }
    }
    /* vider la recherche et filtre*/
    if(!autofiltre && types_en_cours==0)
    {
        types_en_cours = 0;
        types_en_cours_nom = "";
        types_en_cours_slug = "";
        taille_en_cours = 0;
        taille_en_cours_nom = "";
        taille_en_cours_slug = "";
        recherche_en_cours = "";
    }
    page = 1;
    offset = 0;
    /* retirer tout les indices de choix de filtre catégiorie */
    document.querySelectorAll(".js_detail_liste_filtre_produit").forEach(function (element){
        element.classList.remove("active");
    })
    /* si ça viens pas d'une recherche */
    if(!recherche)
    {
        $.post("/fonctions/filtre_fetch_types.php", {id : id, types : types_en_cours, taille : taille_en_cours}, function(data){
            /* placer l'indice sur le choix de la categorie filtre et placer le nom */
            categoreiFiltre.classList.add("active");
            /* vider les tailles par defaut */
            div_filtre_tailles.classList.add("null");
            details_filtre_tailles.innerHTML = "";
            /* afficher les types */
            if(data.result == "ok" && data.msg!='')
            {
                div_filtre_types.classList.remove("null");
                details_filtre_types.innerHTML = data.msg;
            }
            else
            {
                div_filtre_types.classList.add("null");
                details_filtre_types.innerHTML = "";
            }
            /* afficher les tailes */
            if(data.result == "ok" && data.msg2!='')
            {
                div_filtre_tailles.classList.remove("null");
                details_filtre_tailles.innerHTML = data.msg2;
            }
            else
            {
                div_filtre_tailles.classList.add("null");
                details_filtre_tailles.innerHTML = "";
            }
            /* ajuster le filtre */
            categorie_en_cours = id;
            categorie_en_cours_nom = nom;
            categorie_en_cours_slug = slug;
            if(autofiltre && types_en_cours!=0)
            {
                filtre_types(types_en_cours, types_en_cours_nom, types_en_cours_slug, event = null, recherche = null, "ok");
            }
            else
            {
                gestionAffichageArticle();
            }
        })
    }
    else
    {
        /* tout remetre à zéro */
        types_en_cours = 0;
        types_en_cours_nom = "";
        types_en_cours_slug = "";
        taille_en_cours = 0;
        taille_en_cours_nom = "";
        taille_en_cours_slug = "";
        boutique_en_cours = 0;
        boutique_en_cours_nom = "";
        recherche_en_cours = "";
        $.post("/fonctions/filtre_fetch_by_categorie.php", {id : id, found : "ok"}, function(data){
            /* afficher les tailles */
            if(data.result == "ok")
            {
                /* si il y'a des types */
                if(data.msg!='')
                {
                    div_filtre_types.classList.remove("null");
                    details_filtre_types.innerHTML = data.msg;                   
                }
                else
                {
                    div_filtre_types.classList.add("null");
                    details_filtre_types.innerHTML = "";
                }
                /* vider les tailles */
                div_filtre_tailles.classList.add("null");
                details_filtre_tailles.innerHTML = "";
                /* ajouter le(s) categorie(s) en cours */
                div_filtre_categories.classList.remove("null");
                details_filtre_categories.innerHTML = data.msg2;
            }
            else
            {
                Swal.fire({
                    title: "Une erreur s'est produite",
                    text: "Veuillez réessailler...",
                    icon: "error",
                    confirmButtonColor: '#6775d6',
                    timer: 2000
                });
            }
            /* ajuster le filtre */
            types_en_cours = id;
            types_en_cours_nom = nom;
            types_en_cours_slug = slug;
            gestionAffichageArticle();
        })
    }
}
/* filtrer les types */
function filtre_types(id = "", nom = "", slug = "", event = null, recherche = null, autofiltre = null)
{
    /* si on est à la page d'accueuil */
    if(typeof home_page !== "undefined")
    {
        window.location.href = "/type/"+slug;
        return;
    }
    /* si on a un evenement envoyer */
    if(event)
    {
        event.preventDefault();
        donnee_de_recherche.forEach(function (element){
            element.innerHTML = "";
            element.classList.add("null");
        })
        input_search_bar_2.value = nom;
    }
    else
    {
        input_search_bar_2.value = "";
    }
    /* si ça viens pas d'une recherche */
    let typesFiltre = document.querySelector(".js_detail_liste_filtre_produit_types"+id);
    /* si c'est une anullation de filtre */
    if(typesFiltre==undefined)
    {
        types_en_cours = 0;
        types_en_cours_nom = "";
        types_en_cours_slug = "";
    }
    if(recherche == null && autofiltre == null)
    {
        /* si c'est une anullation de filtre */
        if(typesFiltre!=undefined)
        {
            if(typesFiltre.classList.contains("active"))
            {
                types_en_cours = 0;
                types_en_cours_nom = "";
                types_en_cours_slug = "";
                taille_en_cours = 0;
                taille_en_cours_filtre = 0;
                taille_en_cours_slug = "";
                taille_en_cours_nom = "";
                recherche_en_cours = "";
                page = 1;
                offset = 0;
                /* recuprrer toutes les catégories */
                $.post("/fonctions/filtre_fetch_all_categories.php", {found : "ok", categorie: categorie_en_cours, taille:taille_en_cours}, function(data){
                    /* afficher les categories */
                    if(data.result == "ok")
                    {
                        div_filtre_categories.classList.remove("null");
                        details_filtre_categories.innerHTML = data.msg;
                        /* vider les tailles */
                        div_filtre_tailles.classList.add("null");
                        details_filtre_tailles.innerHTML = "";
                        /* retirer l'indice sur le choix des types filtre et placer le nom */
                        typesFiltre.classList.remove("active");
                        /* vider les types */
                        div_filtre_types.classList.add("null");
                        details_filtre_tailles.innerHTML = "";
                    }
                    else
                    {
                        Swal.fire({
                            title: "Une erreur s'est produite",
                            text: "Veuillez réessailler...",
                            icon: "error",
                            confirmButtonColor: '#6775d6',
                            timer: 2000
                        });
                    }
                    /* ajuster le filtre */
                    if(categorie_en_cours!=0)
                    {
                        fetchTypesViaCategorie(categorie_en_cours);
                    }
                    else
                    {
                        gestionAffichageArticle();
                    }
                })
                return;
            }
        }
    }
    /* vider la recherche et filtre*/
    if(!autofiltre && taille_en_cours==0)
    {
        taille_en_cours = 0;
        taille_en_cours_nom = "";
        taille_en_cours_slug = "";
        recherche_en_cours = "";
    }
    page = 1;
    offset = 0;
    /* retirer tout les indices de choix de filtre types */
    document.querySelectorAll(".js_detail_liste_filtre_produit_types").forEach(function (element){
        element.classList.remove("active");
    })
    /* si ça viens pas d'une recherche */
    if(!recherche && id!=0)
    {
        $.post("/fonctions/filtre_fetch_tailles.php", {id : id, categorie : categorie_en_cours, taille: taille_en_cours_filtre}, function(data){
            /* placer l'indice sur le choix des types filtre*/
            typesFiltre.classList.add("active");
            /* afficher les tailles */
            if(data.result == "ok")
            {
                if(data.msg!="")
                {
                    div_filtre_tailles.classList.remove("null");
                    details_filtre_tailles.innerHTML = data.msg;
                }
            }
            else
            {
                div_filtre_tailles.classList.add("null");
                details_filtre_tailles.innerHTML = "";
            }
            /* ajuster le filtre */
            types_en_cours = id;
            types_en_cours_nom = nom;
            types_en_cours_slug = slug;
            if(autofiltre || taille_en_cours!=0)
            {
                filtre_tailles(taille_en_cours, taille_en_cours_nom, taille_en_cours_slug, null, null, "ok");
            }
            else
            {
                gestionAffichageArticle();
            }
        })
    }
    else if(id!=0)
    {
        /* tout remetre à zéro */
        categorie_en_cours = 0;
        categorie_en_cours_nom = "";
        categorie_en_cours_slug = "";
        boutique_en_cours = 0;
        recherche_en_cours = "";
        $.post("/fonctions/filtre_fetch_by_types.php", {id : id, taille : taille_en_cours, found : "ok"}, function(data){
            /* afficher les tailles */
            if(data.result == "ok")
            {
                /* si il y'a des categories */
                if(data.msg!='')
                {
                    div_filtre_categories.classList.remove("null");
                    details_filtre_categories.innerHTML = data.msg;                   
                }
                else
                {
                    div_filtre_categories.classList.add("null");
                    details_filtre_categories.innerHTML = "";
                }
                /* si il y'a des tailles */
                if(data.msg2!='')
                {
                    div_filtre_tailles.classList.remove("null");
                    details_filtre_tailles.innerHTML = data.msg2;
                }
                else
                {
                    div_filtre_tailles.classList.add("null");
                    details_filtre_tailles.innerHTML = "";
                }
                /* ajouter le type en cours */
                div_filtre_types.classList.remove("null");
                details_filtre_types.innerHTML = data.msg3;
            }
            else
            {
                div_filtre_tailles.classList.add("null");
                details_filtre_tailles.innerHTML = "";

                div_filtre_categories.classList.add("null");
                details_filtre_categories.innerHTML = "";
            }
            /* ajuster le filtre */
            types_en_cours = id;
            types_en_cours_nom = nom;
            types_en_cours_slug = slug;
            gestionAffichageArticle();
        })
    }
    else
    {
        gestionAffichageArticle();
    }
}
/* filtrer les tailles */
function filtre_tailles(id = "", nom = "", slug = "", event = null, recherche = null, autofiltre = null)
{
    /* si on est à la page d'accueuil */
    if(typeof home_page !== "undefined")
    {
        window.location.href = "/taille/"+slug;
        return;
    }
    /* si on a un evenement envoyer */
    if(event)
    {
        event.preventDefault();
        donnee_de_recherche.forEach(function (element){
            element.innerHTML = "";
            element.classList.add("null");
        })
        input_search_bar_2.value = nom;
    }
    else
    {
        input_search_bar_2.value = "";
    }
    let taillesFiltre = document.querySelector(".js_detail_liste_filtre_produit_tailles_"+id);
    /* si c'est une anullation de filtre */
    if(taillesFiltre!=undefined && !autofiltre && !recherche)
    {
        if(taillesFiltre.classList.contains("active"))
        {
            /* retirer l'indice sur le choix de la taille filtre et placer le nom */
            taillesFiltre.classList.remove("active");

            taille_en_cours = 0;
            taille_en_cours_filtre = 0;
            taille_en_cours_nom = "";
            taille_en_cours_slug = "";
            recherche_en_cours = "";
            page = 1;
            offset = 0;
            if(types_en_cours==0 && categorie_en_cours==0)
            {
                /* vider les tailles */
                div_filtre_tailles.classList.add("null");
                details_filtre_tailles.innerHTML = "";
                /* vider les types */
                div_filtre_types.classList.add("null");
                details_filtre_tailles.innerHTML = "";
                /* recuprrer toutes les catégories */
                $.post("/fonctions/filtre_fetch_all_categories.php", {found : "ok"}, function(data){
                    /* afficher les categories */
                    if(data.result == "ok")
                    {
                        div_filtre_categories.classList.remove("null");
                        details_filtre_categories.innerHTML = data.msg;
                    }
                    else
                    {
                        Swal.fire({
                            title: "Une erreur s'est produite",
                            text: "Veuillez réessailler...",
                            icon: "error",
                            confirmButtonColor: '#6775d6',
                            timer: 2000
                        });
                    }
                    gestionAffichageArticle();
                })
            }
            /* si il y'a un type actif */
            else if(types_en_cours!=0)
            {
                fetchTaillesViaTypes(id = types_en_cours);
            }
            else
            {
                gestionAffichageArticle();
            }
            return;
        }
    }
    /* vider la recherche */
    recherche_en_cours = "";
    page = 1;
    offset = 0;
    /* retirer tout les indices de choix de filtre taille */
    document.querySelectorAll(".js_detail_liste_filtre_produit_tailles").forEach(function (element){
        element.classList.remove("active");
    })
    
    /* si ça viens pas d'une recherche */
    if(!recherche)
    {
        /* placer l'indice sur le choix de la taille filtre*/
        taillesFiltre.classList.add("active");
        taille_en_cours = id;
        taille_en_cours_filtre = id;
        taille_en_cours_slug = slug;
        gestionAffichageArticle();
    }
    else
    {
        /* tout remetre à zéro */
        categorie_en_cours = 0;
        categorie_en_cours_nom = "";
        categorie_en_cours_slug = "";
        types_en_cours = 0;
        types_en_cours_nom = "";
        types_en_cours_slug = "";
        boutique_en_cours = 0;
        recherche_en_cours = "";
        $.post("/fonctions/filtre_fetch_by_tailles.php", {id : id, found : "ok"}, function(data){
            /* afficher les tailles */
            if(data.result == "ok")
            {
                /* si il y'a des categories */
                if(data.msg!='')
                {
                    div_filtre_categories.classList.remove("null");
                    details_filtre_categories.innerHTML = data.msg;                   
                }
                else
                {
                    div_filtre_categories.classList.add("null");
                    details_filtre_categories.innerHTML = "";
                }
                /* si il y'a des types */
                if(data.msg2!='')
                {
                    div_filtre_types.classList.remove("null");
                    details_filtre_types.innerHTML = data.msg2;
                }
                else
                {
                    div_filtre_types.classList.add("null");
                    details_filtre_types.innerHTML = "";
                }
                /* ajouter la taille en cours */
                div_filtre_tailles.classList.remove("null");
                details_filtre_tailles.innerHTML = data.msg3;
            }
            else
            {
                div_filtre_tailles.classList.add("null");
                details_filtre_tailles.innerHTML = "";

                div_filtre_types.classList.add("null");
                details_filtre_types.innerHTML = "";
            }
            /* ajuster le filtre */
            taille_en_cours = id;
            taille_en_cours_filtre = id;
            taille_en_cours_nom = nom;
            taille_en_cours_slug = slug;
            gestionAffichageArticle();
        })
    }
}

/* changer l'url */
function setUrlAndTitle() {
    
    /* si on est à la page d'accueuil */
    if(typeof home_page === "undefined")
    {
        let slug = "articles";
        /* placer categorie */
        if(categorie_en_cours_slug!='')
        {
            slug = "categorie/"+categorie_en_cours_slug;
        }
        /* placer types */
        if(slug!="articles" && types_en_cours_slug!='')
        {
            slug += "/type/"+types_en_cours_slug;
        }
        else if(types_en_cours_slug!='')
        {
            slug = "type/"+types_en_cours_slug;
        }
        /* placer tailles */
        if(slug!="articles" && taille_en_cours_slug!='')
        {
            slug += "/taille/"+taille_en_cours_slug;
        }
        else if(taille_en_cours_slug!='')
        {
            slug = "taille/"+taille_en_cours_slug;
        }
        /* si y'a pas de recherche */
        if(recherche_en_cours=='')
        {
            history.pushState(
                { slug },
                "",
                "/" + slug
            );
        }
        /* changer le titre */
        let title_page = "OhNous";
        let title = "Articles";
        /* Si categorie */
        if(categorie_en_cours_nom!='')
        {
            title_page = categorie_en_cours_nom + " | OhNous";
            title = categorie_en_cours_nom;
        }
        /* Si categorie */
        if(types_en_cours_nom!='')
        {
            if(categorie_en_cours_nom!='' && taille_en_cours_nom!='')
            {
                title_page = categorie_en_cours_nom + " | " + types_en_cours_nom + " | " + taille_en_cours_nom + " | OhNous";
                title = categorie_en_cours_nom + " | " + types_en_cours_nom + " | " + taille_en_cours_nom ;
            }
            else if(categorie_en_cours_nom!='')
            {
                title_page = categorie_en_cours_nom + " | " + types_en_cours_nom + " | OhNous";
                title = categorie_en_cours_nom + " | " + types_en_cours_nom;
            }
            else if(taille_en_cours_nom!='')
            {
                title_page = types_en_cours_nom + " | " + taille_en_cours_nom + " | OhNous";
                title = types_en_cours_nom + " | " + taille_en_cours_nom;
            }
            else
            {
                title_page = types_en_cours_nom + " | OhNous";
                title = types_en_cours_nom;
            }
        }
        /* Si taille */
        if(taille_en_cours_nom!='')
        {
            if(categorie_en_cours_nom!='' && types_en_cours_nom!='')
            {
                title_page = categorie_en_cours_nom + " | " + types_en_cours_nom + " | " + taille_en_cours_nom + " | OhNous";
                title = categorie_en_cours_nom + " | " + types_en_cours_nom + " | " + taille_en_cours_nom ;
            }
            else if(categorie_en_cours_nom!='')
            {
                title_page = categorie_en_cours_nom + " | " + taille_en_cours_nom + " | OhNous";
                title = categorie_en_cours_nom + " | " + taille_en_cours_nom;
            }
            else if(types_en_cours_nom!='')
            {
                title_page = types_en_cours_nom + " | " + taille_en_cours_nom + " | OhNous";
                title = types_en_cours_nom + " | " + taille_en_cours_nom;
            }
            else
            {
                title_page = taille_en_cours_nom + " | OhNous";
                title = taille_en_cours_nom;
            }
        }
        /* si c'est une recherche */
        if(recherche_en_cours!='')
        {
            title_page = "Recherche "+recherche_en_cours+" | OhNous";
            title = '<i class="fa-solid fa-magnifying-glass"></i> '+recherche_en_cours+"";
        }
        if(promotion_en_cours === 1)
        {
            title_page = "Promotions | OhNous";
            title = "Promotions";
        }
        changingWord.innerHTML = title;
        document.title = title_page;
    }
}
