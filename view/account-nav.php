<?php
    $accountNavCurrent = $accountNavCurrent ?? 'infos';
?>
<nav class="account-edit-nav">
    <a href="/editer-user" class="<?= $accountNavCurrent === 'infos' ? 'is-active' : '' ?>"><i class="fa-regular fa-user"></i><span>Infos</span></a>
    <a href="/editer-compte-contact" class="<?= $accountNavCurrent === 'contact' ? 'is-active' : '' ?>"><i class="fa-solid fa-address-book"></i><span>Contact</span></a>
    <a href="/editer-compte-securite" class="<?= $accountNavCurrent === 'securite' ? 'is-active' : '' ?>"><i class="fa-solid fa-lock"></i><span>Sécurité</span></a>
</nav>
