<?php
include_once MODEL . 'PaymentTransaction.php';
$payment = (new PaymentTransaction($bdd))->findDetailed((int)($_GET['id'] ?? 0));
if (!$payment) { http_response_code(404); echo '<main class="admin-shell"><p>Paiement introuvable.</p></main>'; return; }
$labels=['pending'=>'En attente','submitted'=>'Soumis','processing'=>'En cours','success'=>'Réussi','successful'=>'Réussi','paid'=>'Réussi','completed'=>'Réussi','failed'=>'Échoué','error'=>'Échoué','expired'=>'Expiré','cancelled'=>'Annulé','canceled'=>'Annulé','refunded'=>'Remboursé'];
$statusKey=strtolower((string)($payment['trans_status'] ?? 'pending')); $statusLabel=$labels[$statusKey] ?? ucfirst($statusKey);
$ht=(float)($payment['amount_ht'] ?? ((float)$payment['sous_total']+(float)$payment['livraison_prix'])); $fee=(float)($payment['payment_fee_amount_resolved'] ?? 0); $total=(float)$payment['amount'];
$money=function($value)use($payment){return number_format((float)$value,2,',',' ').' '.htmlspecialchars((string)$payment['currency'],ENT_QUOTES,'UTF-8');};
$response=json_decode((string)($payment['response_payload'] ?? ''),true) ?: [];
$requestPayload=json_decode((string)($payment['request_payload'] ?? ''),true) ?: [];
$operator=$response['method'] ?? $response['Method'] ?? $requestPayload['method'] ?? '';
?>
<main class="admin-shell admin-detail-page">
    <a class="admin-back-link" href="/admin-paiements"><i class="fa-solid fa-arrow-left"></i> Retour au rapport des paiements</a>
    <section class="admin-detail-heading"><div><span class="admin-hero__eyebrow">Paiement</span><h1><?= htmlspecialchars((string)$payment['reference'],ENT_QUOTES,'UTF-8') ?></h1></div><span class="payment-status payment-status--<?= htmlspecialchars($statusKey,ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($statusLabel,ENT_QUOTES,'UTF-8') ?></span></section>
    <div class="admin-detail-grid">
        <section class="admin-detail-card"><h2>Informations générales</h2><dl class="admin-detail-list">
            <div><dt>Référence interne</dt><dd><?= htmlspecialchars((string)$payment['reference'],ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Référence prestataire</dt><dd><?= htmlspecialchars((string)($payment['provider_reference_resolved'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div>
            <div><dt>Transaction</dt><dd><?= htmlspecialchars((string)($payment['transaction_number_resolved'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Commande</dt><dd><?= htmlspecialchars((string)($payment['order_number'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div>
            <div><dt>Client</dt><dd><?= htmlspecialchars((string)($payment['nom_client'] ?: 'Client invité'),ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Boutique</dt><dd><?= htmlspecialchars((string)($payment['boutiques'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div>
            <div><dt>Mode de paiement</dt><dd><?= $payment['payment_method']==='mobile_money'?'Mobile Money':htmlspecialchars((string)$payment['payment_method'],ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Date</dt><dd><?= htmlspecialchars((string)$payment['created_at'],ENT_QUOTES,'UTF-8') ?></dd></div>
        </dl></section>
        <section class="admin-detail-card"><h2>Informations financières</h2><dl class="admin-detail-list"><div><dt>Montant HT</dt><dd><?= $money($ht) ?></dd></div><div><dt>Supplément 10 %</dt><dd><?= $money($fee) ?></dd></div><div><dt>Montant TTC</dt><dd><?= $money($total) ?></dd></div><div><dt>Devise</dt><dd><?= htmlspecialchars((string)$payment['currency'],ENT_QUOTES,'UTF-8') ?></dd></div></dl><div id="payment_amount_chart" class="admin-chart"></div></section>
        <section class="admin-detail-card"><h2>Informations Mobile Money</h2><dl class="admin-detail-list"><div><dt>Opérateur</dt><dd><?= htmlspecialchars((string)($operator ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Numéro utilisé</dt><dd><?= htmlspecialchars((string)($payment['customer_number'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Référence opérateur</dt><dd><?= htmlspecialchars((string)($payment['financial_institution_id'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div><div><dt>Message FreshPay</dt><dd><?= htmlspecialchars((string)($payment['trans_status_description'] ?: '—'),ENT_QUOTES,'UTF-8') ?></dd></div></dl></section>
        <section class="admin-detail-card"><h2>Suivi de la transaction</h2><div id="payment_status_chart" class="admin-chart"></div><ol class="transaction-timeline"><li><strong>Paiement créé</strong><span><?= htmlspecialchars((string)$payment['created_at'],ENT_QUOTES,'UTF-8') ?></span></li><li><strong><?= htmlspecialchars($statusLabel,ENT_QUOTES,'UTF-8') ?></strong><span><?= htmlspecialchars((string)$payment['updated_at'],ENT_QUOTES,'UTF-8') ?></span></li></ol></section>
    </div>
    <section class="admin-detail-card admin-detail-card--wide"><h2>Articles achetés</h2><div class="payment-items-grid"><?php foreach($payment['articles'] as $item): ?><article class="payment-item-card"><img src="<?= htmlspecialchars((string)($item['image'] ?: '/asset/images/profile/default.jpg'),ENT_QUOTES,'UTF-8') ?>" alt=""><div><h3><?= htmlspecialchars((string)$item['article_nom'],ENT_QUOTES,'UTF-8') ?></h3><p><?= (int)$item['quantite'] ?> × <?= number_format((float)$item['prix_unitaire'],2,',',' ') ?> <?= htmlspecialchars((string)$payment['currency'],ENT_QUOTES,'UTF-8') ?></p><span><?= htmlspecialchars((string)($item['boutique_nom'] ?: 'Boutique'),ENT_QUOTES,'UTF-8') ?></span></div></article><?php endforeach ?></div></section>
</main>
<script>window.ohnousPaymentDetail=<?= json_encode(['ht'=>$ht,'fee'=>$fee,'total'=>$total,'status'=>$statusLabel,'created'=>$payment['created_at'],'updated'=>$payment['updated_at']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="/node_modules/apexcharts/dist/apexcharts.min.js"></script><script src="/asset/js/admin_payment_detail.js" defer></script>
