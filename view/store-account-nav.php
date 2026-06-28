<?php $storeNavCurrent = $storeNavCurrent ?? 'infos'; ?>
<nav class="account-edit-nav">
    <a href="/editer-boutique" class="<?= $storeNavCurrent === 'infos' ? 'is-active' : '' ?>"><i class="fa-solid fa-store"></i><span>Informations</span></a>
    <a href="/editer-boutique-contact" class="<?= $storeNavCurrent === 'contact' ? 'is-active' : '' ?>"><i class="fa-solid fa-address-book"></i><span>Contact</span></a>
    <a href="/editer-boutique-securite" class="<?= $storeNavCurrent === 'security' ? 'is-active' : '' ?>"><i class="fa-solid fa-lock"></i><span>Sécurité</span></a>
</nav>
