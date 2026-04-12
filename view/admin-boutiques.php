<?php
    ohnous_require_admin_or_redirect();

    $search = trim((string)($_GET['search'] ?? ''));
    $status = trim((string)($_GET['status'] ?? 'all'));
    $boutiques = ohnous_admin_fetch_stores($search, $status);
    $storeSuggestions = array_map(function($boutique){
        return [
            'id' => (int)$boutique['id'],
            'label' => (string)$boutique['nom'],
            'email' => (string)($boutique['adresse_email'] ?? ''),
            'url' => '/admin-boutique?id='.(int)$boutique['id']
        ];
    }, $boutiques);
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>Boutiques</h1>
            <p>Retrouvez une boutique, activez son compte, désactivez-le ou ouvrez sa fiche détaillée.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('boutiques') ?>

    <form method="GET" action="/admin-boutiques" class="liquid-panel admin-search-bar">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" id="admin_store_search_input" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher une boutique, un email ou une description" autocomplete="off">
        <select name="status" id="admin_store_status">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Toutes</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Actives</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Pas encore activées</option>
            <option value="test" <?= $status === 'test' ? 'selected' : '' ?>>Boutiques test</option>
        </select>
        <button type="submit" class="btn_ohnous">Rechercher</button>
        <div class="admin-search-suggestions" id="admin_store_search_suggestions"></div>
    </form>

    <div class="admin-list-grid">
        <?php if(empty($boutiques)): ?>
            <div class="empty-liquid-state">
                <div class="empty-liquid-state__icon"><i class="fa-regular fa-store-slash"></i></div>
                <p>Aucune boutique ne correspond à votre recherche.</p>
            </div>
        <?php else: ?>
            <?php foreach($boutiques as $boutique): ?>
                <?php
                    $isActive = isset($boutique['activer']) && (int)$boutique['activer'] === 1;
                    $isTest = ohnous_is_test_store($boutique);
                    $profile = ohnous_get_profile_picture($boutique['profile'] ?? '', 'boutique');
                ?>
                <article class="liquid-panel admin-store-card">
                    <div class="admin-store-card__head">
                        <img src="<?= htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?>">
                        <div>
                            <h3><?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <p class="admin-store-card__description"><?= nl2br(htmlspecialchars((string)$boutique['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <div class="admin-store-card__meta">
                        <span class="status <?= $isActive ? 'active' : 'inactive' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                        <?php if($isTest): ?>
                            <span class="status test">Boutique test</span>
                        <?php endif; ?>
                        <span><?= !empty($boutique['date_activation_fin']) ? 'Fin : '.htmlspecialchars($boutique['date_activation_fin'], ENT_QUOTES, 'UTF-8') : 'Sans date de fin' ?></span>
                    </div>
                    <div class="admin-store-card__actions">
                        <a href="/admin-boutique?id=<?= (int)$boutique['id'] ?>" class="btn_ohnous second">Ouvrir la fiche</a>
                        <button
                            type="button"
                            class="btn_ohnous admin-toggle-store"
                            data-store-id="<?= (int)$boutique['id'] ?>"
                            data-next-state="<?= $isActive ? '0' : '1' ?>"
                            <?= $isTest ? 'data-is-test="1"' : '' ?>
                        >
                            <?= $isTest ? 'Toujours active' : ($isActive ? 'Désactiver' : 'Activer') ?>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
    window.adminStoreSuggestions = <?= json_encode($storeSuggestions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="/asset/js/admin_boutiques.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_boutiques.js") ?>" defer></script>
