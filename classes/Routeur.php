<?php

    class Routeur {
        private $request;

        private $routes = [
                            "" => ["controller" => 'Home', "method" => 'showHome'], 
                            "accueil" => ["controller" => 'Home', "method" => 'showHome'],

                            "ajouter-articles" => ["controller" => 'Home', "method" => 'showAddProduct'],
                            
                            "articles" => ["controller" => 'Home', "method" => 'showArticles'],
                            "Articles" => ["controller" => 'Home', "method" => 'showArticles'],
                            "Article" => ["controller" => 'Home', "method" => 'showArticles'],
                            "article" => ["controller" => 'Home', "method" => 'showArticles'],

                            "connexion" => ["controller" => 'Home', "method" => 'showLogin'],
                            "choix-compte" => ["controller" => 'Home', "method" => 'showAccountChoice'],
                            "inscription-boutique" => ["controller" => 'Home', "method" => 'showSigninStore'],

                            "q" => ["controller" => 'Home', "method" => 'showSearch'],
                        ];

        public function __construct($request) {
            $this->request = $request;
        }

        public function renderController() {

            $request = $this->request;

            if (key_exists($request, $this->routes)) {
                $controller = $this->routes[$request]['controller'];
                $method = $this->routes[$request]['method'];

                $currentController = new $controller();
                $currentController->$method();
            } else {
                // Sinon, on considère que c’est un slug

                /* recuperer les urls */
                $segments = explode('/', $request);
                $params = [];

                for ($i = 0; $i < count($segments); $i += 2) {
                    if (isset($segments[$i + 1])) {
                        $params[$segments[$i]] = $segments[$i + 1];
                    }
                }
                include MODEL . 'bdd.php';

                $titre_page = [];
                $found_filtre = false;

                // CATEGORIE
                if (!empty($params['categorie'])) {
                    $stmt = $bdd->prepare("SELECT * FROM categorie WHERE slug = ?");
                    $stmt->execute([$params['categorie']]);
                    if ($categorie = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $GLOBALS['categorie'] = $categorie;
                        $titre_page[] = $categorie['nom'];
                        $found_filtre = true;
                    }
                }

                // TYPE
                if (!empty($params['type'])) {
                    $stmt = $bdd->prepare("SELECT * FROM types WHERE slug = ?");
                    $stmt->execute([$params['type']]);
                    if ($types = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $GLOBALS['types'] = $types;
                        $titre_page[] = $types['nom'];
                        $found_filtre = true;
                    }
                }

                // TAILLE
                if (!empty($params['taille'])) {
                    $stmt = $bdd->prepare("SELECT * FROM tailles WHERE slug = ?");
                    $stmt->execute([$params['taille']]);
                    if ($tailles = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $GLOBALS['tailles'] = $tailles;
                        $titre_page[] = $tailles['nom'];
                        $found_filtre = true;
                    }
                }
                /* si on a  trouvé des filtre */
                if($found_filtre == true)
                {
                        
                    $view = new View('articles');
                    $titre_page = implode(' | ', $titre_page);
                    $view->render($titre_page. ' | OhNous');
                }

                /* si c'est un article */
                else if(!empty($params['article']))
                {
                    $stmt = $bdd->prepare("SELECT * FROM articles WHERE slug = ?");
                    $stmt->execute([$params['article']]);
                    if ($article = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $GLOBALS['article'] = $article;
                        $titre_page[] = $article['nom'];
                        $view = new View('article-details.php');
                        $titre_page = implode(' | ', $titre_page);
                        $view->render($titre_page. ' | OhNous');
                    }
                }

                /* si c'est une boutique */
                else if(!empty($params['boutique']))
                {
                    $stmt = $bdd->prepare("SELECT * FROM boutiques WHERE slug = ?");
                    $stmt->execute([$params['boutique']]);
                    if ($boutique = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $GLOBALS['boutique'] = $boutique;
                        $titre_page[] = $boutique['nom'];
                        $view = new View('boutique.php');
                        $titre_page = implode(' | ', $titre_page);
                        $view->render($titre_page. ' | OhNous');
                    }
                }
                /* si on a rien trouvé */
                else if($found_filtre == false){
                    echo '
                    <!DOCTYPE html>
                    <html lang="fr">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>404</title>
                        <style>
                            *{
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            body {
                                font-family: Arial, sans-serif;
                                text-align: center;
                                padding: 50px;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                            }
                            div.container {
                                position: absolute;
                                top: 0;
                                left: 0;
                                bottom: 0;
                                right: 0;
                                width: 100%;
                                height: 100vh;
                                background: linear-gradient(135deg, #ffabe7, #6c7ae0);
                                padding: 20px 0px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: #444444;
                            }
                            h1 {
                                font-size: 50px;
                                margin-bottom: 20px;
                            }
                            p {
                                font-size: 20px;
                                margin-bottom: 20px;
                            }
                            a {
                                color: #444444;
                                text-decoration: none;
                                font-weight: bold;
                                display: inline-block;
                                padding: 10px 20px;
                                background-color: #ffabe7;
                                border-radius: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="">
                                <h1>404 - Page non trouvée</h1>
                                <p>La page que vous recherchez n\'existe pas.</p>
                                <p><a href="/accueil">Retourner à la page d\'accueil</a></p>  
                            </div>
                        </div>                  
                    </body>
                    </html>';
                }
            }
        }
    }
?>