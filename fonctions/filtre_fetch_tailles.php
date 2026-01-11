<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $types_id = html_entity_decode(filter_var($_POST['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $all_tailles = select_bdd($bdd, "tailles_types", $where = "types = $types_id", $limit = null, $offset = 0, $order = null, $random = false);
    if(!$all_tailles)
    {
        $results = [
            "result" => "error",
            "msg" => "Aucune taille trouvé"
        ];
    }
    else
    {
        $tables = '';
        $tables_ = "";
        $head = array();
        $table_taille_id = array();
        $table_taille_nom = array();
        $head_exist = false;
        $c = 0 ;
        for($i = 0, $e = 0; $i < count($all_tailles); $i++, $e++)
        {
            $tailles = only_select("tailles", $where = "id = ".$all_tailles[$i]['tailles'], $order = null, $limit = null);
            /* si la taille a un commentaire */
            if($tailles['commentaire'] != "" || $head_exist == true)
            {
                if(!in_array($tailles['commentaire'], $head))
                {
                    $head[] = $tailles['commentaire'];
                }
                $clef = array_search($tailles['commentaire'], $head);
                $table_taille_id[$clef][] = $tailles['id'];
                $table_taille_nom[$clef][] = $tailles['nom'];
                $c++;
                $head_exist = true;
            }
            elseif($tailles)
            {
                /* combien d'article y'a de cette taille */
                $tailles_nombre = select_bdd($bdd, "taille_articles", $where = "taille = '".$tailles['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
                if(count($tailles_nombre)!=0)
                {
                    $tables .= '
                            <div class="detail_liste_filtre_produit js_detail_liste_filtre_produit_tailles js_detail_liste_filtre_produit_tailles_'.$tailles['id'].'" onclick="filtre_tailles(\''.$tailles['id'].'\',\''.$tailles['nom'].'\',\'\')">
                                <div class="nom">'.$tailles['nom'].'</div> <div class="nombre">'.count($tailles_nombre).'</div>
                            </div>';                    
                }

                /* remplir le tableau des entêtes au cas où */
                if(!in_array($tailles['commentaire'], $head)) 
                {
                    $head[] = $tailles['commentaire'];
                }
                $clef = array_search($tailles['commentaire'], $head);
                $table_taille_id[$clef][] = $tailles['id'];
                $table_taille_nom[$clef][] = $tailles['nom'];
                $c++;
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "Aucune taille trouvé"
                ];
            }
        }
        /* si il existe des entêtes de taille */
        if($head_exist==true)
        {
            $tables = "";
            /* placer les heads */
            for($i = 0; $i < count($head); $i++)
            {
                sort($table_taille_id[$i]);
                sort($table_taille_nom[$i]);
                for($e = 0; $e < count($table_taille_id[$i]); $e++)
                {
                    /* combien d'article y'a de cette taille */
                    $tailles_nombre = select_bdd($bdd, "taille_articles", $where = "taille = '".$table_taille_id[$i][$e]."'", $limit = null, $offset = 0, $order = null, $random = false);
                    if(count($tailles_nombre) == 0)
                    {
                        continue;
                    }
                    $tables .= '
                            <div class="detail_liste_filtre_produit js_detail_liste_filtre_produit_tailles js_detail_liste_filtre_produit_tailles_'.$table_taille_id[$i][$e].'" onclick="filtre_tailles(\''.$table_taille_id[$i][$e].'\',\''.$table_taille_nom[$i][$e].'\',\'\')">
                                <div class="nom">'.$table_taille_nom[$i][$e].'</div> <div class="nombre">'.count($tailles_nombre).'</div>
                            </div>';                    
                }
            }
        }

        $results = [
            "result" => "ok",
            "msg" => $tables
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>