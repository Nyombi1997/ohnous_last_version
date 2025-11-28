<?php

/* include config */
include_once '_config.php';

MyAutoload::start();

// Si 'r' n'est pas défini, on met 'home' comme route par défaut
$request = isset($_GET['r']) ? $_GET['r'] : 'accueil';

// Délègue toute la logique au routeur
$routeur = new Routeur($request);
$routeur->renderController();

?>