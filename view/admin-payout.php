<?php
include_once MODEL . 'PayoutTransaction.php';
require_once FONCTION . 'moko_recipient.php';
$payoutBoutiques = (new PayoutTransaction($bdd))->boutiques();
$_SESSION['admin_payout_csrf_at'] = time();
?>
<main class="admin-shell admin-detail-page">
    <a class="admin-back-link" href="/admin-payouts"><i class="fa-solid fa-arrow-left"></i> Retour à l’historique des PayOut</a>
    <?= ohnous_render_admin_nav('payouts') ?>
    <section class="admin-detail-card payout-form-card"><div class="admin-detail-heading"><div><span class="admin-hero__eyebrow">Moko</span><h1>Nouveau PayOut</h1></div></div>
        <p>Sélectionnez la boutique et enregistrez son bénéficiaire avant le versement.</p>
        <form id="admin_payout_form" class="admin-form-grid">
            <?php renderHoneypot('moko_recipient'); ?>
            <input type="hidden" name="recipient_csrf" value="<?= htmlspecialchars(ohnous_recipient_csrf(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="admin-field admin-field--wide"><span>Boutique</span><select name="boutique_id" id="payout_boutique" required><option value="">Choisir une boutique</option><?php foreach ($payoutBoutiques as $boutique): ?><option value="<?= (int)$boutique['id'] ?>" <?= (int)($_GET['boutique_id'] ?? 0) === (int)$boutique['id'] ? 'selected' : '' ?>><?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach ?></select></label>
            <div class="admin-field--wide"><a id="payout_boutique_report" class="admin-back-link" href="/admin-payouts" hidden>Rapport des payouts de cette boutique</a></div>
            <?php include VIEW.'composants/recipient-fields.php'; ?>
            <label class="admin-field"><span>Montant</span><input type="number" name="amount" min="0.01" step="0.01" required></label>
            <label class="admin-field"><span>Devise</span><select name="currency" required><option value="CDF">CDF</option><option value="USD">USD</option></select></label>
            <label class="admin-field"><span>Référence unique de l’opération</span><input type="text" name="reference" minlength="4" maxlength="120" pattern="[A-Za-z0-9._-]{4,120}" placeholder="reversement-commande-123-vendeur-42" required></label>
            <label class="admin-field admin-field--wide"><span>Motif</span><textarea name="reason" rows="4" maxlength="255" required></textarea></label>
            <div class="admin-form-actions"><button class="btn_ohnous" type="submit" disabled><i class="fa-solid fa-paper-plane"></i> Effectuer le PayOut</button></div>
        </form>
    </section>
</main>
<link rel="stylesheet" href="/asset/css/intlTelInput.min.css"><link rel="stylesheet" href="/asset/css/intl-tel-input-fix.css"><script src="/asset/js/intlTelInputWithUtils.min.js"></script><script src="/asset/js/moko_recipient.js" defer></script><script src="/asset/js/admin_payout.js" defer></script>
