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
        for($i = 0, $e = 0; $i < count($all_tailles); $i++, $e++)
        {
            $tailles = only_select("tailles", $where = "id = ".$all_tailles[$i]['tailles'], $order = null, $limit = null);
            if($tailles)
            {
                $tables_ .= '<td class="choix_taille" id="'.$tailles['id'].'" onclick="choixTailles(\''.$tailles['id'].'\')">'.$tailles['nom'].'</td>';
                
                if($e == 2 || $i == count($all_tailles)-1)
                {
                    $tables .= "<tr>$tables_</tr>";
                    $tables_ = "";
                    $e = -1;
                }
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "Aucune taille trouvé"
                ];
            }
        }
        $tables = "<table class=\"table-grid\"><tbody>$tables</tbody></table>";

        $results = [
            "result" => "ok",
            "msg" => $tables
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>