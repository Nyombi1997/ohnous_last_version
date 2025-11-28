<?php

    class home
    {
        public function showHome ()
        {
            /* ramener la vue home */
            $myView = new View('accueil');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
    }
?>