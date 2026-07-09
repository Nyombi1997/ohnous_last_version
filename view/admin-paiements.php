<?php
    ohnous_require_admin_or_redirect();
    include_once MODEL . 'PaymentTransaction.php';

    $paymentModel = new PaymentTransaction($bdd);
    $filters = [
        'q' => trim((string)($_GET['q'] ?? '')),
        'date_from' => trim((string)($_GET['date_from'] ?? '')),
        'date_to' => trim((string)($_GET['date_to'] ?? '')),
        'status' => trim((string)($_GET['status'] ?? '')),
        'client' => trim((string)($_GET['client'] ?? '')),
        'boutique_id' => (int)($_GET['boutique_id'] ?? 0),
        'payment_method' => trim((string)($_GET['payment_method'] ?? '')),
    ];
    $payments = $paymentModel->search($filters, isset($_GET['export']) ? null : 100);
    $selectedPayment = !empty($_GET['id']) ? $paymentModel->findDetailed((int)$_GET['id']) : null;
    $stores = select_bdd($bdd, 'boutiques', null, null, 0, 'nom ASC', false);

    $statusLabels = [
        'pending' => 'En attente',
        'submitted' => 'En attente',
        'success' => 'Réussi',
        'successful' => 'Réussi',
        'paid' => 'Réussi',
        'completed' => 'Réussi',
        'failed' => 'Échoué',
        'error' => 'Échoué',
        'rejected' => 'Échoué',
        'refused' => 'Échoué',
        'cancelled' => 'Annulé',
        'canceled' => 'Annulé',
        'refunded' => 'Remboursé',
    ];
    $formatMoney = function ($amount, $currency = 'USD') {
        return number_format((float)$amount, 2, '.', ' ') . ' ' . htmlspecialchars((string)$currency, ENT_QUOTES, 'UTF-8');
    };
    $statusLabel = function ($status) use ($statusLabels) {
        $key = strtolower((string)$status);
        return $statusLabels[$key] ?? ucfirst($key);
    };

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="paiements-ohnous-'.date('Ymd-His').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Reference interne', 'Reference prestataire', 'Mode', 'Statut', 'Date', 'Montant HT', 'Frais 10%', 'Total paye', 'Client', 'Telephone', 'Boutiques', 'Commande'], ';');
        foreach ($payments as $payment) {
            fputcsv($out, [
                $payment['reference'] ?? '',
                $payment['provider_reference_resolved'] ?? '',
                $payment['payment_method'] ?? '',
                $statusLabel($payment['trans_status'] ?? ''),
                $payment['created_at'] ?? '',
                (float)($payment['amount_ht'] ?? ((float)($payment['sous_total'] ?? 0) + (float)($payment['livraison_prix'] ?? 0))),
                (float)($payment['payment_fee_amount_resolved'] ?? 0),
                (float)($payment['amount'] ?? 0),
                $payment['nom_client'] ?? '',
                $payment['telephone'] ?? '',
                $payment['boutiques'] ?? '',
                $payment['order_number'] ?? '',
            ], ';');
        }
        fclose($out);
        exit;
    }
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <span class="admin-hero__eyebrow">Paiements</span>
            <h1>Historique des paiements</h1>
            <p>Recherchez, filtrez et consultez les transactions OHNOUS.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('paiements') ?>

    <form class="admin-search-bar admin-payment-filters liquid-panel" method="get" action="/admin-paiements">
        <input type="search" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Référence, transaction, client">
        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="search" name="client" value="<?= htmlspecialchars($filters['client'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Client ou téléphone">
        <select name="status">
            <option value="">Tous les statuts</option>
            <?php foreach(['pending' => 'En attente', 'success' => 'Réussi', 'failed' => 'Échoué', 'cancelled' => 'Annulé', 'refunded' => 'Remboursé'] as $key => $label): ?>
                <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <select name="boutique_id">
            <option value="0">Toutes les boutiques</option>
            <?php foreach($stores as $store): ?>
                <option value="<?= (int)$store['id'] ?>" <?= (int)$filters['boutique_id'] === (int)$store['id'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$store['nom'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <select name="payment_method">
            <option value="">Tous les modes</option>
            <option value="mobile_money" <?= $filters['payment_method'] === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
            <option value="visa" <?= $filters['payment_method'] === 'visa' ? 'selected' : '' ?>>Visa</option>
        </select>
        <button type="submit" class="btn_ohnous">Filtrer</button>
        <a class="btn_ohnous second" href="/admin-paiements?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['export' => 'csv'])), ENT_QUOTES, 'UTF-8') ?>">Exporter</a>
    </form>

    <section class="admin-payment-layout">
        <div class="admin-article-table liquid-panel">
            <div class="admin-article-table__head admin-payment-row">
                <span>Paiement</span>
                <span>Client</span>
                <span>Montants</span>
                <span>Statut</span>
                <span></span>
            </div>
            <?php if(empty($payments)): ?>
                <div class="empty-liquid-state">
                    <div class="empty-liquid-state__icon"><i class="fa-solid fa-credit-card"></i></div>
                    <p>Aucun paiement trouvé.</p>
                </div>
            <?php endif; ?>
            <?php foreach($payments as $payment): ?>
                <?php
                    $amountHt = (float)($payment['amount_ht'] ?? ((float)($payment['sous_total'] ?? 0) + (float)($payment['livraison_prix'] ?? 0)));
                    $fee = (float)($payment['payment_fee_amount_resolved'] ?? 0);
                ?>
                <article class="admin-article-table__row admin-payment-row">
                    <div>
                        <strong><?= htmlspecialchars((string)$payment['reference'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small><?= htmlspecialchars((string)($payment['provider_reference_resolved'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                        <small><?= htmlspecialchars((string)$payment['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars((string)($payment['nom_client'] ?: 'Client invité'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <small><?= htmlspecialchars((string)($payment['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <div>
                        <small>HT : <?= $formatMoney($amountHt, $payment['currency'] ?? 'USD') ?></small>
                        <small>10 % : <?= $formatMoney($fee, $payment['currency'] ?? 'USD') ?></small>
                        <strong><?= $formatMoney($payment['amount'] ?? 0, $payment['currency'] ?? 'USD') ?></strong>
                    </div>
                    <div>
                        <span class="admin-payment-status"><?= htmlspecialchars($statusLabel($payment['trans_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <small><?= htmlspecialchars((string)($payment['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                    <a class="admin-article-edit-link" href="/admin-paiements?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['id' => (int)$payment['id']])), ENT_QUOTES, 'UTF-8') ?>">Détail</a>
                </article>
            <?php endforeach; ?>
        </div>

        <aside class="admin-payment-detail liquid-panel">
            <?php if(!$selectedPayment): ?>
                <div class="empty-liquid-state compact">
                    <div class="empty-liquid-state__icon"><i class="fa-solid fa-receipt"></i></div>
                    <p>Sélectionnez un paiement pour voir le détail.</p>
                </div>
            <?php else: ?>
                <?php
                    $amountHt = (float)($selectedPayment['amount_ht'] ?? ((float)($selectedPayment['sous_total'] ?? 0) + (float)($selectedPayment['livraison_prix'] ?? 0)));
                    $fee = (float)($selectedPayment['payment_fee_amount_resolved'] ?? 0);
                    $profile = $selectedPayment['client_profile'] ?? null;
                ?>
                <h2><?= htmlspecialchars((string)$selectedPayment['reference'], ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="admin-payment-detail__grid">
                    <span>Référence prestataire</span><strong><?= htmlspecialchars((string)($selectedPayment['provider_reference_resolved'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Transaction</span><strong><?= htmlspecialchars((string)($selectedPayment['transaction_number_resolved'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Commande</span><strong><?= htmlspecialchars((string)($selectedPayment['order_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Mode</span><strong><?= htmlspecialchars((string)($selectedPayment['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Statut</span><strong><?= htmlspecialchars($statusLabel($selectedPayment['trans_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Date</span><strong><?= htmlspecialchars((string)($selectedPayment['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <span>Montant HT</span><strong><?= $formatMoney($amountHt, $selectedPayment['currency'] ?? 'USD') ?></strong>
                    <span>TVA / Frais 10 %</span><strong><?= $formatMoney($fee, $selectedPayment['currency'] ?? 'USD') ?></strong>
                    <span>Total payé</span><strong><?= $formatMoney($selectedPayment['amount'] ?? 0, $selectedPayment['currency'] ?? 'USD') ?></strong>
                </div>
                <div class="admin-payment-detail__block">
                    <h3>Client</h3>
                    <?php if($profile): ?>
                        <a href="<?= $selectedPayment['client_type'] === 'boutique' ? '/boutique/'.htmlspecialchars((string)$profile['slug'], ENT_QUOTES, 'UTF-8') : '/utilisateur/'.htmlspecialchars((string)$profile['slug'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string)$profile['nom'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php else: ?>
                        <strong><?= htmlspecialchars((string)($selectedPayment['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php endif; ?>
                    <small><?= htmlspecialchars((string)($selectedPayment['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                </div>
                <div class="admin-payment-detail__block">
                    <h3>Articles</h3>
                    <?php foreach(($selectedPayment['articles'] ?? []) as $item): ?>
                        <div class="admin-payment-item">
                            <strong><?= htmlspecialchars((string)$item['article_nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span>Quantité : <?= (int)$item['quantite'] ?></span>
                            <span>Boutique : <?= htmlspecialchars((string)($item['boutique_nom'] ?? 'Boutique inconnue'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="admin-payment-detail__block">
                    <h3>Commande</h3>
                    <p><?= nl2br(htmlspecialchars((string)($selectedPayment['adresse'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                    <small><?= htmlspecialchars((string)($selectedPayment['zone_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                </div>
            <?php endif; ?>
        </aside>
    </section>
</div>
