<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $category_id = html_entity_decode(filter_var($_POST['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $all_types = select_bdd($bdd, "categorie_types", $where = "categorie = $category_id", $limit = null, $offset = 0, $order = null, $random = false);
    if(!$all_types)
    {
        $results = [
            "result" => "error",
            "msg" => "Aucun type trouvé"
        ];
    }
    else
    {
        $tables = "";
        $array_types = array();
        for($i = 0, $e = 0; $i < count($all_types); $i++, $e++)
        {
            $types = only_select("types", $where = "id = ".$all_types[$i]['types'], $order = null, $limit = null);
            /* si on a déjà ajouter ce type */
            if(in_array($types['id'],$array_types))
            {
                continue;
            }
            /* combien d'article y'a de ce type */
            $types_nombre = select_bdd($bdd, "types_article", $where = "types = '".$types['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($types_nombre) == 0)
            {
                continue;
            }
            $array_types[] = $types['id'];
            $tables .= '
                        <div class="detail_liste_filtre_produit js_detail_liste_filtre_produit_types js_detail_liste_filtre_produit_types'.$types['id'].'" onclick="filtre_types(\''.$types['id'].'\',\''.$types['nom'].'\',\'\')">
                            <div class="nom">'.$types['nom'].'</div> <div class="nombre">'.count($types_nombre).'</div>
                        </div>';
        }

        $results = [
            "result" => "ok",
            "msg" => $tables
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>