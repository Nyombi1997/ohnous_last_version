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
    $deletedFileIdsJson = $_POST['deleted_fileIds'] ?? '[]';
    $productImages = json_decode($productImagesJson, true);
    $deletedFileIds = json_decode($deletedFileIdsJson, true);
    if(!is_array($deletedFileIds))
    {
        $deletedFileIds = [];
    }

    if($nom === '' || $prix === '' || $categorie <= 0)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Le nom, le prix et la catégorie sont obligatoires."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($promoActif === 1 && $promoPrix === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Entrez le prix promotionnel de l’article."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($promoActif === 1 && $promoPrix !== '' && (float)$promoPrix >= (float)$prix)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Le prix promotionnel doit être inférieur au prix normal."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!is_array($productImages) || empty($productImages))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Ajoutez au moins une image."
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
        $updateData['promo_actif'] = $promoActif === 1 ? 1 : 0;
    }

    if(ohnous_column_exists('articles', 'promo_prix'))
    {
        $updateData['promo_prix'] = $promoActif === 1 ? $promoPrix : null;
    }

    $oldArticleFileIds = ohnous_get_article_image_file_ids($articleId);

    update_bdd($bdd, 'articles', $updateData, "id = '".(int)$articleId."'");

    $bdd->prepare("DELETE FROM categorie_article WHERE article = :article")->execute([':article' => $articleId]);
    insert_bdd($bdd, 'categorie_article', [
        'article' => $articleId,
        'categorie' => $categorie
    ]);

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

    try
    {
        /* DB d'abord, suppression ImageKit seulement après synchronisation réussie. */
        $syncDeletedFileIds = ohnous_sync_article_images($articleId, $productImages, $updateData['slug'] ?? $article['slug']);
        $currentFileIds = ohnous_get_article_image_file_ids($articleId);
        $missingAfterSaveFileIds = ohnous_filter_deleted_imagekit_file_ids($oldArticleFileIds, $currentFileIds);
        $deleteFileIds = ohnous_filter_deleted_imagekit_file_ids(array_merge($syncDeletedFileIds, $deletedFileIds, $missingAfterSaveFileIds), $currentFileIds);
        $deleteResults = ohnous_delete_imagekit_file_ids($deleteFileIds);
    }
    catch(Throwable $e)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $article = only_select('articles', 'id = '.$articleId, null, null);

    echo json_encode([
        'result' => 'ok',
        'msg' => "L’article a bien été mis à jour.",
        'redirect' => '/article/'.$article['slug'],
        'imagekit_deleted_fileIds' => array_values($deleteFileIds ?? []),
        'imagekit_delete_results' => $deleteResults ?? []
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
