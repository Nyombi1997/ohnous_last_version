<?php
    $accountNavCurrent = $accountNavCurrent ?? 'infos';
?>
<nav class="account-edit-nav">
    <div class="account-edit-nav__group">
        <span class="account-edit-nav__title">Compte</span>
        <a href="/editer-user" class="<?= $accountNavCurrent === 'infos' ? 'is-active' : '' ?>"><i class="fa-regular fa-user"></i><span>Mon profil</span></a>
        <a href="/message" class="<?= $accountNavCurrent === 'messages' ? 'is-active' : '' ?>"><i class="fa-regular fa-comments"></i><span>Ma communauté</span></a>
        <a href="/articles-aimes" class="<?= $accountNavCurrent === 'favoris' ? 'is-active' : '' ?>"><i class="fa-regular fa-heart"></i><span>Mes favoris</span></a>
    </div>
    <div class="account-edit-nav__group">
        <span class="account-edit-nav__title">Compte et sécurité</span>
        <a href="/editer-compte-contact" class="<?= $accountNavCurrent === 'contact' ? 'is-active' : '' ?>"><i class="fa-solid fa-address-book"></i><span>Contact</span></a>
        <a href="/editer-compte-securite" class="<?= $accountNavCurrent === 'securite' ? 'is-active' : '' ?>"><i class="fa-solid fa-lock"></i><span>Sécurité</span></a>
        <a href="/activation-compte" class="<?= $accountNavCurrent === 'activation' ? 'is-active' : '' ?>"><i class="fa-solid fa-user-check"></i><span>Activation</span></a>
    </div>
    <div class="account-edit-nav__group">
        <span class="account-edit-nav__title">Session</span>
        <a href="/deconnexion" class="account-edit-nav__danger"><i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span></a>
    </div>
</nav>
