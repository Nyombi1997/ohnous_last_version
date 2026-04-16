<?php
    class home
    {
        public function showHome ()
        {
            $myView = new View('accueil');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }

        public function showAddProduct ()
        {
            $myView = new View('upload_image');
            $myView->render('Ohnous | DES BOUTIQUES ET DES ARTICLES DE QUALITÉ !');
        }

        public function showArticles ()
        {
            $myView = new View('articles');
            $myView->render('Shop | OhNous');
        }

        public function showShop ()
        {
            $this->showArticles();
        }

        public function showSearch ()
        {
            $this->showShop();
        }

        public function showLogin ()
        {
            $myView = new View('login');
            $myView->render('Ohnous | CONNECTEZ VOUS ET NE RATEZ RIEN DE TOUTES NOS NOUVEAUTÉS !');
        }

        public function showAccountChoice ()
        {
            $myView = new View('choice-signin');
            $myView->render('Ohnous | INSCRIVEZ VOUS ET NE RATEZ RIEN DE TOUTES NOS NOUVEAUTÉS !');
        }

        public function showSigninStore ()
        {
            $myView = new View('signin-store');
            $myView->render("Ohnous | INSCRIVEZ VOTRE BOUTIQUE COMMENCEZ L'EXPÉRIENCE OHNOUS !");
        }

        public function showStore ()
        {
            $myView = new View('boutique');
            $myView->render('Ohnous | BOUTIQUE OHNOUS !');
        }

        public function showLogout ()
        {
            $myView = new View('logout');
            $myView->render('Ohnous | BOUTIQUE OHNOUS !');
        }

        public function showEditStore ()
        {
            $myView = new View('edit-boutique');
            $myView->render('Ohnous | MODIFIER BOUTIQUE OHNOUS !');
        }

        public function showEditStoreProfile ()
        {
            $myView = new View('edit-profile-boutique');
            $myView->render('Ohnous | MODIFIER BOUTIQUE OHNOUS !');
        }

        public function showPassword ()
        {
            $myView = new View('changer-mot-de-passe');
            $myView->render('Ohnous | MODIFIER MOT DE PASSE OHNOUS !');
        }

        public function showCodePassword ()
        {
            $myView = new View('code-mot-de-passe');
            $myView->render('Ohnous | CODE DE VÉRIFICATION OHNOUS !');
        }

        public function showNewPassword ()
        {
            $myView = new View('nouveau-mot-de-passe');
            $myView->render('Ohnous | NOUVEAU MOT DE PASSE OHNOUS !');
        }

        public function showActiveStore ()
        {
            $myView = new View('activer-boutique');
            $myView->render('Ohnous | ACTIVER BOUTIQUE OHNOUS !');
        }

        public function showMessage ()
        {
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
            $myView->render("Ohnous | MODIFICATION D'ARTICLE");
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
            $myView = new View('signin-user');
            $myView->render('Ohnous | INSCRIPTION UTILISATEUR OHNOUS !');
        }

        public function showUserAccount ()
        {
            $myView = new View('user');
            $myView->render('Ohnous | INSCRIPTION UTILISATEUR OHNOUS !');
        }

        public function showCheckout ()
        {
            $myView = new View('checkout');
            $myView->render('Ohnous | CHECKOUT');
        }

        public function showAdminDeliveryZones ()
        {
            $myView = new View('admin-delivery-zones');
            $myView->render('Ohnous | ZONES DE LIVRAISON');
        }

        public function showEditUser ()
        {
            $myView = new View('edit-user');
            $myView->render('Ohnous | MODIFIER UTILISATEUR OHNOUS !');
        }

        public function showEditUserProfile ()
        {
            $myView = new View('edit-profile-utilisateur');
            $myView->render('Ohnous | MODIFIER PROFILE UTILISATEUR OHNOUS !');
        }
    }
?>
