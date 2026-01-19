
<?php
    /* si types */
    if(isset($GLOBALS['types']))
    {
        $types = $GLOBALS['types'];
        /* si existe categorie */
        if(isset($GLOBALS['categorie']))
        {
            echo '
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        prevalueTypes('.(int)$types['id'].', '.json_encode($types['nom']).', '.json_encode($types['slug']).');
                    });
                </script>';            
        }
    }
    /* si taille */
    if(isset($GLOBALS['tailles']))
    {
        $taille = $GLOBALS['tailles'];
        /* si existe categorie ou type */
        if(isset($GLOBALS['categorie']) || isset($GLOBALS['types']))
        {
            echo '
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        prevalueTailles('.(int)$taille['id'].', '.json_encode($taille['nom']).', '.json_encode($taille['slug']).');
                    });
                </script>';            
        }
    }
    /* si categorie */
    if(isset($GLOBALS['categorie']))
    {
        $categorie = $GLOBALS['categorie'];
        echo '
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    // Votre fonction peut être appelée ici en toute sécurité
                    filtre_categorie('.(int)$categorie['id'].','.json_encode($categorie['nom']).','.json_encode($categorie['slug']).',null,null,'.json_encode('ok').');
                });
            </script>';
    }
    /* si types */
    if(isset($GLOBALS['types']))
    {
        $types = $GLOBALS['types'];
        /* si pas $GLOBALS['categorie'] */
        if(!isset($GLOBALS['categorie']))
        {
            echo '
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        // Votre fonction peut être appelée ici en toute sécurité
                        filtre_types('.(int)$types['id'].','.json_encode($types['nom']).','.json_encode($types['slug']).',null,"ok",null);
                    });
                </script>';
        }
    }
    /* si tailles */
    if(isset($GLOBALS['tailles']))
    {
        $tailles = $GLOBALS['tailles'];
        /* si pas $GLOBALS['categorie'] */
        if(!isset($GLOBALS['types']))
        {
            echo '
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        // Votre fonction peut être appelée ici en toute sécurité
                        filtre_tailles('.(int)$tailles['id'].','.json_encode($tailles['nom']).','.json_encode($tailles['slug']).',null,"ok",null);
                    });
                </script>';
        }
    }


    
    /* si c'est une recherche */
    if(isset($_GET['query']))
    {
        echo '
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    prevalueRecherche('.json_encode($_GET['query']).');
                });
            </script>';
    }
?>
<!-- afficher les articles et filtre -->
<div class="parent_container_affiche_produit">
    <!-- afficher les articles -->
    <div class="container_filtre_produit">
        <div class="sous_container_filtre_produit">
            <div class="titre_filtre_produit"></div>
            <!-- Liste des catégories -->
            <div class="div_liste_filtre_produit"  id="div_filtre_categories">
                <div class="titre_liste_filtre_produit"><p>Catégorie(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_categories">
                            <?php
                                /* afficher les categories */
                                $categories = select_bdd($bdd, "categorie", $where = null, $limit = null, $offset = 0, $order = "nom", $random = false);
                                $html_categorie = "";
                                foreach ($categories as $category) {
                                    $categories_nombre = select_bdd($bdd, "categorie_article", $where = "categorie = '".$category['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                                    if(count($categories_nombre) == 0)
                                    {
                                        continue;
                                    }
                                    echo '
                                    <div class="detail_liste_filtre_produit js_detail_liste_filtre_produit js_detail_liste_filtre_produit_'.$category['id'].'" onclick=\'filtre_categorie('.(int)$category['id'].','.json_encode($category['nom']).','.json_encode($category['slug']).')\'>
                                        <div class="nom">'.$category['nom'].'</div> <div class="nombre">'.count($categories_nombre).'</div>
                                    </div>';
                                }
                            ?>
                    </div>
                </div>
            </div>
            <!-- Liste des types -->
            <div class="div_liste_filtre_produit null" id="div_filtre_types">
                <div class="titre_liste_filtre_produit"><p>Type(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_types">
                    </div>
                </div>
            </div>
            <!-- Liste des tailles -->
            <div class="div_liste_filtre_produit null" id="div_filtre_tailles">
                <div class="titre_liste_filtre_produit"><p>Taille(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_tailles">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- afficher les articles -->
    <div class="container_affiche_produit vue_article" id="afficher_article">
        <?php
            /* si c'est une recherche */
            if(isset($_GET['query']))
            {
                $query =  found($_GET['query'], $limit = null, 0, $order = null, $random = false);
                $donnee = getArticlesFromSearch($query, $limit = 12, 0, $order = null, $random = false);
                foreach($donnee as $data)
                {
                    affiche_produit($data);
                }
            }
            else
            {
                $donnee = select_bdd($bdd, "articles", $where = null, $limit = 12, $offset = 0, $order = null, $random = true);
                foreach($donnee as $data)
                {
                    affiche_produit($data);
                }
            }
        ?>
    </div>
</div>