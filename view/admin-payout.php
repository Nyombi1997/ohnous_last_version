<?php
include_once MODEL . 'PayoutTransaction.php';
require_once FONCTION . 'moko_recipient.php';
$payoutBoutiques = (new PayoutTransaction($bdd))->boutiques();
$payoutRecipients = [];
try { $payoutRecipients = (new PayoutTransaction($bdd))->recipients(); } catch (PDOException $e) {}
$_SESSION['admin_payout_csrf_at'] = time();
?>
<main class="admin-shell admin-detail-page admin-page-shell admin-payout-page">
    <a class="admin-back-link" href="/admin-payouts"><i class="fa-solid fa-arrow-left"></i> Retour à l’historique des PayOut</a>
    <?= ohnous_render_admin_nav('payouts') ?>
    <section class="admin-detail-card moko-diagnostic">
        <div class="admin-detail-heading"><h2>Portefeuille Moko</h2><button type="button" class="btn_ohnous" id="moko_diagnostic"><i class="fa-solid fa-plug"></i> Tester la connexion et actualiser le solde</button></div>
        <p id="moko_connection_status" role="status" aria-live="polite"></p>
        <dl id="moko_balances" class="admin-detail-list"></dl>
        <details id="moko_connection_details" hidden><summary>Détail technique</summary><pre class="payout-json"></pre></details>
    </section>
    <section class="admin-detail-card payout-form-card"><div class="admin-detail-heading"><div><span class="admin-hero__eyebrow">Moko</span><h1>Nouveau PayOut</h1></div></div>
        <p>Sélectionnez la boutique et enregistrez son bénéficiaire avant le versement.</p>
        <form id="admin_payout_form" class="admin-form-grid">
            <?php renderHoneypot('moko_recipient'); ?>
            <input type="hidden" name="recipient_csrf" value="<?= htmlspecialchars(ohnous_recipient_csrf(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="admin-field admin-field--wide"><span>Boutique</span><select name="boutique_id" id="payout_boutique" required><option value="">Choisir une boutique</option><?php foreach ($payoutBoutiques as $boutique): ?><option value="<?= (int)$boutique['id'] ?>" <?= (int)($_GET['boutique_id'] ?? 0) === (int)$boutique['id'] ? 'selected' : '' ?>><?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach ?></select></label>
            <div class="admin-field--wide"><a id="payout_boutique_report" class="admin-back-link" href="/admin-payouts" hidden>Rapport des payouts de cette boutique</a></div>
            <?php include VIEW.'composants/recipient-fields.php'; ?>
            <label class="admin-field"><span>Montant</span><input type="number" name="amount" min="0.01" step="0.01" required></label>
            <details class="admin-field--wide" id="moko_recipient_details" hidden><summary>Détail technique du bénéficiaire</summary><pre class="payout-json"></pre></details>
            <label class="admin-field"><span>Devise</span><select name="currency" required><option value="USD">USD</option><option value="CDF">CDF</option></select></label>
            <label class="admin-field"><span>Référence unique de l’opération</span><input type="text" name="reference" minlength="4" maxlength="120" pattern="[A-Za-z0-9._-]{4,120}" placeholder="ohnous-vendor-42-settlement-123" required></label>
            <label class="admin-field admin-field--wide"><span>Motif</span><textarea name="reason" rows="4" maxlength="255" required></textarea></label>
            <div class="admin-form-actions"><button class="btn_ohnous" type="submit" disabled><i class="fa-solid fa-paper-plane"></i> Effectuer le PayOut</button></div>
        </form>
    </section>
    <section class="admin-detail-card"><h2>Bénéficiaires</h2><div class="admin-data-table"><table><thead><tr><th>Boutique</th><th>Nom</th><th>Téléphone</th><th>Opérateur</th><th>Identifiant OHNOUS</th><th>recipient_id Moko</th><th>Statut</th><th>Dernière synchronisation</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($payoutRecipients as $item): ?><tr>
        <?php foreach (['boutique'=>'Boutique','beneficiary'=>'Nom','phone_number'=>'Téléphone','operator'=>'Opérateur','merchant_recipient_id'=>'Identifiant OHNOUS','recipient_id'=>'recipient_id Moko','status'=>'Statut','last_sync_at'=>'Dernière synchronisation'] as $key=>$label): ?><td data-label="<?= $label ?>"><?= htmlspecialchars($item[$key] ?? '—', ENT_QUOTES, 'UTF-8') ?></td><?php endforeach ?>
        <td data-label="Action"><a class="admin-table-action" href="/admin-payout?boutique_id=<?= (int)$item['boutique_id'] ?>"><?= empty($item['recipient_id']) ? 'Enregistrer chez Moko' : 'Voir / synchroniser' ?></a></td></tr><?php endforeach ?>
        <?php if (!$payoutRecipients): ?><tr><td class="admin-table-empty" colspan="9">Aucun bénéficiaire disponible.</td></tr><?php endif ?>
    </tbody></table></div></section>
</main>
<link rel="stylesheet" href="/asset/css/intlTelInput.min.css"><link rel="stylesheet" href="/asset/css/intl-tel-input-fix.css"><script src="/asset/js/intlTelInputWithUtils.min.js"></script><script src="/asset/js/moko_recipient.js?v=<?= filemtime(__DIR__.'/../asset/js/moko_recipient.js') ?>" defer></script><script src="/asset/js/admin_payout.js?v=<?= filemtime(__DIR__.'/../asset/js/admin_payout.js') ?>" defer></script>
