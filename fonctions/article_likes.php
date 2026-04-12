<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $action = html_entity_decode(filter_var($_POST['action'] ?? 'toggle', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $articleId = (int)html_entity_decode(filter_var($_POST['article_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if(!ohnous_table_exists('article_likes'))
    {
        createTable('article_likes', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'article_id INT NOT NULL',
            'account_id INT NOT NULL',
            'account_type VARCHAR(30) NOT NULL',
            'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    if($articleId <= 0)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Article introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $article = only_select("articles", "id = ".$articleId, null, null);
    if(!$article || !ohnous_is_article_visible($article))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Cet article n'est pas disponible."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $actor = ohnous_get_account_actor();
    if(!$actor['connected'])
    {
        echo json_encode([
            'result' => 'auth_required',
            'msg' => "Connectez-vous pour aimer cet article."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $existing = only_select(
        "article_likes",
        "article_id = ".$articleId." AND account_id = ".$actor['id']." AND account_type = '".$actor['type']."'",
        null,
        null
    );

    if($action === 'toggle')
    {
        if($existing)
        {
            $stmt = $bdd->prepare("
                DELETE FROM article_likes
                WHERE id = :id
            ");
            $stmt->execute([':id' => (int)$existing['id']]);
            $liked = false;
            $message = "L'article a été retiré de vos favoris.";
        }
        else
        {
            insert_bdd($bdd, "article_likes", [
                'article_id' => $articleId,
                'account_id' => $actor['id'],
                'account_type' => $actor['type']
            ]);
            $liked = true;
            $message = "L'article a été ajouté à vos favoris.";
        }
    }
    else
    {
        $liked = (bool)$existing;
        $message = '';
    }

    $summary = ohnous_get_article_likes_summary($articleId);

    echo json_encode([
        'result' => 'ok',
        'msg' => $message,
        'liked' => $summary['liked'],
        'count' => $summary['count'],
        'count_formatted' => $summary['count_formatted']
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
