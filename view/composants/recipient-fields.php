<?php $recipientProfile = $recipientProfile ?? null; ?>
<div class="recipient-summary admin-field--wide" id="recipient_summary" role="status" aria-live="polite"></div>
<fieldset class="recipient-fields admin-field--wide" id="recipient_fields">
    <legend>Compte Mobile Money du bénéficiaire</legend>
    <label class="admin-field"><span>Nom complet du titulaire</span><input type="text" name="beneficiary" minlength="2" maxlength="190" autocomplete="name" value="<?= htmlspecialchars($recipientProfile['beneficiary'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></label>
    <div class="admin-field"><label for="payout_phone">Numéro Mobile Money</label><input type="tel" id="payout_phone" name="phone_display" autocomplete="tel" value="<?= htmlspecialchars($recipientProfile['phone_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required><input type="hidden" id="payout_phone_international" name="phone_number"></div>
    <label class="admin-field"><span>Opérateur Mobile Money</span><select name="operator" required><option value="">Choisir</option><?php foreach (['airtel'=>'Airtel Money','orange'=>'Orange Money','mpesa'=>'M-Pesa','afrimoney'=>'Afrimoney'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($recipientProfile['operator'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?></select></label>
    <label class="admin-field"><span>Référence KYC (facultatif)</span><input type="text" name="kyc_reference" maxlength="128" value="<?= htmlspecialchars($recipientProfile['kyc_reference'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
</fieldset>
<div class="admin-form-actions"><button class="btn_ohnous" type="button" id="register_recipient"><i class="fa-solid fa-user-plus"></i> <span>Enregistrer le bénéficiaire</span></button></div>
