<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    ohnous_boot_checkout_session();

    $action = trim((string)($_POST['action'] ?? ''));

    if($action === 'prepare_direct_checkout')
    {
        $item = [
            'id' => (int)($_POST['id'] ?? 0),
            'name' => trim((string)($_POST['name'] ?? '')),
            'price' => (float)($_POST['price'] ?? 0),
            'size' => trim((string)($_POST['size'] ?? '')),
            'image' => trim((string)($_POST['image'] ?? '')),
            'style' => trim((string)($_POST['style'] ?? '')),
            'background' => trim((string)($_POST['background'] ?? '')),
            'slug' => trim((string)($_POST['slug'] ?? '')),
            'qty' => 1
        ];

        if($item['id'] <= 0 || $item['name'] === '')
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Impossible de préparer cette commande directe."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        ohnous_set_direct_checkout_item($item);

        echo json_encode([
            'result' => 'ok',
            'redirect' => '/checkout?mode=direct'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'submit_checkout')
    {
        $mode = trim((string)($_POST['mode'] ?? 'cart'));
        $context = ohnous_get_checkout_context($mode);

        if(empty($context['items']))
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Aucun article n'est disponible pour ce checkout."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if(!ohnous_table_exists('commandes') || !ohnous_table_exists('commande_articles'))
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Les tables SQL du checkout sont manquantes. Appliquez le SQL ajouté dans le README.md."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $adresse = trim((string)($_POST['adresse'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $zoneId = (int)($_POST['zone_id'] ?? 0);

        if($telephone === '' || $adresse === '' || $email === '' || $zoneId <= 0)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Veuillez renseigner le téléphone, l'adresse, l'email et la zone de livraison."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "L'adresse email saisie est invalide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $zone = ohnous_get_delivery_zone_by_id($zoneId);
        if(!$zone || (isset($zone['actif']) && (int)$zone['actif'] !== 1))
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "La zone de livraison sélectionnée est invalide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $deliveryPrice = ohnous_get_delivery_price_for_zone($zoneId);
        if($deliveryPrice === null)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Le prix de livraison n'est pas configuré pour cette zone."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $account = ohnous_get_current_account();
        $orderNumber = 'OHN-'.date('YmdHis').'-'.mt_rand(100, 999);
        $subtotal = ohnous_get_items_total($context['items']);
        $total = $subtotal + (float)$deliveryPrice;

        $insertOrder = [
            'order_number' => $orderNumber,
            'checkout_mode' => $context['mode'],
            'client_type' => $account['type'] ?? 'invite',
            'client_id' => (int)($account['id'] ?? 0),
            'nom_client' => $account['nom'] ?? 'Client OhNous',
            'telephone' => $telephone,
            'adresse' => $adresse,
            'email' => $email,
            'zone_id' => $zoneId,
            'zone_nom' => $zone['nom'] ?? '',
            'livraison_prix' => $deliveryPrice,
            'sous_total' => $subtotal,
            'total' => $total,
            'statut' => 'nouvelle'
        ];

        insert_bdd($bdd, 'commandes', $insertOrder);
        $commandeId = (int)$bdd->lastInsertId();

        foreach($context['items'] as $item)
        {
            $articleId = (int)($item['id'] ?? 0);
            $article = $articleId > 0 ? only_select('articles', 'id = '.$articleId, null, null) : null;

            insert_bdd($bdd, 'commande_articles', [
                'commande_id' => $commandeId,
                'article_id' => $articleId,
                'article_nom' => $item['name'] ?? '',
                'article_slug' => $item['slug'] ?? '',
                'taille' => $item['size'] ?? '',
                'quantite' => max(1, (int)($item['qty'] ?? 1)),
                'prix_unitaire' => (float)($item['price'] ?? 0),
                'image' => $item['image'] ?? '',
                'boutique_id' => (int)($article['boutique'] ?? 0)
            ]);
        }

        if($context['mode'] === 'direct')
        {
            ohnous_clear_direct_checkout();
        }
        else
        {
            unset($_SESSION[ohnous_get_cart_session_key()]);
        }

        echo json_encode([
            'result' => 'ok',
            'msg' => "Votre commande a bien été enregistrée.",
            'order_number' => $orderNumber,
            'redirect' => '/checkout?success=1&order='.rawurlencode($orderNumber)
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'error',
        'msg' => "Action checkout inconnue."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
