<?php
include_once MODEL . 'PayoutTransaction.php';
$exists = ohnous_table_exists('payout_transactions');
$model = $exists ? new PayoutTransaction($bdd) : null;
$filters = ['q'=>trim((string)($_GET['q']??'')),'status'=>trim((string)($_GET['status']??'')),'operator'=>trim((string)($_GET['operator']??'')),'date_from'=>trim((string)($_GET['date_from']??'')),'date_to'=>trim((string)($_GET['date_to']??''))];
$rows = $model ? $model->search($filters, 250) : [];
$stats = $model ? $model->statistics() : ['total'=>0,'successful'=>0,'pending'=>0,'failed'=>0,'total_amount'=>0];
$labels = ['pending'=>'En attente','submitted'=>'Soumis','processing'=>'En attente','success'=>'Réussi','successful'=>'Réussi','paid'=>'Réussi','completed'=>'Réussi','failed'=>'Échoué','error'=>'Échoué','expired'=>'Expiré','cancelled'=>'Annulé','canceled'=>'Annulé'];
$operators = ['airtel'=>'Airtel Money','orange'=>'Orange Money','mpesa'=>'M-Pesa','afrimoney'=>'Afrimoney'];
$exportQuery = http_build_query(array_filter($filters, function($value){ return $value !== ''; }));
?>
<main class="admin-shell admin-payment-page">
<section class="admin-hero liquid-panel"><div><span class="admin-hero__eyebrow">Paiements → PayOut</span><h1>Historique des PayOut</h1><p>Rapport complet des transferts Mobile Money FreshPay.</p></div><div class="admin-hero__actions"><a class="btn_ohnous second" href="/payout-export<?= $exportQuery ? '?'.$exportQuery : '' ?>"><i class="fa-solid fa-file-excel"></i> Export Excel</a><a class="btn_ohnous" href="/admin-payout"><i class="fa-solid fa-plus"></i> Effectuer un PayOut</a></div></section>
<?= ohnous_render_admin_nav('payouts') ?>
<?php if(!$exists): ?><div class="admin-notice">Appliquez la migration PayOut documentée dans le README.md.</div><?php else: ?>
<section class="payout-stats-grid">
<article class="admin-detail-card"><span>Total</span><strong><?= (int)($stats['total']??0) ?></strong></article>
<article class="admin-detail-card"><span>Réussis</span><strong><?= (int)($stats['successful']??0) ?></strong></article>
<article class="admin-detail-card"><span>En cours</span><strong><?= (int)($stats['pending']??0) ?></strong></article>
<article class="admin-detail-card"><span>Échoués</span><strong><?= (int)($stats['failed']??0) ?></strong></article>
<article class="admin-detail-card payout-stats-chart"><div id="payout_stats_chart" class="admin-chart"></div></article>
</section>
<form class="admin-search-bar admin-payment-filters liquid-panel" method="get"><input type="search" name="q" value="<?= htmlspecialchars($filters['q'],ENT_QUOTES,'UTF-8') ?>" placeholder="Référence, bénéficiaire ou numéro"><select name="status"><option value="">Tous les statuts</option><?php foreach($labels as $key=>$label): ?><option value="<?= $key ?>" <?= $filters['status']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select><select name="operator"><option value="">Tous les opérateurs</option><?php foreach($operators as $key=>$label): ?><option value="<?= $key ?>" <?= $filters['operator']===$key?'selected':'' ?>><?= $label ?></option><?php endforeach ?></select><input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'],ENT_QUOTES,'UTF-8') ?>"><input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'],ENT_QUOTES,'UTF-8') ?>"><button class="btn_ohnous" type="submit"><i class="fa-solid fa-filter"></i> Filtrer</button></form>
<section class="admin-data-table liquid-panel"><table><thead><tr><th>Référence interne</th><th>Bénéficiaire</th><th>Numéro</th><th>Opérateur</th><th class="is-number">Montant</th><th>Devise</th><th>Statut</th><th>Date</th><th>Réf. FreshPay</th><th>Réf. opérateur</th><th>Administrateur</th><th>Action</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="12" class="admin-table-empty">Aucun PayOut trouvé.</td></tr><?php endif ?>
<?php foreach($rows as $row): $status=strtolower((string)$row['status']); ?><tr><td><strong><?= htmlspecialchars($row['reference'],ENT_QUOTES,'UTF-8') ?></strong></td><td><?= htmlspecialchars($row['beneficiary']?:'—',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($row['phone_number'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($operators[$row['operator']]??$row['operator'],ENT_QUOTES,'UTF-8') ?></td><td class="is-number"><?= number_format((float)$row['amount'],2,',',' ') ?></td><td><?= htmlspecialchars($row['currency'],ENT_QUOTES,'UTF-8') ?></td><td><span class="payment-status payment-status--<?= htmlspecialchars($status,ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($labels[$status]??ucfirst($status),ENT_QUOTES,'UTF-8') ?></span></td><td><?= htmlspecialchars($row['created_at'],ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($row['freshpay_reference']?:'—',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($row['operator_reference']?:'—',ENT_QUOTES,'UTF-8') ?></td><td><?= htmlspecialchars($row['admin_name']?:'—',ENT_QUOTES,'UTF-8') ?></td><td><a class="admin-table-action" href="/admin-payout-details?id=<?= (int)$row['id'] ?>"><i class="fa-solid fa-eye"></i><span>Détail</span></a></td></tr><?php endforeach ?>
</tbody></table></section><?php endif ?></main>
<?php if($exists): ?><script>window.ohnousPayoutStats=<?= json_encode(['successful'=>(int)($stats['successful']??0),'pending'=>(int)($stats['pending']??0),'failed'=>(int)($stats['failed']??0)],JSON_UNESCAPED_UNICODE) ?>;</script><script src="/node_modules/apexcharts/dist/apexcharts.min.js"></script><script src="/asset/js/admin_payouts.js" defer></script><?php endif ?>
