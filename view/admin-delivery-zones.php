<?php
    ohnous_require_admin_or_redirect();

    $settings = ohnous_get_delivery_settings();
    $zones = ohnous_get_delivery_zones(false);
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>Zones de livraison</h1>
            <p>Créez vos zones, définissez leur prix ou activez un tarif unique pour toutes les zones.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('livraison') ?>

    <?php if(!ohnous_table_exists('delivery_zones') || !ohnous_table_exists('delivery_settings')): ?>
        <div class="liquid-panel checkout-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <p>Les tables SQL de livraison sont manquantes. Appliquez le SQL ajouté dans le README.md pour activer cette page.</p>
        </div>
    <?php else: ?>
        <div class="delivery-admin-grid">
            <section class="liquid-panel delivery-settings-panel">
                <h2>Configuration générale</h2>
                <form id="delivery_settings_form" class="delivery-settings-form">
                    <label class="delivery-toggle">
                        <input type="checkbox" name="use_global_price" value="1" <?= (int)$settings['use_global_price'] === 1 ? 'checked' : '' ?>>
                        <span>Utiliser un même prix pour toutes les zones</span>
                    </label>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="global_delivery_price">Prix global de livraison</label>
                        <input type="number" step="0.01" min="0" id="global_delivery_price" name="global_price" class="input_ajout_image checkout-input" value="<?= htmlspecialchars(number_format((float)$settings['global_price'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <button type="submit" class="btn_ohnous">Enregistrer la configuration</button>
                </form>
            </section>

            <section class="liquid-panel delivery-zone-form-panel">
                <h2>Ajouter ou modifier une zone</h2>
                <form id="delivery_zone_form" class="delivery-zone-form">
                    <input type="hidden" name="zone_id" id="delivery_zone_id" value="0">

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="delivery_zone_name">Nom de la zone</label>
                        <input type="text" id="delivery_zone_name" name="nom" class="input_ajout_image checkout-input" placeholder="Ex. : Paris intra-muros" required>
                    </div>

                    <div class="form_group_ajout_image">
                        <label class="label_ajout_image" for="delivery_zone_price">Prix de livraison</label>
                        <input type="number" step="0.01" min="0" id="delivery_zone_price" name="prix" class="input_ajout_image checkout-input" placeholder="0.00" required>
                    </div>

                    <label class="delivery-toggle">
                        <input type="checkbox" name="actif" id="delivery_zone_active" value="1" checked>
                        <span>Zone active</span>
                    </label>

                    <div class="delivery-zone-form__actions">
                        <button type="submit" class="btn_ohnous">Enregistrer la zone</button>
                        <button type="button" class="btn_ohnous second" id="reset_delivery_zone_form">Réinitialiser</button>
                    </div>
                </form>
            </section>
        </div>

        <section class="liquid-panel delivery-zone-list-panel">
            <div class="delivery-zone-list-panel__head">
                <h2>Zones enregistrées</h2>
                <span><?= count($zones) ?> zone(s)</span>
            </div>

            <?php if(empty($zones)): ?>
                <div class="empty-liquid-state">
                    <div class="empty-liquid-state__icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <p>Aucune zone de livraison n'est encore enregistrée.</p>
                </div>
            <?php else: ?>
                <div class="delivery-zone-list">
                    <?php foreach($zones as $zone): ?>
                        <article class="delivery-zone-card <?= (int)$zone['actif'] === 1 ? 'is-active' : 'is-inactive' ?>">
                            <div>
                                <strong><?= htmlspecialchars($zone['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span>$ <?= number_format((float)$zone['prix'], 2, '.', ' ') ?></span>
                            </div>
                            <div class="delivery-zone-card__actions">
                                <button
                                    type="button"
                                    class="btn_ohnous second js-edit-delivery-zone"
                                    data-zone-id="<?= (int)$zone['id'] ?>"
                                    data-zone-name="<?= htmlspecialchars($zone['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-zone-price="<?= htmlspecialchars(number_format((float)$zone['prix'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>"
                                    data-zone-active="<?= (int)$zone['actif'] ?>"
                                >Modifier</button>
                                <button
                                    type="button"
                                    class="btn_ohnous <?= (int)$zone['actif'] === 1 ? 'second' : '' ?> js-toggle-delivery-zone"
                                    data-zone-id="<?= (int)$zone['id'] ?>"
                                ><?= (int)$zone['actif'] === 1 ? 'Désactiver' : 'Activer' ?></button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <script src="/asset/js/admin_delivery_zones.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_delivery_zones.js") ?>" defer></script>
    <?php endif; ?>
</div>
