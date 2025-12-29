<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $category_id = html_entity_decode(filter_var($_POST['category_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
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
        $tables = '';
        $tables_ = "";
        for($i = 0, $e = 0; $i < count($all_types); $i++, $e++)
        {
            $types = only_select("types", $where = "id = ".$all_types[$i]['types'], $order = null, $limit = null);
            if($types)
            {
                $tables_ .= '<td class="choix_type" id="'.$types['id'].'" onclick="choixTypes(\''.$types['id'].'\')">'.$types['nom'].'</td>';
                
                if($e == 2 || $i == count($all_types)-1)
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
                    "msg" => "Aucun type trouvé"
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