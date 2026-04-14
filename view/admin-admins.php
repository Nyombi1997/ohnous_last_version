<?php
    ohnous_require_admin_or_redirect();

    $admins = ohnous_admin_fetch_admins();
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>Gestion des admins</h1>
            <p>Créez de nouveaux admins, générez un mot de passe automatiquement et envoyez un lien d'accès direct par email.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('admins') ?>

    <div class="admin-account-grid">
        <section class="liquid-panel admin-account-form-panel">
            <h2>Créer un admin</h2>
            <form id="admin_account_form" class="delivery-zone-form">
                <div class="form_group_ajout_image">
                    <label class="label_ajout_image" for="admin_account_name">Nom complet</label>
                    <input type="text" id="admin_account_name" name="nom" class="input_ajout_image checkout-input" placeholder="Ex. : Aïcha Admin" required>
                </div>

                <div class="form_group_ajout_image">
                    <label class="label_ajout_image" for="admin_account_email">Adresse email</label>
                    <input type="email" id="admin_account_email" name="email" class="input_ajout_image checkout-input" placeholder="admin@ohnous.store" required>
                </div>

                <label class="delivery-toggle">
                    <input type="checkbox" name="auto_password" id="admin_auto_password" value="1" checked>
                    <span>Générer automatiquement le mot de passe</span>
                </label>

                <div class="form_group_ajout_image" id="admin_manual_password_wrapper">
                    <label class="label_ajout_image" for="admin_account_password">Mot de passe manuel</label>
                    <input type="text" id="admin_account_password" name="password" class="input_ajout_image checkout-input" placeholder="Laissez vide si génération automatique">
                </div>

                <button type="submit" class="btn_ohnous">Créer l'admin</button>
            </form>
        </section>

        <section class="liquid-panel admin-account-list-panel">
            <div class="delivery-zone-list-panel__head">
                <h2>Admins existants</h2>
                <span><?= count($admins) ?> compte(s)</span>
            </div>

            <?php if(empty($admins)): ?>
                <div class="empty-liquid-state">
                    <div class="empty-liquid-state__icon"><i class="fa-solid fa-user-shield"></i></div>
                    <p>Aucun compte admin n'est encore disponible.</p>
                </div>
            <?php else: ?>
                <div class="admin-account-list">
                    <?php foreach($admins as $admin): ?>
                        <article class="admin-account-card">
                            <div class="admin-account-card__identity">
                                <img src="<?= htmlspecialchars($admin['profile_resolved'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($admin['display_name'], ENT_QUOTES, 'UTF-8') ?>">
                                <div>
                                    <strong><?= htmlspecialchars($admin['display_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars((string)$admin['email'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                            <small>Créé le <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$admin['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script src="/asset/js/admin_accounts.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_accounts.js") ?>" defer></script>
</div>
