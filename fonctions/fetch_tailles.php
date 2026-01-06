<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $types_id = html_entity_decode(filter_var($_POST['types_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $all_tailles = select_bdd($bdd, "tailles_types", $where = " 	types = $types_id", $limit = null, $offset = 0, $order = null, $random = false);
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
                $tables_ .= '<td class="choix_taille" id="'.$tailles['id'].'" onclick="choixTailles(\''.$tailles['id'].'\')">'.$tailles['nom'].'</td>';
                
                if($e == 2 || $i == count($all_tailles)-1)
                {
                    $tables .= "<tr>$tables_</tr>";
                    $tables_ = "";
                    $e = -1;
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
            $thead = "";
            $th = "";
            /* placer les heads */
            for($i = 0; $i < count($head); $i++)
            {
                $th .= "<th>".$head[$i]."</th>";
            }
            $thead = "<thead>
                        <tr>$th</tr>
                    </thead>";
            /* placer les elements */
            $tables_ = "";
            $tables = "";
            for($i = 0, $p = 0; $p < $c; $i++)
            {
                for($e = 0; $e < count($head); $e++)
                {
                    sort($table_taille_id[$e]);
                    sort($table_taille_nom[$e]);
                    if(isset($table_taille_id[$e][$i]) && isset($table_taille_nom[$e][$i]))
                    {
                        $tables_ .= '<td class="choix_taille" id="'.$table_taille_id[$e][$i].'" onclick="choixTailles(\''.$table_taille_id[$e][$i].'\')">'.$table_taille_nom[$e][$i].'</td>';
                    }
                    else
                    {
                        $tables_ .= '<td class="choix_taille" id=""></td>';
                    }
                    $p++;
                }
                $tables .= "<tr>$tables_</tr>";
                $tables_ = "";
            }
            $tables = "<table class=\"table-grid\">$thead<tbody>$tables</tbody></table>";
        }
        else
        {
            $tables = "<table class=\"table-grid\"><tbody>$tables</tbody></table>";
        }

        $results = [
            "result" => "ok",
            "msg" => $tables
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>