<?php
    class home
    {
        public function showHome ()
        {
            /* ramener la vue home */
            $myView = new View('accueil');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
        public function showAddProduct ()
        {
            /* ramener la vue home */
            $myView = new View('upload_image');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
    }
?>