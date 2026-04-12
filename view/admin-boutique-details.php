<?php
    ohnous_require_admin_or_redirect();

    $storeId = (int)($_GET['id'] ?? 0);
    $boutique = $storeId > 0 ? only_select('boutiques', 'id = '.$storeId, null, null) : null;

    if(!$boutique)
    {
        header('Location:/admin-boutiques');
        exit();
    }

    $articles = ohnous_admin_fetch_articles('', (int)$boutique['id']);
    $messages = ohnous_get_admin_store_messages((int)$boutique['id']);
    $threadHtml = '';
    foreach($messages as $message)
    {
        $message['profile'] = $boutique['profile'] ?? '';
        $threadHtml .= ohnous_render_admin_store_message_bubble($message);
    }
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel admin-store-detail-head">
        <div class="admin-store-detail-head__identity">
            <img src="<?= htmlspecialchars(ohnous_get_profile_picture($boutique['profile'] ?? '', 'boutique'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?>">
            <div>
                <h1><?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p><?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
        <a href="/admin-boutiques" class="btn_ohnous second">Retour aux boutiques</a>
    </section>

    <?= ohnous_render_admin_nav('boutiques') ?>

    <section class="admin-store-layout">
        <article class="liquid-panel admin-store-overview">
            <h2>Informations boutique</h2>
            <p><?= nl2br(htmlspecialchars((string)$boutique['description'], ENT_QUOTES, 'UTF-8')) ?></p>
            <div class="admin-store-overview__meta">
                <span class="status <?= ((int)$boutique['activer'] === 1) ? 'active' : 'inactive' ?>"><?= ((int)$boutique['activer'] === 1) ? 'Active' : 'Inactive' ?></span>
                <span>Activation : <?= !empty($boutique['date_activation_debut']) ? htmlspecialchars($boutique['date_activation_debut'], ENT_QUOTES, 'UTF-8') : 'Non définie' ?></span>
                <span>Expiration : <?= !empty($boutique['date_activation_fin']) ? htmlspecialchars($boutique['date_activation_fin'], ENT_QUOTES, 'UTF-8') : 'Non définie' ?></span>
            </div>
            <div class="admin-store-overview__actions">
                <button
                    type="button"
                    class="btn_ohnous admin-toggle-store"
                    data-store-id="<?= (int)$boutique['id'] ?>"
                    data-next-state="<?= ((int)$boutique['activer'] === 1) ? '0' : '1' ?>"
                >
                    <?= ((int)$boutique['activer'] === 1) ? 'Désactiver la boutique' : 'Activer la boutique' ?>
                </button>
            </div>
        </article>

        <article class="liquid-panel admin-store-messages">
            <div class="admin-store-messages__head">
                <h2>Conversation admin</h2>
                <p>L’avatar OhNous apparaît automatiquement dans cet échange.</p>
            </div>
            <div id="admin_store_thread" class="messages-thread admin-store-thread" data-store-id="<?= (int)$boutique['id'] ?>">
                <?= $threadHtml !== '' ? $threadHtml : '<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-comment-dots"></i></div><p>Aucun message envoyé à cette boutique pour le moment.</p></div>' ?>
            </div>
            <form id="admin_store_message_form" class="messages-composer">
                <textarea id="admin_store_message_text" placeholder="Écrire un message à cette boutique..."></textarea>
                <button type="submit" class="btn_ohnous">Envoyer</button>
            </form>
        </article>
    </section>

    <section class="liquid-panel admin-store-articles">
        <div class="admin-store-articles__head">
            <h2>Articles de cette boutique</h2>
            <span><?= count($articles) ?> article(s)</span>
        </div>
        <div class="admin-mini-grid">
            <?php if(empty($articles)): ?>
                <div class="empty-liquid-state compact">
                    <div class="empty-liquid-state__icon"><i class="fa-regular fa-box-open"></i></div>
                    <p>Cette boutique n’a pas encore d’article.</p>
                </div>
            <?php else: ?>
                <?php foreach($articles as $article): ?>
                    <?php
                        $pricing = ohnous_get_article_pricing($article);
                        $image = ohnous_get_article_primary_image((int)$article['id']);
                    ?>
                    <article class="admin-mini-article">
                        <div class="admin-mini-article__image">
                            <?php if($image): ?>
                                <img src="<?= htmlspecialchars($image['img'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($article['nom'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
                        </div>
                        <div class="admin-mini-article__content">
                            <strong><?= htmlspecialchars($article['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= $pricing['promo_actif'] ? '<s>$ '.number_format($pricing['prix_initial'], 2, '.', ' ').'</s> $ '.number_format($pricing['prix_final'], 2, '.', ' ') : '$ '.number_format($pricing['prix_final'], 2, '.', ' ') ?></span>
                            <a href="/admin-editer-article?id=<?= (int)$article['id'] ?>">Modifier</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<script src="/asset/js/admin_boutiques.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_boutiques.js") ?>" defer></script>
