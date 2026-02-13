<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    /* SI ON EST CONNECTER */
    if(isset($_SESSION['store_ohnous_987654321']))
    {
        $boutique = select_bdd($bdd, "boutiques", $where = 'unique_id = "'.$_SESSION['store_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($boutique)!=0)
        {
            $nom = html_entity_decode(filter_var($_POST['nom'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $verif_nom = select_bdd($bdd, "boutiques", $where = "nom = '$nom'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($verif_nom)==0)
            {
                $results = [
                    "result" => "ok",
                    "msg" => ""
                ];
                $slug = generateSlug($nom,$separator = '-');
                $update_data = [
                    "nom" => $nom,
                    "slug" => $slug,
                ];
                update_bdd($bdd, "boutiques", $update_data, "id = '".$boutique[0]['id']."'");
            }
            elseif($verif_nom[0]['unique_id']==$_SESSION['store_ohnous_987654321'])
            {
                $results = [
                    "result" => "ok",
                    "msg" => ""
                ];
            }
            else
            {
                for($i=0;count($verif_nom)>0;$i++)
                {
                    if($i<=9)
                    {
                        $nombre = '0'.$i;
                    }
                    else
                    {
                        $nombre = $i." cool";
                    }
                    $nom = $nom.' '.$nombre;
                    $sortie = '<a href="#" onclick=\'changeName('.json_encode($nom).')\'>'.$nom.'</a>';
                    $verif_nom = select_bdd($bdd, "boutiques", $where = "nom = '$nom'", $limit = null, $offset = 0, $order = null, $random = false);
                }
                $results = [
                    "result" => "error",
                    "msg" => $sortie
                ];
            }
        }
        else
        {
            $results = [
                "result" => "error",
                "msg" => "Vous n'êtes plus connecter"
            ];            
        }
    }
    else
    {
        $results = [
            "result" => "error",
            "msg" => "Vous n'êtes plus connecter"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>