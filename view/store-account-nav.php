<?php $storeNavCurrent = $storeNavCurrent ?? 'infos'; ?>
<nav class="account-edit-nav">
    <div class="account-edit-nav__group">
        <a href="/editer-boutique" class="<?= $storeNavCurrent === 'infos' ? 'is-active' : '' ?>">
            <i class="fa-regular fa-user"></i>
            <span><strong>Informations</strong><small>Profil et présentation</small></span>
        </a>
        <a href="/ajouter-articles" class="<?= $storeNavCurrent === 'articles' ? 'is-active' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            <span><strong>Mes articles</strong><small>Catalogue boutique</small></span>
        </a>
    </div>
    <div class="account-edit-nav__group">
        <a href="/editer-boutique-contact" class="<?= $storeNavCurrent === 'contact' ? 'is-active' : '' ?>">
            <i class="fa-solid fa-address-book"></i>
            <span><strong>Contact</strong><small>Réseaux et liens publics</small></span>
        </a>
        <a href="/editer-boutique-securite" class="<?= $storeNavCurrent === 'security' ? 'is-active' : '' ?>">
            <i class="fa-solid fa-lock"></i>
            <span><strong>Sécurité</strong><small>Mot de passe boutique</small></span>
        </a>
        <a href="/activer-boutique" class="<?= $storeNavCurrent === 'activation' ? 'is-active' : '' ?>">
            <i class="fa-solid fa-circle-check"></i>
            <span><strong>Activation</strong><small>Demande et suivi</small></span>
        </a>
    </div>
    <div class="account-edit-nav__group">
        <a href="/message?admin=1" class="<?= $storeNavCurrent === 'support' ? 'is-active' : '' ?>">
            <i class="fa-regular fa-circle-question"></i>
            <span><strong>Centre d’aide</strong><small>Support OhNous</small></span>
        </a>
        <a href="/deconnexion" class="account-edit-nav__danger">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span><strong>Déconnexion</strong><small>Fermer la session</small></span>
        </a>
    </div>
</nav>
