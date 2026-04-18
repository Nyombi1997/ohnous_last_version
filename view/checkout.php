<?php
    $mode = (isset($_GET['mode']) && $_GET['mode'] === 'direct') ? 'direct' : 'cart';
    $checkoutContext = ohnous_get_checkout_context($mode);
    $deliveryZones = ohnous_get_delivery_zones(true);
    $deliverySettings = ohnous_get_delivery_settings();
    $paymentConfig = include CONFIG . 'payment.php';
    $visaEnabled = !empty($paymentConfig['freshpay']['visa']['enabled']);
    $checkoutSuccess = isset($_GET['success']) && (int)$_GET['success'] === 1;
    $orderNumber = trim((string)($_GET['order'] ?? ''));
?>
<div class="content_page">
    <section class="checkout-shell">
        <div class="checkout-main liquid-panel">
            <div class="checkout-main__head">
                <div>
                    <span class="checkout-eyebrow"><?= $mode === 'direct' ? 'Commande directe' : 'Checkout panier' ?></span>
                    <h1>Finaliser votre commande</h1>
                    <p>
                        <?= $mode === 'direct'
                            ? "Vous commandez uniquement l'article sélectionné depuis la vue article."
                            : "Vous commandez les articles actuellement présents dans votre panier." ?>
                    </p>
                </div>
                <a href="/articles" class="btn_ohnous second">Continuer vos achats</a>
            </div>

            <?php if($checkoutSuccess): ?>
                <div class="checkout-success-state">
                    <div class="checkout-success-state__icon"><i class="fa-solid fa-badge-check"></i></div>
                    <div>
                        <strong>Commande enregistrée avec succès.</strong>
                        <p>Référence : <?= htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(empty($checkoutContext['items'])): ?>
                <div class="empty-liquid-state">
                    <div class="empty-liquid-state__icon"><i class="fa-solid fa-bag-shopping"></i></div>
                    <p>Aucun article n'est disponible pour ce checkout.</p>
                </div>
            <?php else: ?>
                <?php if(empty($deliveryZones)): ?>
                    <div class="checkout-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <p>Aucune zone de livraison active n'est encore configurée. Ajoutez vos zones dans l'espace admin avant de valider une commande.</p>
                    </div>
                <?php endif; ?>

                <form id="checkout_form" class="checkout-form">
                    <input type="hidden" name="mode" value="<?= htmlspecialchars($checkoutContext['mode'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="checkout-form__grid">
                        <div class="checkout-form__fields">
                            <div class="checkout-form__row">
                                <div class="form_group_ajout_image">
                                    <label class="label_ajout_image" for="checkout_firstname">Prénom</label>
                                    <input type="text" id="checkout_firstname" name="firstname" class="input_ajout_image checkout-input" placeholder="Votre prénom" required>
                                </div>

                                <div class="form_group_ajout_image">
                                    <label class="label_ajout_image" for="checkout_lastname">Nom</label>
                                    <input type="text" id="checkout_lastname" name="lastname" class="input_ajout_image checkout-input" placeholder="Votre nom" required>
                                </div>
                            </div>

                            <div class="form_group_ajout_image">
                                <label class="label_ajout_image" for="checkout_telephone">Numéro de téléphone</label>
                                <input type="tel" id="checkout_telephone" name="telephone" class="input_ajout_image checkout-input" placeholder="Ex. : +243 900 000 000" inputmode="tel" required>
                            </div>

                            <div class="form_group_ajout_image">
                                <label class="label_ajout_image" for="checkout_email">Adresse email</label>
                                <input type="email" id="checkout_email" name="email" class="input_ajout_image checkout-input" placeholder="client@exemple.com" required>
                            </div>

                            <div class="form_group_ajout_image">
                                <label class="label_ajout_image" for="checkout_address">Adresse de livraison</label>
                                <textarea id="checkout_address" name="adresse" rows="3" class="input_ajout_image checkout-input checkout-textarea" placeholder="Renseignez l'adresse complète de livraison." required></textarea>
                            </div>

                            <div class="form_group_ajout_image">
                                <label class="label_ajout_image" for="checkout_zone">Zone de livraison</label>
                                <select id="checkout_zone" name="zone_id" class="input_ajout_image checkout-input" required>
                                    <option value="">Choisir une zone</option>
                                    <?php foreach($deliveryZones as $zone): ?>
                                        <?php
                                            $zonePrice = (int)$deliverySettings['use_global_price'] === 1
                                                ? (float)$deliverySettings['global_price']
                                                : (float)($zone['prix'] ?? 0);
                                        ?>
                                        <option value="<?= (int)$zone['id'] ?>" data-price="<?= htmlspecialchars(number_format($zonePrice, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($zone['nom'], ENT_QUOTES, 'UTF-8') ?> - $ <?= number_format($zonePrice, 2, '.', ' ') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <section class="checkout-payment-panel liquid-panel">
                                <div class="checkout-payment-panel__head">
                                    <h2>Méthode de paiement</h2>
                                    <span>Devise : USD</span>
                                </div>

                                <div class="checkout-payment-methods">
                                    <label class="checkout-payment-method is-active">
                                        <input type="radio" name="payment_method" value="mobile_money" checked>
                                        <div>
                                            <strong>Mobile Money</strong>
                                            <span>Paiement asynchrone avec confirmation FreshPay.</span>
                                        </div>
                                    </label>

                                    <label class="checkout-payment-method <?= $visaEnabled ? '' : 'is-disabled' ?>">
                                        <input type="radio" name="payment_method" value="visa" <?= $visaEnabled ? '' : 'disabled' ?>>
                                        <div>
                                            <strong>Carte Visa</strong>
                                            <span><?= $visaEnabled ? 'Configuration backend prête.' : 'Bientôt disponible.' ?></span>
                                        </div>
                                        <?php if(!$visaEnabled): ?>
                                            <em class="checkout-badge-soon">Bientôt disponible</em>
                                        <?php endif; ?>
                                    </label>
                                </div>

                                <div id="mobile_money_fields" class="checkout-method-fields">
                                    <div class="checkout-form__row">
                                        <div class="form_group_ajout_image">
                                            <label class="label_ajout_image" for="checkout_operator">Opérateur</label>
                                            <select id="checkout_operator" name="payment_operator" class="input_ajout_image checkout-input">
                                                <option value="">Choisir un opérateur</option>
                                                <option value="airtel">Airtel Money</option>
                                                <option value="orange">Orange Money</option>
                                                <option value="mpesa">M-Pesa</option>
                                                <option value="afrimoney">Afrimoney</option>
                                            </select>
                                        </div>

                                        <div class="form_group_ajout_image">
                                            <label class="label_ajout_image" for="checkout_customer_number">Numéro Mobile Money</label>
                                            <input type="tel" id="checkout_customer_number" name="customer_number" class="input_ajout_image checkout-input" placeholder="Numéro à débiter" inputmode="tel">
                                        </div>
                                    </div>
                                </div>

                                <div id="checkout_payment_feedback" class="checkout-payment-feedback" aria-live="polite">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <p>Le montant final inclut le sous-total du panier et les frais de livraison en USD.</p>
                                </div>
                            </section>
                        </div>

                        <aside class="checkout-summary liquid-panel">
                            <div class="checkout-summary__head">
                                <h2>Résumé</h2>
                                <span><?= (int)$checkoutContext['count'] ?> article(s)</span>
                            </div>

                            <div class="checkout-summary__items">
                                <?php foreach($checkoutContext['items'] as $item): ?>
                                    <?= ohnous_render_checkout_item_html($item, true) ?>
                                <?php endforeach; ?>
                            </div>

                            <div class="checkout-summary__totals">
                                <div class="checkout-summary__line">
                                    <span>Sous-total</span>
                                    <strong>$ <span id="checkout_subtotal"><?= number_format($checkoutContext['subtotal'], 2, '.', ' ') ?></span></strong>
                                </div>
                                <div class="checkout-summary__line">
                                    <span>Livraison</span>
                                    <strong>$ <span id="checkout_delivery_price">0.00</span></strong>
                                </div>
                                <div class="checkout-summary__line total">
                                    <span>Total</span>
                                    <strong>$ <span id="checkout_total"><?= number_format($checkoutContext['subtotal'], 2, '.', ' ') ?></span></strong>
                                </div>
                            </div>

                            <button type="submit" class="btn_ohnous checkout-submit" <?= empty($deliveryZones) ? 'disabled' : '' ?>>Payer maintenant</button>
                        </aside>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if(!empty($checkoutContext['items'])): ?>
    <script>
        window.ohnousCheckoutConfig = {
            subtotal: <?= json_encode((float)$checkoutContext['subtotal']) ?>,
            mode: <?= json_encode($checkoutContext['mode']) ?>,
            visaEnabled: <?= json_encode($visaEnabled) ?>
        };
    </script>
    <script src="/asset/js/checkout.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/checkout.js") ?>" defer></script>
<?php endif; ?>
