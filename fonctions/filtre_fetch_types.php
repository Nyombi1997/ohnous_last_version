<?php
    include_once "../model/bdd.php";
    header('Content-Type: application/json; charset=utf-8');

    $category_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$category_id) {
        echo json_encode([
            "result" => "error",
            "msg" => "Catégorie invalide"
        ]);
        exit;
    }

    $sql = "
        SELECT 
            t.id,
            t.nom,
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
        $html .= '
            <div class="detail_liste_filtre_produit 
                        js_detail_liste_filtre_produit_types 
                        js_detail_liste_filtre_produit_types'.$type['id'].'"
                onclick="filtre_types(\''.$type['id'].'\', \''.$type['nom'].'\', \'\')">
                <div class="nom">'.$type['nom'].'</div>
                <div class="nombre">'.$type['total'].'</div>
            </div>';
    }

    echo json_encode([
        "result" => "ok",
        "msg" => $html
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>