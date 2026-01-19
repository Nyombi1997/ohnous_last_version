<?php
    include_once "../model/bdd.php";
    header('Content-Type: application/json; charset=utf-8');

    $category_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $types_id = 0;
    if(isset($_POST['types']))
    {
        $types_id = filter_input(INPUT_POST, 'types', FILTER_VALIDATE_INT);
    }
    $taille_id = 0;
    if(isset($_POST['taille']))
    {
        $taille_id = filter_input(INPUT_POST, 'taille', FILTER_VALIDATE_INT);
    }

    if (!$category_id) {
        echo json_encode([
            "result" => "error",
            "msg" => "Catégorie invalide"
        ]);
        exit;
    }

    /* si on un type et une taille */
    if($types_id!=0 && $taille_id!=0)
    {
        /* types */
        $sql = "
            SELECT 
                t.id,
                t.nom,
                t.slug,
                COUNT(DISTINCT a.id) AS total
            FROM types t
            INNER JOIN types_article at ON at.types = t.id
            INNER JOIN articles a ON a.id = at.article
            INNER JOIN categorie_article ac ON ac.article = a.id
            WHERE ac.categorie = :category_id
            AND at.types = :types_id
            GROUP BY t.id
            HAVING total > 0
            ORDER BY t.nom ASC
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':types_id', $types_id, PDO::PARAM_INT);
        $stmt->execute();

        /* tailles */
        $sql = "
            SELECT 
                tal.id,
                tal.nom,
                tal.slug,
                COUNT(DISTINCT a.id) AS total
            FROM tailles tal
            INNER JOIN taille_articles ta ON ta.taille = tal.id
            INNER JOIN articles a ON a.id = ta.article
            INNER JOIN categorie_article ac ON ac.article = a.id
            INNER JOIN types_article at ON at.article = a.id
            WHERE ac.categorie = :category_id
            AND at.types = :types_id
            AND ta.taille = :taille_id
            GROUP BY tal.id
            HAVING total > 0
            ORDER BY tal.nom ASC
        ";

        $stmt_taille = $bdd->prepare($sql);
        $stmt_taille->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt_taille->bindValue(':types_id', $types_id, PDO::PARAM_INT);
        $stmt_taille->bindValue(':taille_id', $taille_id, PDO::PARAM_INT);
        $stmt_taille->execute();
        $taille = $stmt_taille->fetchAll(PDO::FETCH_ASSOC);
    }
    else if($types_id!=0)
    {
        $sql = "
            SELECT 
                t.id,
                t.nom,
                t.slug,
                COUNT(DISTINCT a.id) AS total
            FROM types t
            INNER JOIN types_article at ON at.types = t.id
            INNER JOIN articles a ON a.id = at.article
            INNER JOIN categorie_article ac ON ac.article = a.id
            WHERE ac.categorie = :category_id
            AND at.types = :types_id
            GROUP BY t.id
            HAVING total > 0
            ORDER BY t.nom ASC
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':types_id', $types_id, PDO::PARAM_INT);
        $stmt->execute();

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
            AND at.types = :types_id
            GROUP BY ta.id
            HAVING total > 0
            ORDER BY ta.nom ASC
        ";

        $stmt_taille = $bdd->prepare($sql);
        $stmt_taille->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt_taille->bindValue(':types_id', $types_id, PDO::PARAM_INT);
        $stmt_taille->execute();
        $taille = $stmt_taille->fetchAll(PDO::FETCH_ASSOC);
    }
    else
    {
        $sql = "
            SELECT 
                t.id,
                t.nom,
                t.slug,
                COUNT(DISTINCT a.id) AS total
            FROM types t
            INNER JOIN types_article at ON at.types = t.id
            INNER JOIN articles a ON a.id = at.article
            INNER JOIN categorie_article ac ON ac.article = a.id
            WHERE ac.categorie = :category_id
            GROUP BY t.id
            HAVING total > 0
            ORDER BY t.nom ASC
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$types) {
        echo json_encode([
            "result" => "error",
            "msg" => "Aucun type trouvé"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $html = "";

    foreach ($types as $type) {
        $active = "";
        if($type['id'] == $types_id)
        {
            $active = "active";
        }
        $html .= '
            <div class="detail_liste_filtre_produit 
                        '.$active.'
                        js_detail_liste_filtre_produit_types 
                        js_detail_liste_filtre_produit_types'.$type['id'].'"
                onclick=\'filtre_types('.(int)$type['id'].','.json_encode($type['nom']).','.json_encode($type['slug']).')\'>
                <div class="nom">'.$type['nom'].'</div>
                <div class="nombre">'.$type['total'].'</div>
            </div>';
    }

    $html_taille = "";

    /* s'il y'a une taille */
    if(isset($taille))
    {
        $tailles = $taille;
        foreach ($tailles as $taille) {
            $active = "";
            if($taille['id'] == $taille_id)
            {
                $active = "active";
            }
            $html_taille .= '
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
        "msg" => $html,
        "msg2" => $html_taille
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>