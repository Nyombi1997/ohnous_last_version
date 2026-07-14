<?php
include_once MODEL . 'PayoutTransaction.php';
$reference = trim((string)($_GET['reference'] ?? ''));
$payout = ohnous_table_exists('payout_transactions') ? (new PayoutTransaction($bdd))->findByReference($reference) : null;
if (!$payout) { http_response_code(404); echo '<main class="admin-shell"><p>PayOut introuvable.</p></main>'; return; }
$status = strtolower((string)$payout['status']);
$final = in_array($status, ['success','successful','paid','completed','failed','error','expired','cancelled','canceled','rejected','refused','declined'], true);
?>
<main class="admin-shell admin-detail-page">
    <?= ohnous_render_admin_nav('payouts') ?>
    <section class="admin-detail-card payout-tracking-card" id="payout_tracking" data-reference="<?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?>" data-final="<?= $final ? '1' : '0' ?>">
        <div class="payment-tracking-status">
            <div class="payment-tracking-status__icon <?= $final ? '' : 'is-pending' ?>" id="payout_tracking_icon"><?php if(!$final): ?><span class="payment-tracking-spinner"></span><?php else: ?><i class="fa-solid fa-circle-check"></i><?php endif ?></div>
            <span class="admin-hero__eyebrow">Suivi en temps réel</span>
            <h1 id="payout_tracking_title"><?= $final ? 'PayOut terminé' : 'PayOut en cours' ?></h1>
            <p id="payout_tracking_message"><?= htmlspecialchars($payout['status_description'] ?: 'FreshPay traite votre demande.', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <dl class="admin-detail-list">
            <div><dt>Référence interne</dt><dd><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Montant</dt><dd><?= number_format((float)$payout['amount'], 2, ',', ' ') ?> <?= htmlspecialchars($payout['currency'], ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Statut</dt><dd id="payout_tracking_status"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Transaction</dt><dd id="payout_tracking_transaction"><?= htmlspecialchars($payout['transaction_id'] ?: '—', ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Référence FreshPay</dt><dd id="payout_tracking_freshpay"><?= htmlspecialchars($payout['freshpay_reference'] ?: '—', ENT_QUOTES, 'UTF-8') ?></dd></div>
            <div><dt>Référence opérateur</dt><dd id="payout_tracking_operator"><?= htmlspecialchars($payout['operator_reference'] ?: '—', ENT_QUOTES, 'UTF-8') ?></dd></div>
        </dl>
        <div class="payment-return-card__actions"><a class="btn_ohnous" href="/admin-payout-details?id=<?= (int)$payout['id'] ?>">Voir le détail</a><a class="btn_ohnous second" href="/admin-payouts">Historique</a></div>
    </section>
</main>
<script src="/asset/js/admin_payout_tracking.js" defer></script>
