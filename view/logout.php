<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if(isset($_SESSION['store_ohnous_987654321']))
    {
        unset($_SESSION['store_ohnous_987654321']);
    }
    header('Location: accueil');
    exit();
?>