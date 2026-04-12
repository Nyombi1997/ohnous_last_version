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
        public function showSigninStore ()
        {
            /* ramener la vers inscription comme boutique */
            $myView = new View('signin-store');
            $myView->render('Ohnous | INSCRIVEZ VOTRE BOUTIQUE COMMENCEZ L\'EXPERIENCE OHNOUS !');
        }
        public function showStore ()
        {
            /* ramener la vers  boutique */
            $myView = new View('boutique');
            $myView->render('Ohnous | BOUTIQUE OHNOUS !');
        }
        public function showLogout ()
        {
            /* ramener la vers  deconnexion */
            $myView = new View('logout');
            $myView->render('Ohnous | BOUTIQUE OHNOUS !');
        }
        public function showEditStore ()
        {
            /* ramener la vers  editer boutique */
            $myView = new View('edit-boutique');
            $myView->render('Ohnous | MODIFIER BOUTIQUE OHNOUS !');
        }
        public function showEditStoreProfile ()
        {
            /* ramener la vers  editer profile boutique */
            $myView = new View('edit-profile-boutique');
            $myView->render('Ohnous | MODIFIER BOUTIQUE OHNOUS !');
        }
        public function showPassword ()
        {
            /* ramener la vers  changer mot de passe */
            $myView = new View('changer-mot-de-passe');
            $myView->render('Ohnous | MODIFIER MOT DE PASSE OHNOUS !');
        }
        public function showCodePassword ()
        {
            /* ramener la vers  changer mot de passe */
            $myView = new View('code-mot-de-passe');
            $myView->render('Ohnous | CODE DE VERIFICATION OHNOUS !');
        }
        public function showNewPassword ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('nouveau-mot-de-passe');
            $myView->render('Ohnous | NOUVEAU MOT DE PASSE OHNOUS !');
        }
        public function showActiveStore ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('activer-boutique');
            $myView->render('Ohnous | ACTIVER BOUTIQUE OHNOUS !');
        }
        public function showMessage ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('message');
            $myView->render('Ohnous | MESSAGE OHNOUS !');
        }
        public function showLikedArticles ()
        {
            $myView = new View('articles-aimes');
            $myView->render('Ohnous | ARTICLES AIMÉS OHNOUS !');
        }
        public function showAdminStoreActivation ()
        {
            $myView = new View('admin-activation-boutique');
            $myView->render('Ohnous | ADMIN ACTIVATION BOUTIQUE !');
        }
        public function showAdminLogin ()
        {
            $myView = new View('admin-login');
            $myView->render('Ohnous | CONNEXION ADMIN');
        }
        public function showAdminDashboard ()
        {
            $myView = new View('admin-dashboard');
            $myView->render('Ohnous | ESPACE ADMIN');
        }
        public function showAdminStores ()
        {
            $myView = new View('admin-boutiques');
            $myView->render('Ohnous | GESTION DES BOUTIQUES');
        }
        public function showAdminStoreDetails ()
        {
            $myView = new View('admin-boutique-details');
            $myView->render('Ohnous | DÉTAIL BOUTIQUE ADMIN');
        }
        public function showAdminArticles ()
        {
            $myView = new View('admin-articles');
            $myView->render('Ohnous | GESTION DES ARTICLES');
        }
        public function showAdminEditArticle ()
        {
            $myView = new View('admin-edit-article');
            $myView->render('Ohnous | MODIFICATION D’ARTICLE');
        }
        public function showAdminPassword ()
        {
            $myView = new View('admin-password');
            $myView->render('Ohnous | RÉINITIALISATION ADMIN');
        }
        public function showAdminNewPassword ()
        {
            $myView = new View('admin-new-password');
            $myView->render('Ohnous | NOUVEAU MOT DE PASSE ADMIN');
        }
        public function showSigninUser ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('signin-user');
            $myView->render('Ohnous | INSCRIPTION UTILISATEUR OHNOUS !');
        }
        public function showUserAccount ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('user');
            $myView->render('Ohnous | INSCRIPTION UTILISATEUR OHNOUS !');
        }
        public function showEditUser ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('edit-user');
            $myView->render('Ohnous | MODIFIER UTILISATEUR OHNOUS !');
        }
        public function showEditUserProfile ()
        {
            /* ramener la vers  nouveau mot de passe */
            $myView = new View('edit-profile-utilisateur');
            $myView->render('Ohnous | MODIFIER PROFILE UTILISATEUR OHNOUS !');
        }
    }
?>
