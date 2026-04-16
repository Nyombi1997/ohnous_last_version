<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    include_once __DIR__ . "/fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    $categorie = (int)html_entity_decode(filter_var($_POST['categorie'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $types = (int)html_entity_decode(filter_var($_POST['types'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $taille = (int)html_entity_decode(filter_var($_POST['taille'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $boutique = (int)html_entity_decode(filter_var($_POST['boutique'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $prixActif = html_entity_decode(filter_var($_POST['prix'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $recherche = trim((string)html_entity_decode(filter_var($_POST['recherche'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $order = html_entity_decode(filter_var($_POST['order'] ?? 'date_desc', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $articles = ohnous_get_catalog_articles([
        'category' => $categorie,
        'type' => $types,
        'taille' => $taille,
        'boutique' => $boutique,
    ], $recherche, $order);

    $ranges = ohnous_get_price_filter_ranges();
    $html = '';

    foreach($ranges as $key => $range)
    {
        $count = 0;

        foreach($articles as $article)
        {
            if(ohnous_match_price_filter($article, $key))
            {
                $count++;
            }
        }

        if($count === 0)
        {
            continue;
        }

        $active = $prixActif === $key ? 'active' : '';
        $html .= '
            <div class="detail_liste_filtre_produit '.$active.' js_detail_liste_filtre_produit_prix js_detail_liste_filtre_produit_prix_'.htmlspecialchars($key, ENT_QUOTES, 'UTF-8').'" onclick="filtre_prix('.ohnous_js_html_arg($key).','.ohnous_js_html_arg($range['label']).')">
                <div class="nom">'.$range['label'].'</div>
                <div class="nombre">'.$count.'</div>
            </div>';
    }

    echo json_encode([
        'result' => 'ok',
        'msg' => $html,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
