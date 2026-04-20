<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    include_once __DIR__ . "/../view/composants/fonction_produit.php";
    include_once __DIR__ . "/fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['store_ohnous_987654321']))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Vous n'êtes plus connecté."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $boutique = only_select("boutiques", "unique_id = '".$_SESSION['store_ohnous_987654321']."'", null, null);
    $boutiqueId = (int)html_entity_decode(filter_var($_POST['boutique'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $recherche = trim((string)html_entity_decode(filter_var($_POST['recherche'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $offset = isset($_POST['offset']) ? max(0, (int)$_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? max(1, (int)$_POST['limit']) : 12;

    if(!$boutique || (int)$boutique['id'] !== $boutiqueId)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Boutique introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $sql = "SELECT * FROM articles WHERE boutique = :boutique";
    $params = [':boutique' => $boutiqueId];

    if($recherche !== '')
    {
        $sql .= " AND (nom LIKE :search OR slug LIKE :search OR description LIKE :search)";
        $params[':search'] = '%'.$recherche.'%';
    }

    $sql .= " ORDER BY date_ajout DESC, id DESC";
    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);
    $allArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allArticles = array_values(array_filter($allArticles, function($article){
        return ohnous_get_article_primary_image((int)($article['id'] ?? 0)) !== null;
    }));

    $articles = array_slice($allArticles, $offset, $limit);
    $html = '';

    foreach($articles as $article)
    {
        $html .= affiche_produit($article, true, [
            'allow_hidden_for_owner' => true,
            'show_owner_actions' => true
        ]);
    }

    echo json_encode([
        'result' => 'ok',
        'msg' => $html,
        'nombre' => count($articles),
        'offset' => $offset,
        'limit' => $limit,
        'has_more' => ($offset + count($articles)) < count($allArticles),
        'total' => count($allArticles),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
