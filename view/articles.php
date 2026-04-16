<?php
    $GLOBALS['no_filtre'] = 'ok';

    function ohnous_find_term_by_slug($table, $slug)
    {
        global $bdd;

        $slug = trim((string)$slug);
        if($slug === '')
        {
            return null;
        }

        $stmt = $bdd->prepare("SELECT * FROM {$table} WHERE slug = :slug LIMIT 1");
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    $selectedCategory = $GLOBALS['categorie'] ?? null;
    $selectedType = $GLOBALS['types'] ?? null;
    $selectedSize = $GLOBALS['tailles'] ?? null;

    if(!$selectedCategory && isset($_GET['categorie']))
    {
        $selectedCategory = ohnous_find_term_by_slug('categorie', $_GET['categorie']);
    }

    if(!$selectedType && isset($_GET['type']))
    {
        $selectedType = ohnous_find_term_by_slug('types', $_GET['type']);
    }

    if(!$selectedSize && isset($_GET['taille']))
    {
        $selectedSize = ohnous_find_term_by_slug('tailles', $_GET['taille']);
    }

    $selectedPrice = trim((string)($_GET['prix'] ?? ''));
    $priceRanges = ohnous_get_price_filter_ranges();
    $selectedPriceLabel = isset($priceRanges[$selectedPrice]) ? $priceRanges[$selectedPrice]['label'] : '';

    $categories = select_bdd($bdd, "categorie", null, null, 0, "nom", false);
    $lookup = [
        'categories' => [],
        'types' => [],
        'tailles' => [],
    ];

    foreach(select_bdd($bdd, "categorie", null, null, 0, "nom", false) as $row)
    {
        $lookup['categories'][$row['slug']] = [
            'id' => (int)$row['id'],
            'nom' => $row['nom']
        ];
    }

    foreach(select_bdd($bdd, "types", null, null, 0, "nom", false) as $row)
    {
        $lookup['types'][$row['slug']] = [
            'id' => (int)$row['id'],
            'nom' => $row['nom']
        ];
    }

    foreach(select_bdd($bdd, "tailles", null, null, 0, "nom", false) as $row)
    {
        $lookup['tailles'][$row['slug']] = [
            'id' => (int)$row['id'],
            'nom' => $row['nom']
        ];
    }

    $initialState = [
        'categorie_id' => (int)($selectedCategory['id'] ?? 0),
        'categorie_nom' => (string)($selectedCategory['nom'] ?? ''),
        'categorie_slug' => (string)($selectedCategory['slug'] ?? ''),
        'type_id' => (int)($selectedType['id'] ?? 0),
        'type_nom' => (string)($selectedType['nom'] ?? ''),
        'type_slug' => (string)($selectedType['slug'] ?? ''),
        'taille_id' => (int)($selectedSize['id'] ?? 0),
        'taille_nom' => (string)($selectedSize['nom'] ?? ''),
        'taille_slug' => (string)($selectedSize['slug'] ?? ''),
        'prix' => $selectedPrice,
        'prix_label' => $selectedPriceLabel,
        'query' => trim((string)($_GET['query'] ?? '')),
        'order' => trim((string)($_GET['order'] ?? 'date_desc')),
    ];

    $initialTitle = 'Shop';
    if($initialState['query'] !== '')
    {
        $initialTitle = $initialState['query'];
    }
    elseif($initialState['categorie_nom'] !== '' || $initialState['type_nom'] !== '' || $initialState['taille_nom'] !== '' || $initialState['prix_label'] !== '')
    {
        $parts = array_filter([
            $initialState['categorie_nom'],
            $initialState['type_nom'],
            $initialState['taille_nom'],
            $initialState['prix_label'],
        ]);
        $initialTitle = implode(' • ', $parts);
    }
?>
<script>
    window.shopInitialState = <?= json_encode($initialState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.shopLookup = <?= json_encode($lookup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<div class="intro-hero sans_categorie">
    <div class="blob-bg"></div>
    <div class="intro-text">
        <h1><span id="changing-word-container"><span id="changing-word"><?= htmlspecialchars($initialTitle, ENT_QUOTES, 'UTF-8') ?></span></span></h1>
    </div>
</div>

<section class="parent_container_affiche_produit shop-catalog-page" id="shop_catalog_page">
    <aside class="container_filtre_produit">
        <div class="sous_container_filtre_produit shop-filter-panel">
            <div class="shop-filter-head">
                <div>
                    <p class="shop-filter-head__eyebrow">Catalogue</p>
                    <h2 class="shop-filter-head__title">Filtres</h2>
                </div>
                <a href="/shop" class="btn_voir_plus shop-reset-button null" id="shop_reset_button">Réinitialiser</a>
            </div>

            <div class="shop-toolbar">
                <label class="shop-toolbar__field" for="article_sort_order">
                    <span>Trier par</span>
                    <select id="article_sort_order">
                        <option value="date_desc"<?= $initialState['order'] === 'date_desc' ? ' selected' : '' ?>>Nouveautés</option>
                        <option value="prix_asc"<?= $initialState['order'] === 'prix_asc' ? ' selected' : '' ?>>Prix croissant</option>
                        <option value="prix_desc"<?= $initialState['order'] === 'prix_desc' ? ' selected' : '' ?>>Prix décroissant</option>
                    </select>
                </label>
            </div>

            <div class="shop-filters-summary null" id="shop_filters_summary"></div>

            <div class="div_liste_filtre_produit" id="div_filtre_categories">
                <div class="titre_liste_filtre_produit"><p>Catégorie(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_categories">
                        <?php foreach($categories as $category): ?>
                            <?php
                                $categoryArticles = select_bdd($bdd, "categorie_article", "categorie = '".(int)$category['id']."'", null, 0, null, false);
                                $count = 0;
                                foreach($categoryArticles as $categoryArticle)
                                {
                                    $detailArticle = only_select("articles", "id = '".(int)$categoryArticle['article']."'", null, null);
                                    if($detailArticle && ohnous_is_article_visible($detailArticle))
                                    {
                                        $count++;
                                    }
                                }

                                if($count === 0)
                                {
                                    continue;
                                }

                                $active = ((int)$initialState['categorie_id'] === (int)$category['id']) ? 'active' : '';
                            ?>
                            <div class="detail_liste_filtre_produit <?= $active ?> js_detail_liste_filtre_produit js_detail_liste_filtre_produit_<?= (int)$category['id'] ?>" onclick="return filtre_categorie(<?= (int)$category['id'] ?>, <?= ohnous_js_html_arg($category['nom']) ?>, <?= ohnous_js_html_arg($category['slug']) ?>)">
                                <div class="nom"><?= htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="nombre"><?= $count ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="div_liste_filtre_produit null" id="div_filtre_types">
                <div class="titre_liste_filtre_produit"><p>Type(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_types"></div>
                </div>
            </div>

            <div class="div_liste_filtre_produit null" id="div_filtre_tailles">
                <div class="titre_liste_filtre_produit"><p>Taille(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_tailles"></div>
                </div>
            </div>

            <div class="div_liste_filtre_produit null" id="div_filtre_prix">
                <div class="titre_liste_filtre_produit"><p>Prix</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_prix"></div>
                </div>
            </div>
        </div>
    </aside>

    <div class="container_affiche_produit vue_article shop-results-shell">
        <div class="shop-results-head">
            <div>
                <p class="shop-results-head__eyebrow">Sélection Ohnous</p>
                <h2 class="shop-results-head__title">Articles disponibles</h2>
            </div>
        </div>

        <div class="shop-loading-state" id="articles_loading_state">
            <span class="shop-loading-state__bubble"></span>
            <span class="shop-loading-state__bubble"></span>
            <span class="shop-loading-state__bubble"></span>
        </div>

        <div class="shop-empty-state null" id="articles_empty_state">
            <div class="empty-liquid-state">
                <div class="empty-liquid-state__icon"><i class="fa-solid fa-box-open"></i></div>
                <p>Aucun article disponible.</p>
            </div>
        </div>

        <div class="container_affiche_produit vue_article" id="articles_results"></div>
    </div>
</section>
