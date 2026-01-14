<?php
    include_once "../model/bdd.php";
    header('Content-Type: application/json; charset=utf-8');

    $category_id = filter_input(INPUT_POST, 'categorie_id', FILTER_VALIDATE_INT);
    $type_id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$category_id || !$type_id) {
        echo json_encode([
            "result" => "error",
            "msg" => "Paramètres invalides"
        ]);
        exit;
    }

    $sql = "
        SELECT 
            ta.id,
            ta.nom,
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
        $html .= '
            <div class="detail_liste_filtre_produit 
                        js_detail_liste_filtre_produit_tailles 
                        js_detail_liste_filtre_produit_tailles_'.$taille['id'].'"
                onclick="filtre_tailles(\''.$taille['id'].'\', \''.$taille['nom'].'\', \'\')">
                <div class="nom">'.$taille['nom'].'</div>
                <div class="nombre">'.$taille['total'].'</div>
            </div>';
    }

    echo json_encode([
        "result" => "ok",
        "msg" => $html
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>