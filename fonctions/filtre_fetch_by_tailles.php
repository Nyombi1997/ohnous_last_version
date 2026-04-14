<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $taille_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$taille_id) {
        echo json_encode([
            "result" => "error",
            "msg" => "Type d'article invalide"
        ]);
        exit;
    }
    /* trouver les categories */
    $sql = "
        SELECT 
            ca.id,
            ca.nom,
            ca.slug,
            COUNT(DISTINCT a.id) AS total
        FROM categorie ca
        INNER JOIN categorie_article ac ON ac.categorie = ca.id
        INNER JOIN articles a ON a.id = ac.article
        INNER JOIN boutiques bo_cat ON bo_cat.id = a.boutique AND bo_cat.activer = 1
        INNER JOIN taille_articles ta ON ta.article = a.id
        WHERE ta.taille = :taille_id
        GROUP BY ca.id
        HAVING total > 0
        ORDER BY ca.nom ASC
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':taille_id', $taille_id, PDO::PARAM_INT);
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html_categorie = "";

    if ($categories) {
        foreach ($categories as $categorie) {
            $html_categorie .= '
                <div class="detail_liste_filtre_produit 
                            js_detail_liste_filtre_produit 
                            js_detail_liste_filtre_produit_'.$categorie['id'].'"
                    onclick="filtre_categorie('.(int)$categorie['id'].','.ohnous_js_html_arg($categorie['nom']).','.ohnous_js_html_arg($categorie['slug']).')">
                    <div class="nom">'.$categorie['nom'].'</div>
                    <div class="nombre">'.$categorie['total'].'</div>
                </div>';
        }
    }
    /* trouver les types */
    $sql = "
        SELECT 
            t.id,
            t.nom,
            t.slug,
            COUNT(DISTINCT a.id) AS total
        FROM types t
        INNER JOIN types_article ty ON ty.types = t.id
        INNER JOIN articles a ON a.id = ty.article
        INNER JOIN boutiques bo_type ON bo_type.id = a.boutique AND bo_type.activer = 1
        INNER JOIN taille_articles ta ON ta.article = a.id
        WHERE ta.taille = :taille_id
        GROUP BY t.id
        HAVING total > 0
        ORDER BY t.nom ASC
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':taille_id', $taille_id, PDO::PARAM_INT);
    $stmt->execute();

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html_type = "";

    if ($types) {
        foreach ($types as $type) {
            $html_type .= '
                <div class="detail_liste_filtre_produit 
                            js_detail_liste_filtre_produit_types 
                            js_detail_liste_filtre_produit_types'.$type['id'].'"
                    onclick="filtre_types('.(int)$type['id'].','.ohnous_js_html_arg($type['nom']).','.ohnous_js_html_arg($type['slug']).')">
                    <div class="nom">'.$type['nom'].'</div>
                    <div class="nombre">'.$type['total'].'</div>
                </div>';
        }
    }
    /* afficher la taille en cours */
    $taille = only_select("tailles", $where = "id = '$taille_id'", $order = null, $limit = null);
    $tailles = getRowCount($bdd, "taille_articles", "taille = '$taille_id'", $limit = null, $offset = 0, $order = null);
    $html_taille = '
            <div class="detail_liste_filtre_produit
                        active
                        js_detail_liste_filtre_produit_tailles 
                        js_detail_liste_filtre_produit_tailles_'.$taille['id'].'"
                onclick="filtre_tailles('.(int)$taille['id'].','.ohnous_js_html_arg($taille['nom']).','.ohnous_js_html_arg($taille['slug']).')">
                <div class="nom">'.$taille['nom'].'</div>
                <div class="nombre">'.$tailles.'</div>
            </div>';

    echo json_encode([
        "result" => "ok",
        "msg" => $html_categorie,
        "msg2" => $html_type,
        "msg3" => $html_taille,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
