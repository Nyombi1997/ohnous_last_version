<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    $types_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $taille_id = 0;
    if(isset($_POST['taille']))
    {
        $taille_id = filter_input(INPUT_POST, 'taille', FILTER_VALIDATE_INT);
    }

    if (!$types_id) {
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
        INNER JOIN types_article t ON t.article = a.id
        WHERE t.types = :types_id
        GROUP BY ca.id
        HAVING total > 0
        ORDER BY ca.nom ASC
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':types_id', $types_id, PDO::PARAM_INT);
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
    /* trouver les tailles */
    $sql = "
        SELECT 
            ta.id,
            ta.nom,
            ta.slug,
            COUNT(DISTINCT a.id) AS total
        FROM tailles ta
        INNER JOIN taille_articles at ON at.taille = ta.id
        INNER JOIN articles a ON a.id = at.article
        INNER JOIN boutiques bo_taille ON bo_taille.id = a.boutique AND bo_taille.activer = 1
        INNER JOIN types_article t ON t.article = a.id
        WHERE t.types = :types_id
        GROUP BY ta.id
        HAVING total > 0
        ORDER BY ta.nom ASC
    ";

    $stmt = $bdd->prepare($sql);
    $stmt->bindValue(':types_id', $types_id, PDO::PARAM_INT);
    $stmt->execute();

    $tailles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html_taille = "";

    if ($tailles) {
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
                    onclick="filtre_tailles('.(int)$taille['id'].','.ohnous_js_html_arg($taille['nom']).','.ohnous_js_html_arg($taille['slug']).')">
                    <div class="nom">'.$taille['nom'].'</div>
                    <div class="nombre">'.$taille['total'].'</div>
                </div>';
        }
    }
    /* afficher le type en cours */
    $type = only_select("types", $where = "id = '$types_id'", $order = null, $limit = null);
    $types = getRowCount($bdd, "types_article", "types = '$types_id'", $limit = null, $offset = 0, $order = null);
    $html_type = '
            <div class="detail_liste_filtre_produit 
                        js_detail_liste_filtre_produit_types 
                        js_detail_liste_filtre_produit_types'.$type['id'].'
                        active
                        "
                onclick="filtre_types('.(int)$type['id'].','.ohnous_js_html_arg($type['nom']).','.ohnous_js_html_arg($type['slug']).')">
                <div class="nom">'.$type['nom'].'</div>
                <div class="nombre">'.$types.'</div>
            </div>';

    echo json_encode([
        "result" => "ok",
        "msg" => $html_categorie,
        "msg2" => $html_taille,
        "msg3" => $html_type,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
