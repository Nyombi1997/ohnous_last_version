<?php
$recipientAccount = ohnous_get_current_account();
if (!empty($recipientAccount['connected']) && $recipientAccount['type'] === 'boutique') {
    $recipientStore = only_select('boutiques', 'id = '.(int)$recipientAccount['id'], null, null);
    if (ohnous_is_store_active($recipientStore)) {
        $recipientPending = true;
        $recipientHasDetails = false;
        try {
            require_once MODEL.'PayoutTransaction.php';
            $recipientInfo = (new PayoutTransaction($bdd))->recipientProfile($recipientAccount['id']);
            $recipientPending = empty($recipientInfo['existing_recipient_id']);
            $recipientHasDetails = !empty($recipientInfo['phone_number']) && !empty($recipientInfo['operator']) && !empty($recipientInfo['beneficiary']);
        } catch (Throwable $e) { /* Le rappel reste visible si le module est indisponible. */ }
        if ($recipientPending): ?>
            <aside class="recipient-reminder" id="store_recipient_reminder" role="status">
                <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                <span><?= $recipientHasDetails ? 'Finalisez l’enregistrement de votre bénéficiaire pour recevoir vos versements.' : 'Complétez vos coordonnées Mobile Money pour recevoir vos versements.' ?></span>
                <a class="btn_ohnous" href="/boutique-versements"><?= $recipientHasDetails ? 'Finaliser' : 'Compléter' ?></a>
            </aside>
        <?php endif;
    }
}
