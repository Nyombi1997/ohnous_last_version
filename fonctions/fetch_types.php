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
        $types_list = [];
        for($i = 0; $i < count($all_types); $i++)
        {
            $types = only_select("types", $where = "id = ".$all_types[$i]['types'], $order = null, $limit = null);
            if($types)
            {
                $types_list[] = $types;
            }
            else
            {
                $results = [
                    "result" => "error",
                    "msg" => "Aucun type trouvé"
                ];
            }
        }
        usort($types_list, function($a, $b){
            return strnatcasecmp((string)$a['nom'], (string)$b['nom']);
        });

        $tables = '';
        $tables_ = "";
        for($i = 0, $e = 0; $i < count($types_list); $i++, $e++)
        {
            $types = $types_list[$i];
            $tables_ .= '<td class="choix_type" id="'.$types['id'].'" onclick="choixTypes(\''.$types['id'].'\')">'.$types['nom'].'</td>';

            if($e == 2 || $i == count($types_list)-1)
            {
                $tables .= "<tr>$tables_</tr>";
                $tables_ = "";
                $e = -1;
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
