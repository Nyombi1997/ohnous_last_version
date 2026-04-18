<?php
    ohnous_sync_test_store_activation();
?>
<div class="intro-hero sans_categorie boutique-directory-hero">
    <div class="blob-bg"></div>
    <div class="intro-text">
        <h1><span id="changing-word-container"><span id="changing-word">Boutiques</span></span></h1>
        <p class="directory-hero-subtitle">Explorez les univers actifs d’OhNous, du plus récent au plus ancien.</p>
    </div>
</div>

<section class="store-directory-shell liquid-panel" id="stores_directory_page">
    <div class="shop-results-head">
        <div>
            <p class="shop-results-head__eyebrow">Sélection Ohnous</p>
            <h2 class="shop-results-head__title">Boutiques à découvrir</h2>
        </div>
    </div>

    <div class="shop-loading-state" id="stores_directory_loader">
        <span class="shop-loading-state__bubble"></span>
        <span class="shop-loading-state__bubble"></span>
        <span class="shop-loading-state__bubble"></span>
    </div>

    <div class="shop-empty-state null" id="stores_directory_empty">
        <div class="empty-liquid-state">
            <div class="empty-liquid-state__icon"><i class="fa-solid fa-store"></i></div>
            <p>Aucune boutique active n’est disponible pour le moment.</p>
        </div>
    </div>

    <div class="public-store-grid" id="stores_directory_results"></div>
</section>

<script src="/asset/js/stores.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/stores.js") ?>" defer></script>
