<?php
include_once MODEL . 'PaymentTransaction.php';
$model = new PaymentTransaction($bdd);
$filters = [
    'q' => trim((string)($_GET['q'] ?? '')), 'status' => trim((string)($_GET['status'] ?? '')),
    'payment_method' => trim((string)($_GET['payment_method'] ?? '')), 'date_from' => trim((string)($_GET['date_from'] ?? '')),
    'date_to' => trim((string)($_GET['date_to'] ?? '')),
];
$payments = $model->search($filters, 100);
$labels = ['pending'=>'En attente','submitted'=>'Soumis','processing'=>'En cours','success'=>'Réussi','successful'=>'Réussi','paid'=>'Réussi','completed'=>'Réussi','failed'=>'Échoué','error'=>'Échoué','expired'=>'Expiré','cancelled'=>'Annulé','canceled'=>'Annulé','refunded'=>'Remboursé'];
$statusLabel = function ($value) use ($labels) { $key = strtolower(trim((string)$value)); return $labels[$key] ?? ucfirst($key ?: 'En attente'); };
$money = function ($value, $currency) { return number_format((float)$value, 2, ',', ' ') . ' ' . htmlspecialchars((string)$currency, ENT_QUOTES, 'UTF-8'); };
?>
<main class="admin-shell admin-payment-page">
    <section class="admin-hero liquid-panel"><div><span class="admin-hero__eyebrow">Paiements</span><h1>Rapport des paiements</h1><p>Suivez les encaissements et leurs statuts FreshPay.</p></div></section>
    <?= ohnous_render_admin_nav('paiements') ?>
    <form class="admin-search-bar admin-payment-filters liquid-panel" method="get" action="/admin-paiements">
        <input type="search" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Référence, commande ou client">
        <select name="status"><option value="">Tous les statuts</option><?php foreach(['pending'=>'En attente','submitted'=>'Soumis','processing'=>'En cours','success'=>'Réussi','failed'=>'Échoué','expired'=>'Expiré','cancelled'=>'Annulé','refunded'=>'Remboursé'] as $key=>$label): ?><option value="<?= $key ?>" <?= $filters['status']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select>
        <select name="payment_method"><option value="">Tous les modes</option><option value="mobile_money" <?= $filters['payment_method']==='mobile_money'?'selected':'' ?>>Mobile Money</option><option value="visa" <?= $filters['payment_method']==='visa'?'selected':'' ?>>Visa</option></select>
        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'], ENT_QUOTES, 'UTF-8') ?>">
        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'], ENT_QUOTES, 'UTF-8') ?>">
        <button class="btn_ohnous" type="submit"><i class="fa-solid fa-filter"></i> Filtrer</button>
    </form>
    <section class="admin-data-table liquid-panel">
        <table><thead><tr><th>Références</th><th>Commande</th><th>Client</th><th>Boutique</th><th>Mode</th><th class="is-number">Montant HT</th><th class="is-number">TTC (10 %)</th><th class="is-number">Total</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody><?php if(!$payments): ?><tr><td colspan="11" class="admin-table-empty">Aucun paiement trouvé.</td></tr><?php endif ?>
        <?php foreach($payments as $payment): $ht=(float)($payment['amount_ht'] ?? ((float)$payment['sous_total']+(float)$payment['livraison_prix'])); $fee=(float)($payment['payment_fee_amount_resolved'] ?? 0); $status=strtolower((string)$payment['trans_status']); ?>
            <tr>
                <td data-label="Références"><strong><?= htmlspecialchars((string)$payment['reference'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string)($payment['provider_reference_resolved'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></small></td>
                <td data-label="Commande"><?= htmlspecialchars((string)($payment['order_number'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Client"><strong><?= htmlspecialchars((string)($payment['nom_client'] ?: 'Client invité'), ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string)($payment['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small></td>
                <td data-label="Boutique"><?= htmlspecialchars((string)($payment['boutiques'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Mode"><?= $payment['payment_method']==='mobile_money'?'Mobile Money':htmlspecialchars((string)$payment['payment_method'], ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Montant HT" class="is-number"><?= $money($ht,$payment['currency']) ?></td><td data-label="TTC (10 %)" class="is-number"><?= $money($fee,$payment['currency']) ?></td><td data-label="Total" class="is-number"><strong><?= $money($payment['amount'],$payment['currency']) ?></strong></td>
                <td data-label="Statut"><span class="payment-status payment-status--<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabel($status), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td data-label="Date"><time><?= htmlspecialchars((string)$payment['created_at'], ENT_QUOTES, 'UTF-8') ?></time></td>
                <td data-label="Actions"><a class="admin-table-action" href="/admin-paiement-details?id=<?= (int)$payment['id'] ?>"><i class="fa-solid fa-eye"></i><span>Voir les détails</span></a></td>
            </tr>
        <?php endforeach ?></tbody></table>
    </section>
</main>
