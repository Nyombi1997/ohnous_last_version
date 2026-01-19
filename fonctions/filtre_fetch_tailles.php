<?php
    include_once "../model/bdd.php";
    header('Content-Type: application/json; charset=utf-8');

    $category_id = filter_input(INPUT_POST, 'categorie', FILTER_VALIDATE_INT);
    $taille_id = filter_input(INPUT_POST, 'taille', FILTER_VALIDATE_INT);
    $type_id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($type_id == 0) {
        echo json_encode([
            "result" => "error",
            "msg" => $type_id
        ]);
        exit;
    }
    /* si on a déterminer la categorie */
    if($category_id!=0 || $taille_id!='')
    {
        /* si taille */
        if($category_id!=0)
        {
            $sql = "
                SELECT 
                    ta.id,
                    ta.nom,
                    ta.slug,
                    COUNT(DISTINCT a.id) AS total
                FROM tailles ta
                INNER JOIN taille_articles atl ON atl.taille = ta.id
                INNER JOIN articles a ON a.id = atl.article
                INNER JOIN categorie_article ac ON ac.article = a.id
                INNER JOIN types_article at ON at.article = a.id
                WHERE ac.categorie = :category_id
                AND at.types = :type_id
                GROUP BY ta.id
                HAVING total > 0
                ORDER BY ta.nom ASC
            ";

            $stmt = $bdd->prepare($sql);
            $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindValue(':type_id', $type_id, PDO::PARAM_INT);
            $stmt->execute();
        }
        else
        {
            $sql = "
                SELECT 
                    ta.id,
                    ta.nom,
                    ta.slug,
                    COUNT(DISTINCT a.id) AS total
                FROM tailles ta
                INNER JOIN taille_articles atl ON atl.taille = ta.id
                INNER JOIN articles a ON a.id = atl.article
                INNER JOIN types_article at ON at.article = a.id
                WHERE at.types = :type_id
                GROUP BY ta.id
                HAVING total > 0
                ORDER BY ta.nom ASC
            ";

            $stmt = $bdd->prepare($sql);
            $stmt->bindValue(':type_id', $type_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        $tailles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$tailles) {
            echo json_encode([
                "result" => "error",
                "msg" => "Aucune taille trouvée"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } 

        $html = "";
        foreach ($tailles as $taille) {
            $active = "";
            if($taille['id'] == $taille_id)
            {
                $active = "active";
            }
            $html .= '
                <div class="detail_liste_filtre_produit 
                            '.$active.'
                            js_detail_liste_filtre_produit_tailles 
                            js_detail_liste_filtre_produit_tailles_'.$taille['id'].'"
                    onclick=\'filtre_tailles('.(int)$taille['id'].','.json_encode($taille['nom']).','.json_encode($taille['slug']).')\'>
                    <div class="nom">'.$taille['nom'].'</div>
                    <div class="nombre">'.$taille['total'].'</div>
                </div>';
        } 
    }
    else
    {
        $sql = "
            SELECT 
                ta.id,
                ta.nom,
                ta.slug,
                COUNT(DISTINCT a.id) AS total
            FROM tailles ta
            INNER JOIN taille_articles atl ON atl.taille = ta.id
            INNER JOIN articles a ON a.id = atl.article
            INNER JOIN types_article at ON at.article = a.id
            WHERE at.types = :type_id
            GROUP BY ta.id
            HAVING total > 0
            ORDER BY ta.nom ASC
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':type_id', $type_id, PDO::PARAM_INT);
        $stmt->execute();

        $tailles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$tailles) {
            echo json_encode([
                "result" => "error",
                "msg" => "Aucune taille trouvée"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $html = "";
        foreach ($tailles as $taille) {
            $active = "";
            if($taille['id'] == $taille_id)
            {
                $active = "active";
            }
            $html .= '
                <div class="detail_liste_filtre_produit 
                            '.$active.'
                            js_detail_liste_filtre_produit_tailles 
                            js_detail_liste_filtre_produit_tailles_'.$taille['id'].'"
                    onclick=\'filtre_tailles('.(int)$taille['id'].','.json_encode($taille['nom']).','.json_encode($taille['slug']).')\'>
                    <div class="nom">'.$taille['nom'].'</div>
                    <div class="nombre">'.$taille['total'].'</div>
                </div>';
        }
    }

    echo json_encode([
        "result" => "ok",
        "msg" => $html
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>