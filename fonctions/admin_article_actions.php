<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if(!ohnous_is_admin())
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Accès administrateur requis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $action = trim((string)html_entity_decode(filter_var($_POST['action'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

    if($action !== 'update_article')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Action article inconnue."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $articleId = (int)html_entity_decode(filter_var($_POST['article_id'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $article = only_select('articles', 'id = '.$articleId, null, null);

    if(!$article)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Article introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $nom = trim((string)html_entity_decode(filter_var($_POST['nom'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $prix = trim((string)html_entity_decode(filter_var($_POST['prix'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $description = trim((string)html_entity_decode(filter_var($_POST['description'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $reserve = (int)html_entity_decode(filter_var($_POST['reserve'] ?? 1, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $categorie = (int)html_entity_decode(filter_var($_POST['categorie'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $types = (int)html_entity_decode(filter_var($_POST['types'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $tailles = trim((string)html_entity_decode(filter_var($_POST['tailles'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $promoActif = (int)html_entity_decode(filter_var($_POST['promo_actif'] ?? 0, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $promoPrix = trim((string)html_entity_decode(filter_var($_POST['promo_prix'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $productImagesJson = $_POST['product_images'] ?? '';

    if($nom === '' || $prix === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Le nom et le prix sont obligatoires."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $updateData = [
        'nom' => $nom,
        'prix' => $prix,
        'description' => $description,
        'reserve' => $reserve
    ];

    if($nom !== (string)$article['nom'])
    {
        $updateData['slug'] = generateSlug($nom);
    }

    if(ohnous_column_exists('articles', 'promo_actif'))
    {
        $updateData['promo_actif'] = $promoActif;
    }

    if(ohnous_column_exists('articles', 'promo_prix'))
    {
        $updateData['promo_prix'] = $promoActif === 1 ? $promoPrix : null;
    }

    update_bdd($bdd, 'articles', $updateData, "id = '".(int)$articleId."'");

    if($categorie > 0)
    {
        $bdd->prepare("DELETE FROM categorie_article WHERE article = :article")->execute([':article' => $articleId]);
        insert_bdd($bdd, 'categorie_article', [
            'article' => $articleId,
            'categorie' => $categorie
        ]);
    }

    $bdd->prepare("DELETE FROM types_article WHERE article = :article")->execute([':article' => $articleId]);
    if($types > 0)
    {
        insert_bdd($bdd, 'types_article', [
            'article' => $articleId,
            'types' => $types
        ]);
    }

    $bdd->prepare("DELETE FROM taille_articles WHERE article = :article")->execute([':article' => $articleId]);
    if($tailles !== '')
    {
        foreach(explode(',', $tailles) as $tailleId)
        {
            $tailleId = (int)$tailleId;
            if($tailleId <= 0)
            {
                continue;
            }

            insert_bdd($bdd, 'taille_articles', [
                'article' => $articleId,
                'taille' => $tailleId
            ]);
        }
    }

    $productImages = json_decode($productImagesJson, true);
    if(is_array($productImages) && !empty($productImages))
    {
        /* On remplace la galerie pour rester cohérent avec l’ordre et les suppressions faites dans l’UI. */
        $bdd->prepare("DELETE FROM image_articles WHERE article = :article")->execute([':article' => $articleId]);

        foreach($productImages as $image)
        {
            if(empty($image['url']))
            {
                continue;
            }

            $insert = [
                'article' => $articleId,
                'img' => $image['url'],
                'alt_text' => $updateData['slug'] ?? $article['slug'],
                'background' => $image['background'] ?? '',
                'styles' => $image['style'] ?? '',
            ];

            if(ohnous_column_exists('image_articles', 'fileId'))
            {
                $insert['fileId'] = $image['fileId'] ?? '';
            }

            insert_bdd($bdd, 'image_articles', $insert);
        }
    }

    $article = only_select('articles', 'id = '.$articleId, null, null);

    echo json_encode([
        'result' => 'ok',
        'msg' => "L’article a bien été mis à jour.",
        'redirect' => '/article/'.$article['slug']
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
