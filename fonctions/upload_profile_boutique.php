<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
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
            $boutique = $boutique[0];

            $product_image_url = html_entity_decode(filter_var($_POST['product_image_url'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $fileId = html_entity_decode(filter_var($_POST['fileId'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $background = html_entity_decode(filter_var($_POST['background'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $hasFileIdColumn = ohnous_column_exists('boutiques', 'fileId');
            $oldFileId = $hasFileIdColumn ? (string)($boutique['fileId'] ?? '') : '';
            $verif_boutique = select_bdd($bdd, "boutiques", $where = "id = '".$boutique['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($verif_boutique)==0)
            {

                $results = [
                    "result" => "error",
                    "msg" => "vous n'êtes pas connecter"
                ];

                // Retour en JSON
                echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
            else
            {
                /* ajouter l'image */
                $update_data = [
                    "profile" => $product_image_url,
                    "backgrounds" => $background,
                ];
                if($hasFileIdColumn)
                {
                    $update_data["fileId"] = $fileId;
                }

                $updated = update_bdd($bdd, "boutiques", $update_data, "id = '".$boutique['id']."'");
                if(!$updated)
                {
                    echo json_encode([
                        "result" => "error",
                        "msg" => "Impossible de mettre à jour la photo."
                    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    exit;
                }

                if($oldFileId !== '' && $fileId !== '' && $oldFileId !== $fileId)
                {
                    ohnous_delete_imagekit_file($oldFileId);
                }

                $results = [
                    "result" => "ok",
                    "msg" => ""
                ];

                // Retour en JSON
                echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        }
        else
        {

            $results = [
                "result" => "error",
                "msg" => "vous n'êtes pas connecter"
            ];

            // Retour en JSON
            echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
    else
    {

        $results = [
            "result" => "error",
            "msg" => "vous n'êtes pas connecter"
        ];

        // Retour en JSON
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
?>
