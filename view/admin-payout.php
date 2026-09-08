<?php
include_once MODEL . 'PayoutTransaction.php';
$payoutBoutiques = (new PayoutTransaction($bdd))->boutiques();
$_SESSION['admin_payout_csrf_at'] = time();
?>
<main class="admin-shell admin-detail-page">
    <a class="admin-back-link" href="/admin-payouts"><i class="fa-solid fa-arrow-left"></i> Retour à l’historique des PayOut</a>
    <?= ohnous_render_admin_nav('payouts') ?>
    <section class="admin-detail-card payout-form-card"><div class="admin-detail-heading"><div><span class="admin-hero__eyebrow">Moko</span><h1>Nouveau PayOut</h1></div></div>
        <p>Versement Mobile Money en RDC. Réutilisez la même référence pour une même opération. Le bénéficiaire doit être actif chez Moko.</p>
        <form id="admin_payout_form" class="admin-form-grid">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ohnous_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="admin-field admin-field--wide"><span>Boutique</span><select name="boutique_id" id="payout_boutique" required><option value="">Choisir une boutique</option><?php foreach ($payoutBoutiques as $boutique): ?><option value="<?= (int)$boutique['id'] ?>" <?= (int)($_GET['boutique_id'] ?? 0) === (int)$boutique['id'] ? 'selected' : '' ?>><?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach ?></select></label>
            <div class="admin-field--wide"><a id="payout_boutique_report" class="admin-back-link" href="/admin-payouts" hidden>Rapport des payouts de cette boutique</a></div>
            <label class="admin-field"><span>Bénéficiaire</span><input type="text" name="beneficiary" required></label>
            <label class="admin-field"><span>Identifiant stable du bénéficiaire</span><input type="text" name="merchant_recipient_id" maxlength="128" pattern="[A-Za-z0-9_-]+" placeholder="vendor_42" required></label>
            <label class="admin-field"><span>Référence du dossier KYC (facultatif)</span><input type="text" name="kyc_reference" maxlength="128"></label>
            <label class="admin-field"><span>ID Moko existant (rattachement facultatif)</span><input type="text" name="existing_recipient_id" maxlength="64" placeholder="rec_…"></label>
            <div class="admin-field"><label for="payout_phone">Numéro</label><input type="tel" id="payout_phone" name="phone_display" autocomplete="off" aria-controls="payout_phone_suggestions" required><input type="hidden" id="payout_phone_international" name="phone_number"><div id="payout_phone_suggestions" class="payout-phone-suggestions" hidden></div></div>
            <label class="admin-field"><span>Opérateur Mobile Money</span><select name="operator" required><option value="">Choisir</option><option value="airtel">Airtel Money</option><option value="orange">Orange Money</option><option value="mpesa">M-Pesa</option><option value="afrimoney">Afrimoney</option></select></label>
            <label class="admin-field"><span>Montant</span><input type="number" name="amount" min="0.01" step="0.01" required></label>
            <label class="admin-field"><span>Devise</span><select name="currency" required><option value="CDF">CDF</option><option value="USD">USD</option></select></label>
            <label class="admin-field"><span>Référence unique de l’opération</span><input type="text" name="reference" minlength="4" maxlength="120" pattern="[A-Za-z0-9._-]{4,120}" placeholder="reversement-commande-123-vendeur-42" required></label>
            <label class="admin-field admin-field--wide"><span>Motif</span><textarea name="reason" rows="4" maxlength="255" required></textarea></label>
            <div class="admin-form-actions"><button class="btn_ohnous" type="submit"><i class="fa-solid fa-paper-plane"></i> Effectuer le PayOut</button></div>
        </form>
    </section>
</main>
<link rel="stylesheet" href="/asset/css/intlTelInput.min.css"><link rel="stylesheet" href="/asset/css/intl-tel-input-fix.css"><script src="/asset/js/intlTelInputWithUtils.min.js"></script><script src="/asset/js/admin_payout.js" defer></script>
