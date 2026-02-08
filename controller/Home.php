<?php
    class home
    {
        public function showHome ()
        {
            /* ramener la vers home */
            $myView = new View('accueil');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
        public function showAddProduct ()
        {
            /* ramener la vers ajout article */
            $myView = new View('upload_image');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
        public function showArticles ()
        {
            /* ramener la vers all articles */
            $myView = new View('articles');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
        public function showSearch ()
        {
            /* ramener la vers all articles */
            $myView = new View('articles');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }
        public function showLogin ()
        {
            /* ramener la vers all articles */
            $myView = new View('login');
            $myView->render('Ohnous | CONNECTEZ VOUS ET NE RATEZ RIEN DE TOUTES NOS NOUVEAUTÉS !');
        }
        public function showAccountChoice ()
        {
            /* ramener la vers all articles */
            $myView = new View('choice-signin');
            $myView->render('Ohnous | INSCRIVEZ VOUS ET NE RATEZ RIEN DE TOUTES NOS NOUVEAUTÉS !');
        }
    }
?>