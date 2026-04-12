<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $action = html_entity_decode(filter_var($_POST['action'] ?? 'fetch', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $articleId = (int)html_entity_decode(filter_var($_POST['article_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if($articleId <= 0)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Article introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $commentColumnReady = ohnous_column_exists('notes_article', 'commentaire');
    $clientTypeColumnReady = ohnous_column_exists('notes_article', 'client_type');
    $currentAccount = ohnous_get_current_account();

    /* préparer une réponse homogène pour simplifier le rafraîchissement temps réel */
    $buildPayload = function() use ($articleId, $currentAccount, $commentColumnReady, $clientTypeColumnReady) {
        $summary = ohnous_get_article_rating_summary($articleId);
        $reviews = ohnous_get_article_reviews($articleId, 20);

        return [
            'result' => 'ok',
            'connected' => $currentAccount['connected'],
            'account_type' => $currentAccount['type'],
            'schema_ready' => ($commentColumnReady && $clientTypeColumnReady),
            'summary' => [
                'average' => $summary['moyenne'],
                'average_formatted' => $summary['moyenne_formatted'],
                'count' => $summary['total'],
                'count_formatted' => $summary['total_formatted'],
            ],
            'latest_review_id' => empty($reviews) ? 0 : (int)$reviews[0]['id'],
            'summary_html' => ohnous_render_article_rating_summary($articleId, 'detail'),
            'reviews_html' => ohnous_render_article_reviews_html($articleId, 20),
        ];
    };

    if($action === 'fetch')
    {
        echo json_encode($buildPayload(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action !== 'create')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Action invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!$currentAccount['connected'])
    {
        echo json_encode([
            'result' => 'auth_required',
            'msg' => "Connectez-vous ou inscrivez-vous pour publier un avis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!$commentColumnReady || !$clientTypeColumnReady)
    {
        echo json_encode([
            'result' => 'schema_required',
            'msg' => "Une mise à jour SQL est nécessaire pour activer les commentaires d'articles."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $note = (float)html_entity_decode(filter_var($_POST['note'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $commentaire = trim((string)html_entity_decode(filter_var($_POST['commentaire'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

    if($note < 1 || $note > 5)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Choisissez une note entre 1 et 5."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($commentaire === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Ajoutez un commentaire pour publier votre avis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(mb_strlen($commentaire) > 1200)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Votre commentaire est trop long. Restez sous 1 200 caractères."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $existingStmt = $bdd->prepare("
        SELECT id
        FROM notes_article
        WHERE article_id = :article_id
        AND client_id = :client_id
        AND client_type = :client_type
        LIMIT 1
    ");
    $existingStmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
    $existingStmt->bindValue(':client_id', (int)$currentAccount['id'], PDO::PARAM_INT);
    $existingStmt->bindValue(':client_type', $currentAccount['type']);
    $existingStmt->execute();
    $existingReview = $existingStmt->fetch(PDO::FETCH_ASSOC);

    if($existingReview)
    {
        /* un même compte met à jour son avis au lieu de créer des doublons */
        update_bdd($bdd, 'notes_article', [
            'note' => $note,
            'commentaire' => $commentaire,
            'date_ajout' => date('Y-m-d H:i:s')
        ], "id = '".(int)$existingReview['id']."'");

        $payload = $buildPayload();
        $payload['msg'] = "Votre avis a été mis à jour.";
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    insert_bdd($bdd, 'notes_article', [
        'client_id' => (int)$currentAccount['id'],
        'client_type' => $currentAccount['type'],
        'article_id' => $articleId,
        'note' => $note,
        'commentaire' => $commentaire
    ]);

    $payload = $buildPayload();
    $payload['msg'] = "Votre avis a été publié.";
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
