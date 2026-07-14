<main class="admin-shell admin-detail-page">
    <a class="admin-back-link" href="/admin-payouts"><i class="fa-solid fa-arrow-left"></i> Retour à l’historique des PayOut</a>
    <?= ohnous_render_admin_nav('payouts') ?>
    <section class="admin-detail-card payout-form-card"><div class="admin-detail-heading"><div><span class="admin-hero__eyebrow">FreshPay</span><h1>Nouveau PayOut</h1></div></div>
        <form id="admin_payout_form" class="admin-form-grid">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ohnous_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="admin-field"><span>Bénéficiaire</span><input type="text" name="beneficiary" required></label>
            <label class="admin-field"><span>Numéro</span><input type="tel" id="payout_phone" name="phone_display" required><input type="hidden" id="payout_phone_international" name="phone_number"></label>
            <label class="admin-field"><span>Opérateur Mobile Money</span><select name="operator" required><option value="">Choisir</option><option value="airtel">Airtel Money</option><option value="orange">Orange Money</option><option value="mpesa">M-Pesa</option><option value="afrimoney">Afrimoney</option></select></label>
            <label class="admin-field"><span>Montant</span><input type="number" name="amount" min="0.01" step="0.01" required></label>
            <label class="admin-field"><span>Devise</span><select name="currency" required><option value="USD">USD</option><option value="CDF">CDF</option></select></label>
            <label class="admin-field"><span>Référence</span><input type="text" name="reference" placeholder="Générée automatiquement si vide"></label>
            <label class="admin-field admin-field--wide"><span>Motif</span><textarea name="reason" rows="4" required></textarea></label>
            <div class="admin-form-actions"><button class="btn_ohnous" type="submit"><i class="fa-solid fa-paper-plane"></i> Effectuer le PayOut</button></div>
        </form>
    </section>
</main>
<link rel="stylesheet" href="/asset/css/intlTelInput.min.css"><link rel="stylesheet" href="/asset/css/intl-tel-input-fix.css"><script src="/asset/js/intlTelInputWithUtils.min.js"></script><script src="/asset/js/admin_payout.js" defer></script>
