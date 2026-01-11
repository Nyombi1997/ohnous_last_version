<?php
    class View {
        private $template;

        public function __construct($template) 
        {
            $this->template = $template;
        }

        public function render($title_page = 'OhNous')
        {
            /* lien du site web */
            $lien_actuel = "http://ohnous.new.local";
            
            $template = $this->template;

            /* ramener la bdd */
            include MODEL.'bdd.php';
            /* ramener la recherche bdd */
            include_once MODEL.'select.php';
            /* ramener la fonction product */
            include_once VIEW.'composants/fonction_produit.php';
            /* ramener les fonctions */
            include_once FONCTION.'fonctions.php';
            global $bdd;

            ob_start();

            // Prépare le chemin de template en ajoutant .php si nécessaire
            $templatePath = VIEW . $template;
            if (substr($templatePath, -4) !== '.php') {
                $templatePath .= '.php';
            }

            include_once $templatePath;
            $contentPage = ob_get_clean();
            // Si on va vers l’accueille
            if (basename($templatePath) != 'accueil.php') {
                $GLOBALS['others'] = 'ok';
            }
            include_once VIEW . '_gabarit.php';
        }
    }
?>