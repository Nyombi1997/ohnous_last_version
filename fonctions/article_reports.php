<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";

    header('Content-Type: application/json; charset=utf-8');

    $currentAccount = ohnous_get_current_account();
    if(empty($currentAccount['connected']))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Connectez-vous pour signaler cet article."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    ohnous_ensure_article_reports_table();

    $articleId = (int)html_entity_decode(filter_var($_POST['article_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $motif = trim((string)html_entity_decode(filter_var($_POST['motif'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $message = trim((string)html_entity_decode(filter_var($_POST['message'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $article = $articleId > 0 ? only_select('articles', 'id = '.$articleId, null, null) : null;

    if(!$article)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Article introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $reasons = ohnous_get_article_report_reasons();
    if(!isset($reasons[$motif]))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Choisissez un motif valide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(mb_strlen($message, 'UTF-8') < 8)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Expliquez brièvement le problème."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(mb_strlen($message, 'UTF-8') > 1200)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Le message est trop long."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $existing = $bdd->prepare("
        SELECT id
        FROM article_reports
        WHERE article_id = :article_id
        AND client_type = :client_type
        AND client_id = :client_id
        AND statut = 'nouveau'
        LIMIT 1
    ");
    $existing->execute([
        ':article_id' => $articleId,
        ':client_type' => (string)$currentAccount['type'],
        ':client_id' => (int)$currentAccount['id']
    ]);

    if($existing->fetch(PDO::FETCH_ASSOC))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Vous avez déjà signalé cet article."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $boutique = only_select('boutiques', 'id = '.(int)$article['boutique'], null, null);
    $report = [
        'article_id' => $articleId,
        'boutique_id' => (int)$article['boutique'],
        'client_type' => (string)$currentAccount['type'],
        'client_id' => (int)$currentAccount['id'],
        'client_nom' => (string)($currentAccount['nom'] ?? 'Client OhNous'),
        'motif' => $reasons[$motif],
        'message' => $message
    ];

    insert_bdd($bdd, 'article_reports', $report);
    ohnous_send_article_report_admin_email($article, $boutique ?: [], $report);

    echo json_encode([
        'result' => 'ok',
        'msg' => "Signalement envoyé. Merci, l’équipe OhNous va vérifier."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
