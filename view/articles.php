<?php
    if(isset($GLOBALS['categorie']))
    {
        $categorie = $GLOBALS['categorie'];
    }
?>
<!-- afficher les articles et filtre -->
<div class="parent_container_affiche_produit">
    <!-- afficher les articles -->
    <div class="container_filtre_produit">
        <div class="sous_container_filtre_produit">
            <div class="titre_filtre_produit"></div>
            <!-- Liste des catégories -->
            <div class="div_liste_filtre_produit">
                <div class="titre_liste_filtre_produit"><p>Catégorie(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit">
                            <?php
                                /* afficher les categories */
                                $categories = select_bdd($bdd, "categorie_article", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
                                $category_ids = array();
                                foreach ($categories as $category) {
                                    $detail_category = only_select("categorie", $where = "id = '".$category['categorie']."'", $order = null, $limit = null);
                                    $categories_nombre = select_bdd($bdd, "categorie_article", $where = "categorie = '".$category['categorie']."'", $limit = null, $offset = 0, $order = null, $random = false);
                                    if(in_array($detail_category['id'], $category_ids)) {
                                        continue; // Passer à l'itération suivante si l'ID de catégorie a déjà été traité
                                    }
                                    echo '
                                    <div class="detail_liste_filtre_produit js_detail_liste_filtre_produit js_detail_liste_filtre_produit_'.$detail_category['id'].'" onclick="filtre_categorie(\''.$detail_category['id'].'\',\''.$detail_category['nom'].'\',\''.$detail_category['slug'].'\')">
                                        <div class="nom">'.$detail_category['nom'].'</div> <div class="nombre">'.count($categories_nombre).'</div>
                                    </div>';
                                    $category_ids[] = $detail_category['id'];
                                }
                            ?>
                    </div>
                </div>
            </div>
            <!-- Liste des catégories -->
            <div class="div_liste_filtre_produit null" id="div_filtre_types">
                <div class="titre_liste_filtre_produit"><p>Type(s)</p></div>
                <div class="liste_filtre_produit">
                    <div class="div_detail_liste_filtre_produit" id="details_filtre_types">
                    </div>
                </div>
            </div>
            <!-- Liste des catégories -->
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
    <div class="container_affiche_produit vue_article">
        <?php
            $donnee = select_bdd($bdd, "articles", $where = null, $limit = 12, $offset = 0, $order = null, $random = true);
            foreach($donnee as $data)
            {
                affiche_produit($data);
            }
            echo '<!-- HTML !-->
                <div class="div_btn_voir_plus">
                    <a href="" class="btn_voir_plus" role="button">Voir plus  <i class="fa-solid fa-arrow-right-long"></i></a>
                </div>';
        ?>
    </div>
</div>