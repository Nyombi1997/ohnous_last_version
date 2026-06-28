<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(!isset($_SESSION['user_ohnous_987654321']))
    {
        header("Location:/404");
        exit();
    }
    $user = only_select("utilisateur", "unique_id = '".addslashes($_SESSION['user_ohnous_987654321'])."'", null, null);
    if(!$user)
    {
        header("Location:/404");
        exit();
    }
    $accountNavCurrent = 'contact';
    $latestRequest = ohnous_get_latest_user_activation_request((int)$user['id']);
?>
<script>let home_page = true;</script>
<div class="intro-hero plus">
    <div class="blob-bg"><span id="new_boutique"></span></div>
    <div class="container_login_page account-edit-shell">
        <?php include VIEW.'account-nav.php'; ?>
        <section class="liquid-panel account-edit-panel">
            <h1>Contact et activation</h1>
            <p class="account-edit-muted">Statut : <?= ohnous_is_user_active($user) ? 'Compte activé' : 'Compte non activé' ?></p>
            <?php if($latestRequest): ?>
                <div class="activation-panel__details">
                    <span><?= ohnous_get_user_activation_status_label($latestRequest['statut'] ?? 'en_attente') ?></span>
                    <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$latestRequest['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>
            <a href="/activation-compte" class="btn_ohnous">Demander l’activation</a>
        </section>
    </div>
</div>
