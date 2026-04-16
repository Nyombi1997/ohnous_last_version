(function ($) {
    var PAGE_SIZE = 12;
    var state = {
        categorie: 0,
        categorieNom: "",
        categorieSlug: "",
        type: 0,
        typeNom: "",
        typeSlug: "",
        taille: 0,
        tailleNom: "",
        tailleSlug: "",
        boutique: 0,
        boutiqueNom: "",
        boutiqueSlug: "",
        prix: "",
        prixLabel: "",
        recherche: "",
        order: "date_desc",
        offset: 0,
        loading: false,
        hasMore: true
    };

    var els = {};

    function cacheDom() {
        els.results = document.getElementById("articles_results");
        els.loader = document.getElementById("articles_loading_state");
        els.empty = document.getElementById("articles_empty_state");
        els.filtersSummary = document.getElementById("shop_filters_summary");
        els.heroTitle = document.getElementById("changing-word");
        els.input = document.getElementById("input_search_bar_2");
        els.sort = document.getElementById("article_sort_order");
        els.price = document.getElementById("details_filtre_prix");
        els.priceWrapper = document.getElementById("div_filtre_prix");
        els.types = document.getElementById("details_filtre_types");
        els.typesWrapper = document.getElementById("div_filtre_types");
        els.tailles = document.getElementById("details_filtre_tailles");
        els.taillesWrapper = document.getElementById("div_filtre_tailles");
        els.categories = document.getElementById("details_filtre_categories");
        els.resetButton = document.getElementById("shop_reset_button");
    }

    function isShopPage() {
        return !!document.getElementById("shop_catalog_page");
    }

    function normalizeId(id) {
        var parsed = parseInt(id, 10);
        return isNaN(parsed) ? 0 : parsed;
    }

    function setLoading(active) {
        state.loading = !!active;
        if (els.loader) {
            els.loader.classList.toggle("is-visible", !!active);
        }
    }

    function resetPagination() {
        state.offset = 0;
        state.hasMore = true;
    }

    function hasActiveFilters() {
        return !!(
            state.categorie ||
            state.type ||
            state.taille ||
            state.prix ||
            (state.recherche || "").trim() !== ""
        );
    }

    function updateResetButtonVisibility() {
        if (!els.resetButton) {
            return;
        }

        els.resetButton.classList.toggle("null", !hasActiveFilters());
    }

    function renderSummary() {
        if (!els.filtersSummary) {
            return;
        }

        var parts = [];

        if (state.recherche) {
            parts.push('<span class="shop-active-pill is-search"><i class="fa-solid fa-magnifying-glass"></i> ' + state.recherche + '</span>');
        }
        if (state.categorieNom) {
            parts.push('<span class="shop-active-pill">Catégorie : ' + state.categorieNom + '</span>');
        }
        if (state.typeNom) {
            parts.push('<span class="shop-active-pill">Type : ' + state.typeNom + '</span>');
        }
        if (state.tailleNom) {
            parts.push('<span class="shop-active-pill">Taille : ' + state.tailleNom + '</span>');
        }
        if (state.prixLabel) {
            parts.push('<span class="shop-active-pill">Prix : ' + state.prixLabel + '</span>');
        }

        els.filtersSummary.innerHTML = parts.join("");
        els.filtersSummary.classList.toggle("null", parts.length === 0);
    }

    function updateHeroTitle() {
        if (!els.heroTitle) {
            return;
        }

        var title = "Shop";
        var documentTitle = "Shop | OhNous";

        if (state.recherche) {
            title = state.recherche;
            documentTitle = state.recherche + " | OhNous";
        } else if (state.categorieNom || state.typeNom || state.tailleNom || state.prixLabel) {
            var chunks = [];
            if (state.categorieNom) {
                chunks.push(state.categorieNom);
            }
            if (state.typeNom) {
                chunks.push(state.typeNom);
            }
            if (state.tailleNom) {
                chunks.push(state.tailleNom);
            }
            if (state.prixLabel) {
                chunks.push(state.prixLabel);
            }
            title = chunks.join(" • ");
            documentTitle = title + " | OhNous";
        }

        els.heroTitle.textContent = title;
        document.title = documentTitle;
    }

    function updateUrl() {
        var params = new URLSearchParams();

        if (state.recherche) {
            params.set("query", state.recherche);
        }
        if (state.categorieSlug) {
            params.set("categorie", state.categorieSlug);
        }
        if (state.typeSlug) {
            params.set("type", state.typeSlug);
        }
        if (state.tailleSlug) {
            params.set("taille", state.tailleSlug);
        }
        if (state.prix) {
            params.set("prix", state.prix);
        }
        if (state.order && state.order !== "date_desc") {
            params.set("order", state.order);
        }

        var nextUrl = "/shop" + (params.toString() ? "?" + params.toString() : "");
        history.replaceState({ shop: true }, "", nextUrl);
    }

    function showEmptyState(show) {
        if (els.empty) {
            els.empty.classList.toggle("null", !show);
        }
    }

    function syncActiveClasses() {
        document.querySelectorAll(".js_detail_liste_filtre_produit").forEach(function (node) {
            node.classList.remove("active");
        });
        document.querySelectorAll(".js_detail_liste_filtre_produit_prix").forEach(function (node) {
            node.classList.remove("active");
        });

        if (state.categorie) {
            var categoryNode = document.querySelector(".js_detail_liste_filtre_produit_" + state.categorie);
            if (categoryNode) {
                categoryNode.classList.add("active");
            }
        }

        if (state.prix) {
            var priceNode = document.querySelector(".js_detail_liste_filtre_produit_prix_" + state.prix);
            if (priceNode) {
                priceNode.classList.add("active");
            }
        }
    }

    function refreshPriceOptions() {
        if (!els.price || !els.priceWrapper) {
            return;
        }

        $.post("/fonctions/filtre_fetch_prix.php", {
            categorie: state.categorie,
            types: state.type,
            taille: state.taille,
            boutique: state.boutique,
            recherche: state.recherche,
            prix: state.prix,
            order: state.order
        }, function (data) {
            if (data && data.result === "ok" && data.msg !== "") {
                els.price.innerHTML = data.msg;
                els.priceWrapper.classList.remove("null");
            } else {
                els.price.innerHTML = "";
                els.priceWrapper.classList.add("null");
            }
        }, "json");
    }

    function refreshTypes() {
        if (!els.types || !els.typesWrapper) {
            return;
        }

        if (!state.categorie) {
            els.types.innerHTML = "";
            els.typesWrapper.classList.add("null");
            return;
        }

        $.post("/fonctions/filtre_fetch_types.php", {
            id: state.categorie,
            types: state.type,
            taille: state.taille
        }, function (data) {
            if (data && data.result === "ok" && data.msg !== "") {
                els.types.innerHTML = data.msg;
                els.typesWrapper.classList.remove("null");

                if (data.msg2 !== undefined && data.msg2 !== "") {
                    els.tailles.innerHTML = data.msg2;
                    els.taillesWrapper.classList.remove("null");
                } else if (!state.type) {
                    els.tailles.innerHTML = "";
                    els.taillesWrapper.classList.add("null");
                }
            } else {
                els.types.innerHTML = "";
                els.typesWrapper.classList.add("null");
                if (!state.type) {
                    els.tailles.innerHTML = "";
                    els.taillesWrapper.classList.add("null");
                }
            }
        }, "json");
    }

    function refreshTailles() {
        if (!els.tailles || !els.taillesWrapper) {
            return;
        }

        if (!state.type) {
            if (!state.categorie) {
                els.tailles.innerHTML = "";
                els.taillesWrapper.classList.add("null");
            }
            return;
        }

        $.post("/fonctions/filtre_fetch_tailles.php", {
            id: state.type,
            categorie: state.categorie,
            taille: state.taille
        }, function (data) {
            if (data && data.result === "ok" && data.msg !== "") {
                els.tailles.innerHTML = data.msg;
                els.taillesWrapper.classList.remove("null");
            } else {
                els.tailles.innerHTML = "";
                els.taillesWrapper.classList.add("null");
            }
        }, "json");
    }

    function refreshLinkedFilters() {
        refreshTypes();
        refreshTailles();
        refreshPriceOptions();
    }

    function requestArticles(reset) {
        if (!isShopPage() || state.loading || (!state.hasMore && !reset)) {
            return;
        }

        if (reset) {
            resetPagination();
            if (els.results) {
                els.results.innerHTML = "";
            }
            showEmptyState(false);
        }

        setLoading(true);

        $.post("/fonctions/filtre_article.php", {
            categorie: state.categorie,
            types: state.type,
            taille: state.taille,
            boutique: state.boutique,
            prix: state.prix,
            recherche: state.recherche,
            order: state.order,
            offset: state.offset,
            limit: PAGE_SIZE
        }, function (data) {
            setLoading(false);

            if (!data || data.result !== "ok") {
                if (reset) {
                    showEmptyState(true);
                }
                state.hasMore = false;
                return;
            }

            if (reset && parseInt(data.total, 10) === 0) {
                showEmptyState(true);
                state.hasMore = false;
                return;
            }

            if (data.msg && els.results) {
                els.results.insertAdjacentHTML("beforeend", data.msg);
                state.offset += parseInt(data.nombre, 10) || 0;
            }

            state.hasMore = !!data.has_more;

            if (typeof window.initLiquidImages === "function") {
                window.initLiquidImages(els.results || undefined);
            }

            syncActiveClasses();
            renderSummary();
            updateResetButtonVisibility();
            updateHeroTitle();
            updateUrl();
        }, "json").fail(function () {
            setLoading(false);
            if (reset) {
                showEmptyState(true);
            }
            state.hasMore = false;
        });
    }

    function loadMoreIfNeeded() {
        if (!isShopPage() || state.loading || !state.hasMore) {
            return;
        }

        var bottomTrigger = document.documentElement.scrollHeight - 360;
        var currentBottom = window.scrollY + window.innerHeight;

        if (currentBottom >= bottomTrigger) {
            requestArticles(false);
        }
    }

    function clearSearchPreservingInput() {
        state.recherche = "";
        if (els.input) {
            els.input.value = "";
        }
    }

    function resetAllFilters(skipRequest) {
        state.categorie = 0;
        state.categorieNom = "";
        state.categorieSlug = "";
        state.type = 0;
        state.typeNom = "";
        state.typeSlug = "";
        state.taille = 0;
        state.tailleNom = "";
        state.tailleSlug = "";
        state.prix = "";
        state.prixLabel = "";
        clearSearchPreservingInput();

        document.querySelectorAll(".detail_liste_filtre_produit").forEach(function (node) {
            node.classList.remove("active");
        });

        if (!skipRequest) {
            refreshLinkedFilters();
            requestArticles(true);
        }
    }

    function hydrateFromInitialState() {
        if (!window.shopInitialState) {
            return;
        }

        state.categorie = normalizeId(window.shopInitialState.categorie_id);
        state.categorieNom = window.shopInitialState.categorie_nom || "";
        state.categorieSlug = window.shopInitialState.categorie_slug || "";
        state.type = normalizeId(window.shopInitialState.type_id);
        state.typeNom = window.shopInitialState.type_nom || "";
        state.typeSlug = window.shopInitialState.type_slug || "";
        state.taille = normalizeId(window.shopInitialState.taille_id);
        state.tailleNom = window.shopInitialState.taille_nom || "";
        state.tailleSlug = window.shopInitialState.taille_slug || "";
        state.prix = window.shopInitialState.prix || "";
        state.prixLabel = window.shopInitialState.prix_label || "";
        state.recherche = window.shopInitialState.query || "";
        state.order = window.shopInitialState.order || "date_desc";

        if (els.input && state.recherche) {
            els.input.value = state.recherche;
        }
        if (els.sort) {
            els.sort.value = state.order;
        }
    }

    window.prevalueRecherche = function (query) {
        state.recherche = (query || "").trim();
        if (els.input) {
            els.input.value = state.recherche;
        }
        requestArticles(true);
    };

    window.prevalueTypes = function (id, nom, slug) {
        state.type = normalizeId(id);
        state.typeNom = nom || "";
        state.typeSlug = slug || "";
    };

    window.prevalueTailles = function (id, nom, slug) {
        state.taille = normalizeId(id);
        state.tailleNom = nom || "";
        state.tailleSlug = slug || "";
    };

    window.filtre_categorie = function (id, nom, slug, event) {
        if (event) {
            event.preventDefault();
        }

        id = normalizeId(id);

        if (!isShopPage()) {
            window.location.href = "/shop?categorie=" + encodeURIComponent(slug);
            return false;
        }

        if (state.categorie === id) {
            resetAllFilters(true);
        } else {
            state.categorie = id;
            state.categorieNom = nom || "";
            state.categorieSlug = slug || "";
            state.type = 0;
            state.typeNom = "";
            state.typeSlug = "";
            state.taille = 0;
            state.tailleNom = "";
            state.tailleSlug = "";
            state.prix = "";
            state.prixLabel = "";
            clearSearchPreservingInput();
        }

        refreshLinkedFilters();
        requestArticles(true);
        return false;
    };

    window.filtre_types = function (id, nom, slug, event) {
        if (event) {
            event.preventDefault();
        }

        id = normalizeId(id);

        if (!isShopPage()) {
            window.location.href = "/shop?type=" + encodeURIComponent(slug);
            return false;
        }

        if (state.type === id) {
            state.type = 0;
            state.typeNom = "";
            state.typeSlug = "";
            state.taille = 0;
            state.tailleNom = "";
            state.tailleSlug = "";
        } else {
            state.type = id;
            state.typeNom = nom || "";
            state.typeSlug = slug || "";
            state.taille = 0;
            state.tailleNom = "";
            state.tailleSlug = "";
            clearSearchPreservingInput();
        }

        refreshLinkedFilters();
        requestArticles(true);
        return false;
    };

    window.filtre_tailles = function (id, nom, slug, event) {
        if (event) {
            event.preventDefault();
        }

        id = normalizeId(id);

        if (!isShopPage()) {
            window.location.href = "/shop?taille=" + encodeURIComponent(slug);
            return false;
        }

        if (state.taille === id) {
            state.taille = 0;
            state.tailleNom = "";
            state.tailleSlug = "";
        } else {
            state.taille = id;
            state.tailleNom = nom || "";
            state.tailleSlug = slug || "";
            clearSearchPreservingInput();
        }

        refreshLinkedFilters();
        requestArticles(true);
        return false;
    };

    window.filtre_prix = function (key, label) {
        if (!isShopPage()) {
            window.location.href = "/shop?prix=" + encodeURIComponent(key);
            return false;
        }

        if (state.prix === key) {
            state.prix = "";
            state.prixLabel = "";
        } else {
            state.prix = key || "";
            state.prixLabel = label || "";
            clearSearchPreservingInput();
        }

        refreshPriceOptions();
        requestArticles(true);
        return false;
    };

    window.reinitialiserShop = function () {
        resetAllFilters(false);
        return false;
    };

    window.ohnousShopSubmitSearch = function (query) {
        state.recherche = (query || "").trim();
        state.categorie = 0;
        state.categorieNom = "";
        state.categorieSlug = "";
        state.type = 0;
        state.typeNom = "";
        state.typeSlug = "";
        state.taille = 0;
        state.tailleNom = "";
        state.tailleSlug = "";
        state.prix = "";
        state.prixLabel = "";
        refreshLinkedFilters();
        requestArticles(true);
    };

    window.ohnousShopApplySearchResult = function (kind, value, slug) {
        if (kind === "search") {
            window.ohnousShopSubmitSearch(value);
            return;
        }

        if (kind === "categorie") {
            state.categorie = normalizeId(window.shopLookup && window.shopLookup.categories && window.shopLookup.categories[slug] ? window.shopLookup.categories[slug].id : 0);
            state.categorieNom = value || "";
            state.categorieSlug = slug || "";
            state.type = 0;
            state.typeNom = "";
            state.typeSlug = "";
            state.taille = 0;
            state.tailleNom = "";
            state.tailleSlug = "";
            clearSearchPreservingInput();
            refreshLinkedFilters();
            requestArticles(true);
            return;
        }

        if (kind === "type") {
            state.type = normalizeId(window.shopLookup && window.shopLookup.types && window.shopLookup.types[slug] ? window.shopLookup.types[slug].id : 0);
            state.typeNom = value || "";
            state.typeSlug = slug || "";
            clearSearchPreservingInput();
            refreshLinkedFilters();
            requestArticles(true);
            return;
        }

        if (kind === "taille") {
            state.taille = normalizeId(window.shopLookup && window.shopLookup.tailles && window.shopLookup.tailles[slug] ? window.shopLookup.tailles[slug].id : 0);
            state.tailleNom = value || "";
            state.tailleSlug = slug || "";
            clearSearchPreservingInput();
            refreshLinkedFilters();
            requestArticles(true);
        }
    };

    $(document).ready(function () {
        cacheDom();

        if (!isShopPage()) {
            return;
        }

        hydrateFromInitialState();
        syncActiveClasses();
        renderSummary();
        updateResetButtonVisibility();
        updateHeroTitle();
        refreshLinkedFilters();
        requestArticles(true);

        if (els.sort) {
            els.sort.addEventListener("change", function () {
                state.order = this.value || "date_desc";
                requestArticles(true);
            });
        }

        if (els.resetButton) {
            els.resetButton.addEventListener("click", function (event) {
                event.preventDefault();
                resetAllFilters(false);
            });
        }

        window.addEventListener("scroll", loadMoreIfNeeded);
        window.addEventListener("resize", loadMoreIfNeeded);
    });
})(jQuery);
