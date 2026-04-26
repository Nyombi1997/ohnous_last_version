<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    /* SI ON EST CONNECTER */
    if(isset($_SESSION['user_ohnous_987654321']))
    {
        $user = select_bdd($bdd, "utilisateur", $where = 'unique_id = "'.$_SESSION['user_ohnous_987654321'].'"', $limit = null, $offset = 0, $order = null, $random = false);
        if(count($user)!=0)
        {
            $user = $user[0];

            $product_image_url = html_entity_decode(filter_var($_POST['product_image_url'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $fileId = html_entity_decode(filter_var($_POST['fileId'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $background = html_entity_decode(filter_var($_POST['background'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $hasFileIdColumn = ohnous_column_exists('utilisateur', 'fileId');
            $oldFileId = $hasFileIdColumn ? (string)($user['fileId'] ?? '') : '';
            $verif_user = select_bdd($bdd, "utilisateur", $where = "id = '".$user['id']."'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($verif_user)==0)
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

                $updated = update_bdd($bdd, "utilisateur", $update_data, "id = '".$user['id']."'");
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
