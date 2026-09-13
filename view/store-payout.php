<?php
$account = ohnous_get_current_account();
if (empty($account['connected']) || $account['type'] !== 'boutique') {
    header('Location: /connexion'); exit();
}
require_once FONCTION.'moko_recipient.php';
require_once MODEL.'PayoutTransaction.php';
$recipientProfile = null;
$recipientAvailable = true;
try { $recipientProfile = (new PayoutTransaction($bdd))->recipientProfile($account['id']); }
catch (Throwable $e) { $recipientAvailable = false; }
?>
<div class="edit-shop-page store-recipient-page">
    <div class="edit-shop-shell"><div class="edit-shop-layout">
        <aside class="edit-shop-sidebar"><?php $storeNavCurrent = 'payout'; include VIEW.'store-account-nav.php'; ?></aside>
        <main class="edit-shop-content"><section class="edit-shop-card">
            <div class="edit-shop-card__head"><h1>Coordonnées de versement</h1></div>
            <?php if (!$recipientAvailable): ?>
                <p>Les coordonnées de versement sont momentanément indisponibles. Contactez le support.</p>
            <?php else: ?>
                <form id="store_recipient_form" class="admin-form-grid" data-profile="<?= htmlspecialchars(json_encode($recipientProfile, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                    <?php renderHoneypot('moko_recipient'); ?>
                    <input type="hidden" name="recipient_csrf" value="<?= htmlspecialchars(ohnous_recipient_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                    <?php include VIEW.'composants/recipient-fields.php'; ?>
                </form>
            <?php endif ?>
        </section></main>
    </div></div>
</div>
<link rel="stylesheet" href="/asset/css/intlTelInput.min.css"><link rel="stylesheet" href="/asset/css/intl-tel-input-fix.css"><script src="/asset/js/intlTelInputWithUtils.min.js"></script><script src="/asset/js/moko_recipient.js" defer></script>
