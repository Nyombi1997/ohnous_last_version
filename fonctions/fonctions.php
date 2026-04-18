<?php
    /* add number of days */
    function ajouter_jours($date, $nb_jours)
    {
        $date_obj = new DateTime($date);
        $date_obj->modify("+$nb_jours days");
        return $date_obj->format('Y-m-d');
    }
    /* date in French */
    function date_fr($date)
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $date = ucfirst($formatter->format(new DateTime($date)));
        return $date;
    }
    /* date in English */
    function date_en($date)
    {
        setlocale(LC_TIME, 'en_EN.UTF-8', 'fra');
        $formatter = new IntlDateFormatter('en_EN', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $date = ucfirst($formatter->format(new DateTime($date)));
        return $date;
    }
    /* date difference */
    function difference_date($date1, $date2)
    {
        $start_date = new DateTime($date1);
        $end_date = new DateTime($date2);
        $difference = $start_date->diff($end_date)->days;
        return $difference;
    }
    /* date difference */
    function calculateWorkingDays(string $startDateStr, string $endDateStr, array $holidays): int
    {
        // 1. Initialization
        $workingDays = 0;

        $startDate = new DateTime($startDateStr);
        $endDate = new DateTime($endDateStr);

        // Adjust end date to include the whole day
        // If $endDateStr is '2025-06-06 23:59:59', $endDate becomes '2025-06-07 00:00:00'
        $endDate->modify('+1 day');
        $endDate->setTime(0, 0, 0);

        // 2. Prepare holidays
        $parsedHolidays = [];
        foreach ($holidays as $holiday) {
            if (is_array($holiday) && count($holiday) === 2) {
                // It's a holiday interval
                $intervalStart = new DateTime($holiday[0]);
                $intervalEnd = new DateTime($holiday[1]);
                // Loop to add each day of the interval
                $currentHolidayDate = clone $intervalStart;
                while ($currentHolidayDate <= $intervalEnd) {
                    $parsedHolidays[$currentHolidayDate->format('Y-m-d')] = true;
                    $currentHolidayDate->modify('+1 day');
                }
            } else {
                // It's a single holiday
                $parsedHolidays[(new DateTime($holiday))->format('Y-m-d')] = true;
            }
        }

        // 3. Iteration loop
        $currentDate = clone $startDate;
        while ($currentDate < $endDate) {
            $dayOfWeek = (int)$currentDate->format('N'); // 1 (Monday) to 7 (Sunday)
            $currentDateFormatted = $currentDate->format('Y-m-d');

            // Check 1: Not a weekend (Saturday or Sunday)
            if ($dayOfWeek !== 6 && $dayOfWeek !== 7) { // 6 = Saturday, 7 = Sunday
                // Check 2: Not a holiday
                if (!isset($parsedHolidays[$currentDateFormatted])) {
                    $workingDays++;
                }
            }

            // Move to next day
            $currentDate->modify('+1 day');
        }

        return $workingDays;
    }

    /* trouver les tailles */
    function fetch_tailles($produitId)
    {
        global $bdd;
        $all_tailles = select_bdd($bdd, "taille_articles", $where = "article = $produitId", $limit = null, $offset = 0, $order = null, $random = false);
        $taille = "";
        $taille_array = array();
        for($i = 0; $i < count($all_tailles); $i++)
        {
            if(in_array($all_tailles[$i]['taille'], $taille_array))
            {
                continue; // Passer à l'itération suivante si l'ID de taille a déjà été traité
            }
            $tailles = only_select("tailles", $where = "id = ".$all_tailles[$i]['taille'], $order = null, $limit = null);
            if($tailles)
            {
                if(!empty($taille))
                {
                    $taille .= ", ";
                }
                $taille .= $tailles['nom'];
                $taille_array[] = $all_tailles[$i]['taille'];
            }
        }
        return $taille;
    }
    /* id pour le panier */
    function cartKey($id, $size) {
        return $id . '_' . $size; 
    }
    /* cles de session communes du panier et du checkout */
    function ohnous_get_cart_session_key()
    {
        return 'cart-ohnous-123456789';
    }
    function ohnous_get_direct_checkout_session_key()
    {
        return 'checkout-direct-ohnous-123456789';
    }
    function ohnous_boot_checkout_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    /* retrouver les lignes du panier depuis la session existante */
    function ohnous_get_cart_items()
    {
        ohnous_boot_checkout_session();
        $items = $_SESSION[ohnous_get_cart_session_key()] ?? [];
        return is_array($items) ? $items : [];
    }
    /* totaliser un lot d'articles checkout/panier */
    function ohnous_get_items_total(array $items)
    {
        $total = 0.0;

        foreach($items as $item)
        {
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $qty = isset($item['qty']) ? max(1, (int)$item['qty']) : 1;
            $total += ($price * $qty);
        }

        return $total;
    }
    function ohnous_clear_direct_checkout()
    {
        ohnous_boot_checkout_session();
        unset($_SESSION[ohnous_get_direct_checkout_session_key()]);
    }
    /* preparer un checkout direct avec un seul article */
    function ohnous_set_direct_checkout_item(array $item)
    {
        ohnous_boot_checkout_session();

        $_SESSION[ohnous_get_direct_checkout_session_key()] = [
            'generated_at' => date('Y-m-d H:i:s'),
            'items' => [
                cartKey($item['id'] ?? 0, $item['size'] ?? '') => [
                    'id' => (int)($item['id'] ?? 0),
                    'name' => (string)($item['name'] ?? ''),
                    'price' => (float)($item['price'] ?? 0),
                    'size' => (string)($item['size'] ?? ''),
                    'qty' => max(1, (int)($item['qty'] ?? 1)),
                    'image' => (string)($item['image'] ?? ''),
                    'style' => (string)($item['style'] ?? ''),
                    'background' => (string)($item['background'] ?? ''),
                    'slug' => (string)($item['slug'] ?? '')
                ]
            ]
        ];
    }
    function ohnous_get_direct_checkout_items()
    {
        ohnous_boot_checkout_session();
        $payload = $_SESSION[ohnous_get_direct_checkout_session_key()] ?? [];
        $items = $payload['items'] ?? [];
        return is_array($items) ? $items : [];
    }
    /* resoudre le contexte du checkout : panier complet ou commande directe */
    function ohnous_get_checkout_context($mode = 'cart')
    {
        $mode = $mode === 'direct' ? 'direct' : 'cart';
        $items = $mode === 'direct' ? ohnous_get_direct_checkout_items() : ohnous_get_cart_items();

        return [
            'mode' => $mode,
            'items' => $items,
            'count' => count($items),
            'subtotal' => ohnous_get_items_total($items),
        ];
    }
    /* rendre une ligne HTML de panier/checkout */
    function ohnous_render_checkout_item_html(array $item, $compact = false)
    {
        $name = htmlspecialchars((string)($item['name'] ?? 'Article OhNous'), ENT_QUOTES, 'UTF-8');
        $slug = htmlspecialchars((string)($item['slug'] ?? ''), ENT_QUOTES, 'UTF-8');
        $size = trim((string)($item['size'] ?? ''));
        $style = htmlspecialchars((string)($item['style'] ?? ''), ENT_QUOTES, 'UTF-8');
        $background = htmlspecialchars((string)($item['background'] ?? ''), ENT_QUOTES, 'UTF-8');
        $price = isset($item['price']) ? (float)$item['price'] : 0;
        $qty = isset($item['qty']) ? max(1, (int)$item['qty']) : 1;
        $liquidImage = ohnous_prepare_liquid_image((string)($item['image'] ?? ''), '(max-width: 768px) 35vw, 180px');
        $wrapperClass = $compact ? 'checkout-order-item compact' : 'checkout-order-item';
        $subtotal = number_format($price * $qty, 2, '.', ' ');

        return '
            <article class="'.$wrapperClass.'">
                <div class="checkout-order-item__media" style="background: '.$background.';">
                    <img
                        class="blur-up js-liquid-image"
                        src="'.htmlspecialchars($liquidImage['placeholder'], ENT_QUOTES, 'UTF-8').'"
                        data-image-base="'.htmlspecialchars($liquidImage['base'], ENT_QUOTES, 'UTF-8').'"
                        data-image-fallback="'.htmlspecialchars($liquidImage['fallback'], ENT_QUOTES, 'UTF-8').'"
                        data-image-high="'.htmlspecialchars($liquidImage['high'], ENT_QUOTES, 'UTF-8').'"
                        data-image-srcset="'.htmlspecialchars($liquidImage['srcset'], ENT_QUOTES, 'UTF-8').'"
                        data-image-sizes="'.htmlspecialchars($liquidImage['sizes'], ENT_QUOTES, 'UTF-8').'"
                        loading="lazy"
                        style="'.$style.'"
                        alt="'.$slug.'"
                    >
                </div>
                <div class="checkout-order-item__content">
                    <strong>'.$name.'</strong>
                    <span>'.($size !== '' ? 'Taille : '.htmlspecialchars($size, ENT_QUOTES, 'UTF-8') : 'Taille non pr&eacute;cis&eacute;e').'</span>
                    <span>Quantit&eacute; : '.$qty.'</span>
                </div>
                <div class="checkout-order-item__price">$ '.$subtotal.'</div>
            </article>
        ';
    }
    /* gestion nombre */
    function formatNumberShort($number) {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        } else {
            return $number;
        }
    }
    /* gestion 9plus */
    function gestion_9_plus($number)
    {
        if($number>9)
        {
            return "+9";
        }
        else
        {
            return $number;
        }
    }

    /* vérifier l'existence d'une colonne pour rester compatible avec la base actuelle */
    function ohnous_column_exists($table, $column)
    {
        global $bdd;
        static $cache = [];

        $key = $table.'.'.$column;
        if(isset($cache[$key]))
        {
            return $cache[$key];
        }

        $sql = "
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND COLUMN_NAME = :column
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            ':table' => $table,
            ':column' => $column
        ]);

        $cache[$key] = ((int)$stmt->fetchColumn()) > 0;
        return $cache[$key];
    }

    /* récupérer le compte connecté en respectant la logique de session existante */
    function ohnous_get_current_account()
    {
        global $bdd;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $default = [
            'connected' => false,
            'type' => null,
            'id' => null,
            'unique_id' => null,
            'nom' => null,
            'link' => '/connexion',
            'icon_html' => '<i class="fa fa-user"></i>',
            'profile' => '',
        ];

        if(isset($_SESSION['admin_ohnous_987654321']))
        {
            $adminId = (int)$_SESSION['admin_ohnous_987654321'];
            if($adminId > 0 && ohnous_table_exists('admins'))
            {
                $admin = only_select("admins", "id = ".$adminId, null, null);
                if($admin)
                {
                    $adminProfile = ohnous_get_profile_picture($admin['profile'] ?? '', 'admin');
                    $iconHtml = '<img src="'.htmlspecialchars($adminProfile, ENT_QUOTES, 'UTF-8').'" alt="Admin OhNous">';

                    return [
                        'connected' => true,
                        'type' => 'admin',
                        'id' => (int)$admin['id'],
                        'unique_id' => (string)$admin['id'],
                        'nom' => !empty($admin['nom']) ? $admin['nom'] : 'Admin OhNous',
                        'email' => $admin['email'] ?? '',
                        'link' => '/admin',
                        'icon_html' => $iconHtml,
                        'profile' => $adminProfile,
                    ];
                }
            }
        }

        if(isset($_SESSION['store_ohnous_987654321']))
        {
            $boutique = select_bdd($bdd, "boutiques", $where = "unique_id = '".$_SESSION['store_ohnous_987654321']."'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($boutique) > 0)
            {
                $boutique = $boutique[0];
                $iconHtml = $boutique['profile'] != ""
                    ? '<img src="'.htmlspecialchars($boutique['profile'], ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8').'">'
                    : '<i class="fa-solid fa-store"></i>';

                return [
                    'connected' => true,
                    'type' => 'boutique',
                    'id' => (int)$boutique['id'],
                    'unique_id' => $boutique['unique_id'],
                    'nom' => $boutique['nom'],
                    'link' => '/boutique',
                    'icon_html' => $iconHtml,
                    'profile' => $boutique['profile'] ?? '',
                ];
            }
        }

        if(isset($_SESSION['user_ohnous_987654321']))
        {
            $user = select_bdd($bdd, "utilisateur", $where = "unique_id = '".$_SESSION['user_ohnous_987654321']."'", $limit = null, $offset = 0, $order = null, $random = false);
            if(count($user) > 0)
            {
                $user = $user[0];
                $iconHtml = $user['profile'] != ""
                    ? '<img src="'.htmlspecialchars($user['profile'], ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8').'">'
                    : '<i class="fa-regular fa-user"></i>';

                return [
                    'connected' => true,
                    'type' => 'utilisateur',
                    'id' => (int)$user['id'],
                    'unique_id' => $user['unique_id'],
                    'nom' => $user['nom'],
                    'link' => '/compte',
                    'icon_html' => $iconHtml,
                    'profile' => $user['profile'] ?? '',
                ];
            }
        }

        return $default;
    }

    /* construire l'affichage visuel des étoiles */
    function ohnous_render_stars($note = 0.0)
    {
        $note = max(0, min(5, (float)$note));
        $html = '';

        for($i = 1; $i <= 5; $i++)
        {
            if($note >= $i)
            {
                $html .= '<i class="fa-solid fa-star"></i>';
            }
            elseif($note >= ($i - 0.5))
            {
                $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
            }
            else
            {
                $html .= '<i class="fa-regular fa-star"></i>';
            }
        }

        return $html;
    }

    /* résumé des avis d'un article */
    function ohnous_get_article_rating_summary($articleId)
    {
        global $bdd;

        $stmt = $bdd->prepare("
            SELECT COUNT(*) AS total, COALESCE(AVG(note), 0) AS moyenne
            FROM notes_article
            WHERE article_id = :article_id
        ");
        $stmt->bindValue(':article_id', (int)$articleId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = isset($row['total']) ? (int)$row['total'] : 0;
        $moyenne = isset($row['moyenne']) ? (float)$row['moyenne'] : 0;

        return [
            'total' => $total,
            'total_formatted' => formatNumberShort($total),
            'moyenne' => $moyenne,
            'moyenne_formatted' => number_format($moyenne, 1, ',', ' '),
            'stars_html' => ohnous_render_stars($moyenne),
        ];
    }

    /* résoudre l'auteur d'un avis */
    function ohnous_get_review_author(array $review)
    {
        $author = [
            'nom' => 'Client OhNous',
            'type' => 'Client',
            'link' => '#',
            'profile' => '',
            'icon' => 'fa-user'
        ];

        $clientId = isset($review['client_id']) ? (int)$review['client_id'] : 0;
        $clientType = $review['client_type'] ?? '';

        if($clientId <= 0)
        {
            return $author;
        }

        if($clientType === 'utilisateur' || $clientType === '')
        {
            $user = only_select("utilisateur", "id = ".$clientId, $order = null, $limit = null);
            if($user)
            {
                return [
                    'nom' => $user['nom'] ?: 'Utilisateur OhNous',
                    'type' => 'Utilisateur',
                    'link' => '/compte',
                    'profile' => $user['profile'] ?? '',
                    'icon' => 'fa-user'
                ];
            }
        }

        if($clientType === 'boutique' || $clientType === '')
        {
            $boutique = only_select("boutiques", "id = ".$clientId, $order = null, $limit = null);
            if($boutique)
            {
                $link = '/boutique';
                if(!empty($boutique['slug']))
                {
                    $link = '/boutique/'.$boutique['slug'];
                }

                return [
                    'nom' => $boutique['nom'] ?: 'Boutique OhNous',
                    'type' => 'Boutique',
                    'link' => $link,
                    'profile' => $boutique['profile'] ?? '',
                    'icon' => 'fa-store'
                ];
            }
        }

        return $author;
    }

    /* date avis en français */
    function ohnous_format_review_date($date)
    {
        try
        {
            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
            return ucfirst($formatter->format(new DateTime($date)));
        }
        catch(Exception $e)
        {
            return $date;
        }
    }

    /* récupérer les avis d'un article */
    function ohnous_get_article_reviews($articleId, $limit = 20)
    {
        global $bdd;

        $selectComment = ohnous_column_exists('notes_article', 'commentaire')
            ? "COALESCE(commentaire, '') AS commentaire"
            : "'' AS commentaire";
        $selectType = ohnous_column_exists('notes_article', 'client_type')
            ? "COALESCE(client_type, '') AS client_type"
            : "'' AS client_type";

        $sql = "
            SELECT id, client_id, article_id, note, date_ajout, ".$selectComment.", ".$selectType."
            FROM notes_article
            WHERE article_id = :article_id
            ORDER BY date_ajout DESC, id DESC
        ";

        if($limit !== null)
        {
            $sql .= " LIMIT :limit";
        }

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':article_id', (int)$articleId, PDO::PARAM_INT);
        if($limit !== null)
        {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        $stmt->execute();

        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($reviews as &$review)
        {
            $review['author'] = ohnous_get_review_author($review);
        }

        return $reviews;
    }

    /* carte HTML d'un avis */
    function ohnous_render_single_review_html(array $review)
    {
        $author = $review['author'] ?? ohnous_get_review_author($review);
        $authorName = htmlspecialchars($author['nom'], ENT_QUOTES, 'UTF-8');
        $authorType = htmlspecialchars($author['type'], ENT_QUOTES, 'UTF-8');
        $commentaire = trim((string)($review['commentaire'] ?? ''));
        $commentaireHtml = $commentaire !== ''
            ? '<p class="review-card__comment">'.nl2br(htmlspecialchars($commentaire, ENT_QUOTES, 'UTF-8')).'</p>'
            : '<p class="review-card__comment empty">Aucun commentaire ajouté pour cet avis.</p>';

        $avatarHtml = !empty($author['profile'])
            ? '<img src="'.htmlspecialchars($author['profile'], ENT_QUOTES, 'UTF-8').'" alt="'.$authorName.'">'
            : '<span class="review-card__avatar-icon"><i class="fa-solid '.htmlspecialchars($author['icon'], ENT_QUOTES, 'UTF-8').'"></i></span>';

        $authorLinkStart = !empty($author['link']) && $author['link'] !== '#'
            ? '<a href="'.htmlspecialchars($author['link'], ENT_QUOTES, 'UTF-8').'" class="review-card__author-link">'
            : '<span class="review-card__author-link no-link">';
        $authorLinkEnd = !empty($author['link']) && $author['link'] !== '#' ? '</a>' : '</span>';

        return '
            <article class="review-card" data-review-id="'.(int)$review['id'].'">
                <div class="review-card__top">
                    <div class="review-card__identity">
                        <div class="review-card__avatar">'.$avatarHtml.'</div>
                        <div class="review-card__meta">
                            '.$authorLinkStart.'
                                <strong>'.$authorName.'</strong>
                            '.$authorLinkEnd.'
                            <span>'.$authorType.' • '.ohnous_format_review_date($review['date_ajout']).'</span>
                        </div>
                    </div>
                    <div class="review-card__rating">
                        <span class="review-card__stars">'.ohnous_render_stars((float)$review['note']).'</span>
                        <strong>'.number_format((float)$review['note'], 1, ',', ' ').'/5</strong>
                    </div>
                </div>
                '.$commentaireHtml.'
            </article>
        ';
    }

    /* liste HTML des avis */
    function ohnous_render_article_reviews_html($articleId, $limit = 20)
    {
        $reviews = ohnous_get_article_reviews($articleId, $limit);

        if(empty($reviews))
        {
            return '
                <div class="review-empty-state">
                    <div class="review-empty-state__icon"><i class="fa-regular fa-comment-dots"></i></div>
                    <p>Aucun avis pour le moment. Soyez le premier à partager votre ressenti.</p>
                </div>
            ';
        }

        $html = '';
        foreach($reviews as $review)
        {
            $html .= ohnous_render_single_review_html($review);
        }

        return $html;
    }

    /* résumé HTML des avis */
    function ohnous_render_article_rating_summary($articleId, $context = 'card')
    {
        $summary = ohnous_get_article_rating_summary($articleId);
        $contextClass = $context === 'detail' ? 'detail' : 'card';
        $label = $summary['total'] > 1 ? 'avis' : 'avis';
        $detailValue = $summary['total'] > 0 ? $summary['moyenne_formatted'].'/5' : '';
        $detailText = $summary['total'] > 0
            ? $summary['total_formatted'].' '.$label
            : '';

        return '
            <div class="ohnous-rating-summary '.$contextClass.'" data-review-summary-article="'.(int)$articleId.'" data-review-count="'.$summary['total'].'">
                <div class="ohnous-rating-summary__stars">'.$summary['stars_html'].'</div>
                <div class="ohnous-rating-summary__text">
                    <strong>'.$detailValue.'</strong>
                    <span>'.$detailText.'</span>
                </div>
            </div>
        ';
    }

    /* construire une URL ImageKit propre avec transformations */
    function ohnous_imagekit_url($url, array $transformations = [])
    {
        $url = trim((string)$url);
        if($url === '')
        {
            return '';
        }

        if(empty($transformations))
        {
            return $url;
        }

        $separator = (strpos($url, '?') === false) ? '?' : '&';

        return $url.$separator.'tr='.implode(',', $transformations);
    }

    /* préparer les URLs pour un chargement progressif et fiable des images */
    function ohnous_prepare_liquid_image($url, $sizes = '(max-width: 768px) 90vw, 600px')
    {
        $base_url = trim((string)$url);

        if($base_url === '')
        {
            return [
                'placeholder' => '',
                'fallback' => '',
                'high' => '',
                'srcset' => '',
                'sizes' => $sizes,
                'base' => '',
            ];
        }

        $placeholder = ohnous_imagekit_url($base_url, ['w-80', 'q-20']);
        $fallback = ohnous_imagekit_url($base_url, ['w-400', 'q-45']);
        $high_400 = ohnous_imagekit_url($base_url, ['w-400', 'q-82']);
        $high_800 = ohnous_imagekit_url($base_url, ['w-800', 'q-82']);
        $high_1200 = ohnous_imagekit_url($base_url, ['w-1200', 'q-85']);

        return [
            'placeholder' => $placeholder,
            'fallback' => $fallback,
            'high' => $high_800,
            'srcset' => $high_400.' 400w, '.$high_800.' 800w, '.$high_1200.' 1200w',
            'sizes' => $sizes,
            'base' => $base_url,
        ];
    }

    /* vérifier si une valeur existe dans un tableau SQL */
    function ohnous_table_exists($table)
    {
        global $bdd;
        static $cache = [];

        if(isset($cache[$table]))
        {
            return $cache[$table];
        }

        $stmt = $bdd->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
        ");
        $stmt->execute([':table' => $table]);

        $cache[$table] = ((int)$stmt->fetchColumn()) > 0;
        return $cache[$table];
    }

    /* récupérer une boutique de manière sûre */
    function ohnous_get_store_by_id($storeId)
    {
        $storeId = (int)$storeId;
        if($storeId <= 0)
        {
            return null;
        }

        $boutique = only_select("boutiques", "id = ".$storeId, null, null);
        return $boutique ?: null;
    }

    /* gÃ©nÃ©rer un slug boutique unique sans bloquer l'Ã©dition de la boutique courante */
    function ohnous_generate_unique_store_slug($name, $excludeStoreId = 0)
    {
        global $bdd;

        $excludeStoreId = (int)$excludeStoreId;
        $name = trim((string)$name);
        if($name === '')
        {
            return '';
        }

        $baseSlug = strtolower($name);
        $baseSlug = iconv('UTF-8', 'ASCII//TRANSLIT', $baseSlug);
        $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', $baseSlug);
        $baseSlug = preg_replace('/-+/', '-', (string)$baseSlug);
        $baseSlug = trim((string)$baseSlug, '-');

        if($baseSlug === '')
        {
            $baseSlug = 'boutique';
        }

        $slug = $baseSlug;
        $suffix = 1;

        while(true)
        {
            $isUsed = false;
            $tablesStmt = $bdd->query("
                SELECT TABLE_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND COLUMN_NAME = 'slug'
            ");
            $tables = $tablesStmt ? $tablesStmt->fetchAll(PDO::FETCH_COLUMN) : [];

            foreach($tables as $table)
            {
                if($table === 'boutiques' && $excludeStoreId > 0)
                {
                    $stmt = $bdd->prepare("SELECT id FROM boutiques WHERE slug = :slug AND id != :id LIMIT 1");
                    $stmt->execute([
                        ':slug' => $slug,
                        ':id' => $excludeStoreId
                    ]);
                }
                else
                {
                    $stmt = $bdd->prepare("SELECT 1 FROM `$table` WHERE slug = :slug LIMIT 1");
                    $stmt->execute([
                        ':slug' => $slug
                    ]);
                }

                if($stmt->fetch())
                {
                    $isUsed = true;
                    break;
                }
            }

            if(!$isUsed)
            {
                return $slug;
            }

            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }
    }

    /* une boutique test n’a pas d’adresse email */
    function ohnous_is_test_store($store)
    {
        if(!$store || !is_array($store))
        {
            return false;
        }

        return trim((string)($store['adresse_email'] ?? '')) === '';
    }

    /* une boutique est visible si elle est activée et si la période n'est pas expirée */
    function ohnous_is_store_active($store)
    {
        if(!$store || !is_array($store))
        {
            return false;
        }

        /* Les boutiques sans email sont des boutiques de test et doivent rester visibles. */
        if(ohnous_is_test_store($store))
        {
            return true;
        }

        if(isset($store['activer']) && (int)$store['activer'] !== 1)
        {
            return false;
        }

        if(isset($store['date_activation_fin']) && trim((string)$store['date_activation_fin']) !== '')
        {
            try
            {
                $today = new DateTime('today');
                $endDate = new DateTime($store['date_activation_fin']);
                $endDate->setTime(23, 59, 59);
                return $endDate >= $today;
            }
            catch(Exception $e)
            {
                return false;
            }
        }

        return true;
    }

    /* synchroniser en base les boutiques test pour les vues admin et les filtres */
    function ohnous_sync_test_store_activation()
    {
        global $bdd;

        static $done = false;
        if($done)
        {
            return;
        }
        $done = true;

        if(!ohnous_table_exists('boutiques'))
        {
            return;
        }

        $stmt = $bdd->prepare("
            UPDATE boutiques
            SET activer = 1
            WHERE (adresse_email IS NULL OR TRIM(adresse_email) = '')
            AND activer <> 1
        ");
        $stmt->execute();
    }

    /* vérifier la visibilité d'un article selon sa boutique */
    function ohnous_is_article_visible($article)
    {
        if(!$article || !is_array($article))
        {
            return false;
        }

        $store = ohnous_get_store_by_id($article['boutique'] ?? 0);
        return ohnous_is_store_active($store);
    }

    /* filtrer une liste d'articles en ne gardant que les boutiques actives */
    function ohnous_filter_visible_articles(array $articles)
    {
        $filtered = [];

        foreach($articles as $article)
        {
            if(ohnous_is_article_visible($article))
            {
                $filtered[] = $article;
            }
        }

        return $filtered;
    }

    /* récupérer les articles visibles avec limite logique */
    function ohnous_get_visible_articles($limit = null, $offset = 0, $order = null, $random = false)
    {
        global $bdd;

        ohnous_sync_test_store_activation();

        if($limit === null)
        {
            $articles = select_bdd($bdd, "articles", null, null, 0, $order, $random);
            $articles = ohnous_filter_visible_articles($articles);
            return array_values(array_filter($articles, function($article){
                return ohnous_get_article_primary_image((int)($article['id'] ?? 0)) !== null;
            }));
        }

        $poolLimit = max((int)$limit * 8, 48);
        $articles = select_bdd($bdd, "articles", null, $poolLimit, (int)$offset, $order, $random);
        $articles = ohnous_filter_visible_articles($articles);
        $articles = array_values(array_filter($articles, function($article){
            return ohnous_get_article_primary_image((int)($article['id'] ?? 0)) !== null;
        }));

        return array_slice($articles, 0, (int)$limit);
    }

    function ohnous_get_visible_stores($limit = null, $offset = 0, $order = "date_ajout DESC, id DESC")
    {
        global $bdd;

        ohnous_sync_test_store_activation();

        if($limit === null)
        {
            $stores = select_bdd($bdd, "boutiques", null, null, 0, $order, false);
        }
        else
        {
            $poolLimit = max((int)$limit * 6, 30);
            $stores = select_bdd($bdd, "boutiques", null, $poolLimit, (int)$offset, $order, false);
        }

        $stores = array_values(array_filter($stores, function($store){
            return ohnous_is_store_active($store);
        }));

        if($limit !== null)
        {
            return array_slice($stores, 0, (int)$limit);
        }

        return $stores;
    }

    function ohnous_get_public_store_description($description, $maxLength = 120)
    {
        $description = trim(strip_tags((string)$description));
        if($description === '')
        {
            return 'Découvrez les nouveautés de cette boutique sur OhNous.';
        }

        if(mb_strlen($description, 'UTF-8') <= $maxLength)
        {
            return $description;
        }

        return rtrim(mb_substr($description, 0, $maxLength - 1, 'UTF-8')).'…';
    }

    function ohnous_render_public_store_card(array $boutique, $return = false, $isCta = false)
    {
        if($isCta)
        {
            $html = '
                <article class="public-store-card public-store-card--cta">
                    <div class="public-store-card__orb"></div>
                    <div class="public-store-card__content">
                        <span class="public-store-card__eyebrow">Explorer</span>
                        <h3>Toutes les boutiques</h3>
                        <p>Accédez à l’espace boutiques pour découvrir encore plus d’univers.</p>
                        <a href="/boutiques" class="btn_voir_plus" role="button">Voir les boutiques <i class="fa-solid fa-arrow-right-long"></i></a>
                    </div>
                </article>
            ';

            if($return)
            {
                return $html;
            }

            echo $html;
            return;
        }

        if(!ohnous_is_store_active($boutique))
        {
            return '';
        }

        $profileUrl = ohnous_get_profile_picture($boutique['profile'] ?? '', 'boutique');
        $profileImage = ohnous_prepare_liquid_image($profileUrl, '(max-width: 768px) 32vw, 180px');
        $name = htmlspecialchars((string)($boutique['nom'] ?? 'Boutique OhNous'), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(ohnous_get_public_store_description($boutique['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $slug = trim((string)($boutique['slug'] ?? ''));
        $link = $slug !== '' ? '/boutique/'.$slug : '/boutique';

        $html = '
            <article class="public-store-card">
                <a href="'.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'" class="public-store-card__link">
                    <div class="public-store-card__visual">
                        <div class="public-store-card__glow"></div>
                        <div class="public-store-card__avatar">
                            <img
                                class="blur-up js-liquid-image"
                                src="'.htmlspecialchars($profileImage['placeholder'], ENT_QUOTES, 'UTF-8').'"
                                data-image-base="'.htmlspecialchars($profileImage['base'], ENT_QUOTES, 'UTF-8').'"
                                data-image-fallback="'.htmlspecialchars($profileImage['fallback'], ENT_QUOTES, 'UTF-8').'"
                                data-image-high="'.htmlspecialchars($profileImage['high'], ENT_QUOTES, 'UTF-8').'"
                                data-image-srcset="'.htmlspecialchars($profileImage['srcset'], ENT_QUOTES, 'UTF-8').'"
                                data-image-sizes="'.htmlspecialchars($profileImage['sizes'], ENT_QUOTES, 'UTF-8').'"
                                loading="lazy"
                                alt="'.$name.'"
                            >
                        </div>
                    </div>
                    <div class="public-store-card__content">
                        <span class="public-store-card__eyebrow">Boutique</span>
                        <h3>'.$name.'</h3>
                        <p>'.$description.'</p>
                        <span class="public-store-card__action">Voir la boutique <i class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>
            </article>
        ';

        if($return)
        {
            return $html;
        }

        echo $html;
    }

    function ohnous_article_has_relation($articleId, $table, $column, $valueId)
    {
        global $bdd;

        $articleId = (int)$articleId;
        $valueId = (int)$valueId;

        if($articleId <= 0 || $valueId <= 0)
        {
            return false;
        }

        $stmt = $bdd->prepare("SELECT COUNT(*) FROM {$table} WHERE article = :article AND {$column} = :value");
        $stmt->bindValue(':article', $articleId, PDO::PARAM_INT);
        $stmt->bindValue(':value', $valueId, PDO::PARAM_INT);
        $stmt->execute();

        return ((int)$stmt->fetchColumn()) > 0;
    }

    function ohnous_match_catalog_filters(array $article, array $filters = [])
    {
        $articleId = (int)($article['id'] ?? 0);
        if($articleId <= 0)
        {
            return false;
        }

        $categoryId = (int)($filters['category'] ?? 0);
        $typeId = (int)($filters['type'] ?? 0);
        $tailleId = (int)($filters['taille'] ?? 0);
        $boutiqueId = (int)($filters['boutique'] ?? 0);
        $priceFilter = trim((string)($filters['prix'] ?? ''));

        if($boutiqueId > 0 && (int)($article['boutique'] ?? 0) !== $boutiqueId)
        {
            return false;
        }

        if($categoryId > 0 && !ohnous_article_has_relation($articleId, 'categorie_article', 'categorie', $categoryId))
        {
            return false;
        }

        if($typeId > 0 && !ohnous_article_has_relation($articleId, 'types_article', 'types', $typeId))
        {
            return false;
        }

        if($tailleId > 0 && !ohnous_article_has_relation($articleId, 'taille_articles', 'taille', $tailleId))
        {
            return false;
        }

        if($priceFilter !== '' && !ohnous_match_price_filter($article, $priceFilter))
        {
            return false;
        }

        return true;
    }

    /* récupérer les suggestions d'articles visibles */
    function ohnous_get_article_suggestions($excludeArticleIds = [], $limit = 8)
    {
        $excludeMap = [];
        foreach($excludeArticleIds as $articleId)
        {
            $excludeMap[(int)$articleId] = true;
        }

        $suggestions = [];
        $visibleArticles = ohnous_get_visible_articles(null, 0, null, true);

        foreach($visibleArticles as $article)
        {
            if(isset($excludeMap[(int)$article['id']]))
            {
                continue;
            }

            $suggestions[] = $article;

            if(count($suggestions) >= $limit)
            {
                break;
            }
        }

        return $suggestions;
    }

    /* lecture de la configuration de livraison */
    function ohnous_get_delivery_settings()
    {
        global $bdd;

        $settings = [
            'use_global_price' => 0,
            'global_price' => 0.0
        ];

        if(!ohnous_table_exists('delivery_settings'))
        {
            return $settings;
        }

        $rows = select_bdd($bdd, 'delivery_settings', null, null, 0, null, false);
        foreach($rows as $row)
        {
            $key = (string)($row['setting_key'] ?? '');
            $value = $row['setting_value'] ?? '';

            if($key === 'use_global_price')
            {
                $settings['use_global_price'] = (int)$value === 1 ? 1 : 0;
            }
            elseif($key === 'global_price')
            {
                $settings['global_price'] = (float)$value;
            }
        }

        return $settings;
    }

    /* zones de livraison gerees par l'admin */
    function ohnous_get_delivery_zones($onlyActive = true)
    {
        global $bdd;

        if(!ohnous_table_exists('delivery_zones'))
        {
            return [];
        }

        $where = $onlyActive ? 'actif = 1' : null;
        return select_bdd($bdd, 'delivery_zones', $where, null, 0, 'nom ASC, id DESC', false);
    }

    function ohnous_get_delivery_zone_by_id($zoneId)
    {
        $zoneId = (int)$zoneId;
        if($zoneId <= 0 || !ohnous_table_exists('delivery_zones'))
        {
            return null;
        }

        $zone = only_select('delivery_zones', 'id = '.$zoneId, null, null);
        return $zone ?: null;
    }

    /* calculer les frais de livraison selon le mode admin */
    function ohnous_get_delivery_price_for_zone($zoneId)
    {
        $settings = ohnous_get_delivery_settings();
        if((int)$settings['use_global_price'] === 1)
        {
            return (float)$settings['global_price'];
        }

        $zone = ohnous_get_delivery_zone_by_id($zoneId);
        if(!$zone)
        {
            return null;
        }

        return (float)($zone['prix'] ?? 0);
    }

    /* l'identité d'un compte pour les favoris et messages */
    function ohnous_get_account_actor()
    {
        $account = ohnous_get_current_account();

        return [
            'connected' => $account['connected'],
            'type' => $account['type'],
            'id' => (int)$account['id'],
            'key' => ($account['type'] ?: 'guest').'#'.(int)$account['id']
        ];
    }

    /* compter les likes d'un article */
    function ohnous_get_article_likes_summary($articleId)
    {
        global $bdd;

        if(!ohnous_table_exists('article_likes'))
        {
            return [
                'count' => 0,
                'count_formatted' => '0',
                'liked' => false,
            ];
        }

        $articleId = (int)$articleId;
        $actor = ohnous_get_account_actor();

        $stmt = $bdd->prepare("
            SELECT COUNT(*) AS total
            FROM article_likes
            WHERE article_id = :article_id
        ");
        $stmt->execute([':article_id' => $articleId]);
        $count = (int)$stmt->fetchColumn();

        $liked = false;
        if($actor['connected'])
        {
            $likedStmt = $bdd->prepare("
                SELECT id
                FROM article_likes
                WHERE article_id = :article_id
                AND account_id = :account_id
                AND account_type = :account_type
                LIMIT 1
            ");
            $likedStmt->execute([
                ':article_id' => $articleId,
                ':account_id' => $actor['id'],
                ':account_type' => $actor['type']
            ]);
            $liked = (bool)$likedStmt->fetch(PDO::FETCH_ASSOC);
        }

        return [
            'count' => $count,
            'count_formatted' => formatNumberShort($count),
            'liked' => $liked,
        ];
    }

    /* récupérer les articles aimés du compte connecté */
    function ohnous_get_liked_articles_for_current_account()
    {
        global $bdd;

        $actor = ohnous_get_account_actor();
        if(!$actor['connected'] || !ohnous_table_exists('article_likes'))
        {
            return [];
        }

        $stmt = $bdd->prepare("
            SELECT article_id
            FROM article_likes
            WHERE account_id = :account_id
            AND account_type = :account_type
            ORDER BY date_ajout DESC, id DESC
        ");
        $stmt->execute([
            ':account_id' => $actor['id'],
            ':account_type' => $actor['type']
        ]);

        $articleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $articles = [];

        foreach($articleIds as $articleId)
        {
            $article = only_select("articles", "id = ".(int)$articleId, null, null);
            if($article && ohnous_is_article_visible($article))
            {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    /* construire le bouton like */
    function ohnous_render_like_button($articleId, $context = 'card')
    {
        $articleId = (int)$articleId;
        $likes = ohnous_get_article_likes_summary($articleId);
        $account = ohnous_get_current_account();
        $activeClass = $likes['liked'] ? 'is-liked' : '';
        $connectedClass = $account['connected'] ? 'is-connected' : 'is-guest';
        $label = $likes['liked'] ? 'Retirer des favoris' : 'Ajouter aux favoris';

        //like masquer pour le moment
        /*return '
            <button
                type="button"
                class="like_affiche_produit js-like-button '.$activeClass.' '.$connectedClass.'"
                data-article-id="'.$articleId.'"
                data-liked="'.($likes['liked'] ? '1' : '0').'"
                data-context="'.htmlspecialchars($context, ENT_QUOTES, 'UTF-8').'"
                aria-label="'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'"
                title="'.htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'"
            >
                <i class="'.($likes['liked'] ? 'fa-solid' : 'fa-regular').' fa-heart"></i>
                <span class="like_affiche_produit__count" data-like-count>'.$likes['count_formatted'].'</span>
            </button>
        ';*/
    }

    /* lien direct WhatsApp */
    function ohnous_format_whatsapp_link($number, $message = 'Bonjour, je viens de OhNous.')
    {
        $digits = preg_replace('/\D+/', '', (string)$number);
        if($digits === '')
        {
            return '';
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    /* récupérer les liens sociaux visibles d'une boutique */
    function ohnous_get_store_social_links(array $boutique)
    {
        $socials = [];
        $map = [
            'facebook' => ['icon' => 'fa-square-facebook', 'label' => 'Facebook'],
            'instagram' => ['icon' => 'fa-square-instagram', 'label' => 'Instagram'],
            'twitter' => ['icon' => 'fa-square-x-twitter', 'label' => 'X'],
            'trends' => ['icon' => 'fa-square-threads', 'label' => 'Threads'],
            'tiktok' => ['icon' => 'fa-tiktok', 'label' => 'TikTok'],
        ];

        foreach($map as $column => $meta)
        {
            if(!empty($boutique[$column]))
            {
                $socials[] = [
                    'url' => $boutique[$column],
                    'icon' => $meta['icon'],
                    'label' => $meta['label']
                ];
            }
        }

        if(!empty($boutique['telephone_whatsapp']))
        {
            $socials[] = [
                'url' => ohnous_format_whatsapp_link($boutique['telephone_whatsapp']),
                'icon' => 'fa-square-whatsapp',
                'label' => 'WhatsApp'
            ];
        }

        return $socials;
    }

    /* récupérer le nombre de messages non lus pour le compte courant */
    function ohnous_get_unread_messages_count($account = null)
    {
        global $bdd;

        if(!ohnous_table_exists('messages'))
        {
            return 0;
        }

        if($account === null)
        {
            $account = ohnous_get_current_account();
        }

        if(empty($account['connected']) || empty($account['type']) || empty($account['id']))
        {
            return 0;
        }

        if($account['type'] === 'boutique')
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM messages
                WHERE boutique_id = :id
                AND from_id = client_id
                AND lu = 0
            ");
        }
        else
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM messages
                WHERE client_id = :id
                AND from_id = boutique_id
                AND lu = 0
            ");
        }

        $stmt->execute([':id' => (int)$account['id']]);
        return (int)$stmt->fetchColumn();
    }

    /* construire les conversations pour la messagerie */
    function ohnous_get_conversations_for_current_account()
    {
        global $bdd;

        $account = ohnous_get_current_account();
        if(!$account['connected'] || !ohnous_table_exists('messages'))
        {
            return [];
        }

        if($account['type'] === 'boutique')
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE boutique_id = :id
                ORDER BY date_ajout DESC, id DESC
            ");
        }
        else
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE client_id = :id
                ORDER BY date_ajout DESC, id DESC
            ");
        }
        $stmt->execute([':id' => (int)$account['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conversations = [];

        foreach($rows as $row)
        {
            $clientId = (int)$row['client_id'];
            $boutiqueId = (int)$row['boutique_id'];
            $conversationKey = $clientId.'-'.$boutiqueId;

            if(!isset($conversations[$conversationKey]))
            {
                $boutique = only_select("boutiques", "id = ".$boutiqueId, null, null);
                $utilisateur = only_select("utilisateur", "id = ".$clientId, null, null);

                $other = $account['type'] === 'boutique' ? $utilisateur : $boutique;
                $otherType = $account['type'] === 'boutique' ? 'utilisateur' : 'boutique';
                $otherName = $other['nom'] ?? 'Compte OhNous';
                $otherProfile = ohnous_get_profile_picture($other['profile'] ?? '', $otherType);
                $otherSlug = $other['slug'] ?? '';
                $otherLink = '#';

                if($otherType === 'boutique' && $otherSlug !== '')
                {
                    $otherLink = '/boutique/'.$otherSlug;
                }
                elseif($otherType === 'utilisateur')
                {
                    $otherLink = '/compte';
                }

                $conversations[$conversationKey] = [
                    'conversation_key' => $conversationKey,
                    'client_id' => $clientId,
                    'boutique_id' => $boutiqueId,
                    'other_type' => $otherType,
                    'other_name' => $otherName,
                    'other_profile' => $otherProfile,
                    'other_slug' => $otherSlug,
                    'other_link' => $otherLink,
                    'last_message' => '',
                    'last_message_date' => '',
                    'unread_count' => 0,
                ];
            }

            if($conversations[$conversationKey]['last_message'] === '')
            {
                $conversations[$conversationKey]['last_message'] = $row['messages'];
                $conversations[$conversationKey]['last_message_date'] = $row['date_ajout'];
            }

            $isUnreadForCurrent = false;
            if($account['type'] === 'boutique' && (int)$row['from_id'] === $clientId && (int)$row['lu'] === 0)
            {
                $isUnreadForCurrent = true;
            }
            if($account['type'] === 'utilisateur' && (int)$row['from_id'] === $boutiqueId && (int)$row['lu'] === 0)
            {
                $isUnreadForCurrent = true;
            }

            if($isUnreadForCurrent)
            {
                $conversations[$conversationKey]['unread_count']++;
            }
        }

        uasort($conversations, function($a, $b){
            return strcmp($b['last_message_date'], $a['last_message_date']);
        });

        return array_values($conversations);
    }

    /* récupérer les messages d'une conversation */
    function ohnous_get_messages_for_conversation($clientId, $boutiqueId)
    {
        global $bdd;

        if(!ohnous_table_exists('messages'))
        {
            return [];
        }

        $stmt = $bdd->prepare("
            SELECT *
            FROM messages
            WHERE client_id = :client_id
            AND boutique_id = :boutique_id
            ORDER BY date_ajout ASC, id ASC
        ");
        $stmt->execute([
            ':client_id' => (int)$clientId,
            ':boutique_id' => (int)$boutiqueId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* rendre un message lu pour le compte courant */
    function ohnous_mark_conversation_as_read($clientId, $boutiqueId)
    {
        global $bdd;

        $account = ohnous_get_current_account();
        if(!$account['connected'] || !ohnous_table_exists('messages'))
        {
            return;
        }

        if($account['type'] === 'boutique')
        {
            $stmt = $bdd->prepare("
                UPDATE messages
                SET lu = 1
                WHERE client_id = :client_id
                AND boutique_id = :boutique_id
                AND from_id = client_id
            ");
        }
        else
        {
            $stmt = $bdd->prepare("
                UPDATE messages
                SET lu = 1
                WHERE client_id = :client_id
                AND boutique_id = :boutique_id
                AND from_id = boutique_id
            ");
        }

        $stmt->execute([
            ':client_id' => (int)$clientId,
            ':boutique_id' => (int)$boutiqueId
        ]);
    }

    /* construire la bulle HTML d'un message */
    function ohnous_render_message_bubble(array $message, array $account)
    {
        $isMine = (int)$message['from_id'] === (int)$account['id'];
        $class = $isMine ? 'is-mine' : 'is-theirs';
        $date = ohnous_format_review_date($message['date_ajout']);
        $content = nl2br(htmlspecialchars($message['messages'], ENT_QUOTES, 'UTF-8'));

        return '
            <article class="message-bubble '.$class.'" data-message-id="'.(int)$message['id'].'">
                <div class="message-bubble__content">'.$content.'</div>
                <span class="message-bubble__date">'.$date.'</span>
            </article>
        ';
    }

    /* image de profil par défaut selon le type de compte */
    function ohnous_get_default_profile_image($type = 'utilisateur')
    {
        $type = (string)$type;

        if($type === 'admin')
        {
            return '/asset/images/icons/favicon-1.png';
        }

        return '/asset/images/profile/default.jpg';
    }

    /* toujours renvoyer une image de profil exploitable */
    function ohnous_get_profile_picture($profile = '', $type = 'utilisateur')
    {
        $profile = trim((string)$profile);
        if($profile !== '')
        {
            return $profile;
        }

        return ohnous_get_default_profile_image($type);
    }

    /* vérifier si l'admin est connecté */
    function ohnous_is_admin()
    {
        $account = ohnous_get_current_account();
        return !empty($account['connected']) && ($account['type'] ?? '') === 'admin';
    }

    /* bloquer les accès admin côté vues */
    function ohnous_require_admin_or_redirect($redirect = '/admin-login')
    {
        if(!ohnous_is_admin())
        {
            header('Location:'.$redirect);
            exit();
        }
    }

    /* résumé des prix avec ou sans promotion */
    function ohnous_get_article_pricing(array $article)
    {
        $prix = isset($article['prix']) ? (float)$article['prix'] : 0;
        $promoActif = false;
        $promoPrix = null;

        if(
            ohnous_column_exists('articles', 'promo_actif')
            && ohnous_column_exists('articles', 'promo_prix')
            && isset($article['promo_actif'])
            && (int)$article['promo_actif'] === 1
            && isset($article['promo_prix'])
            && trim((string)$article['promo_prix']) !== ''
        ) {
            $promoActif = true;
            $promoPrix = (float)$article['promo_prix'];
        }

        return [
            'promo_actif' => $promoActif,
            'prix_initial' => $prix,
            'prix_final' => $promoActif ? $promoPrix : $prix,
            'reduction' => ($promoActif && $prix > 0 && $promoPrix !== null) ? max(0, round((1 - ($promoPrix / $prix)) * 100)) : 0,
        ];
    }

    /* prix final réellement utilisé dans le catalogue */
    function ohnous_get_article_effective_price(array $article)
    {
        $pricing = ohnous_get_article_pricing($article);
        return (float)$pricing['prix_final'];
    }

    /* bornes de filtres de prix côté catalogue */
    function ohnous_get_price_filter_ranges()
    {
        return [
            'moins-25' => ['label' => 'Moins de 25 $', 'min' => null, 'max' => 25],
            '25-50' => ['label' => '25 $ à 50 $', 'min' => 25, 'max' => 50],
            '50-100' => ['label' => '50 $ à 100 $', 'min' => 50, 'max' => 100],
            'plus-100' => ['label' => 'Plus de 100 $', 'min' => 100, 'max' => null],
        ];
    }

    /* vérifier si un article correspond au filtre prix sélectionné */
    function ohnous_match_price_filter(array $article, $priceFilter = '')
    {
        $priceFilter = trim((string)$priceFilter);
        if($priceFilter === '')
        {
            return true;
        }

        $ranges = ohnous_get_price_filter_ranges();
        if(!isset($ranges[$priceFilter]))
        {
            return true;
        }

        $price = ohnous_get_article_effective_price($article);
        $min = $ranges[$priceFilter]['min'];
        $max = $ranges[$priceFilter]['max'];

        if($min !== null && $price < (float)$min)
        {
            return false;
        }

        if($max !== null && $price > (float)$max)
        {
            return false;
        }

        return true;
    }

    /* récupérer le catalogue visible selon recherche et filtres */
    function ohnous_get_catalog_articles(array $filters = [], $search = '', $order = 'date_desc')
    {
        global $bdd;

        $search = trim((string)$search);
        $priceFilter = trim((string)($filters['prix'] ?? ''));

        $baseFilters = [
            'category' => (int)($filters['category'] ?? 0),
            'type' => (int)($filters['type'] ?? 0),
            'taille' => (int)($filters['taille'] ?? 0),
            'boutique' => (int)($filters['boutique'] ?? 0),
            'promotion' => 0
        ];

        if($search !== '')
        {
            $query = found($search, null, 0, $order, false);
            $articles = getArticlesFromSearch($query, null, 0, $order, false);
        }
        elseif($baseFilters['category'] !== 0 || $baseFilters['type'] !== 0 || $baseFilters['taille'] !== 0 || $baseFilters['boutique'] !== 0)
        {
            $articles = select_articles_filtre($bdd, $baseFilters, null, 0, $order, false);
        }
        else
        {
            $defaultOrder = $order === 'prix_desc' || $order === 'plus_chers'
                ? "prix DESC, id DESC"
                : ($order === 'prix_asc' ? "prix ASC, id DESC" : "date_ajout DESC, id DESC");
            $articles = ohnous_get_visible_articles(null, 0, $defaultOrder, false);
        }

        $articles = ohnous_filter_visible_articles($articles);

        if(
            $baseFilters['category'] !== 0
            || $baseFilters['type'] !== 0
            || $baseFilters['taille'] !== 0
            || $baseFilters['boutique'] !== 0
            || $priceFilter !== ''
        )
        {
            $articles = array_values(array_filter($articles, function($article) use ($filters){
                return ohnous_match_catalog_filters($article, $filters);
            }));
        }

        return array_values($articles);
    }

    /* savoir si un article est en promotion */
    function ohnous_is_article_on_promo($article)
    {
        if(!$article || !is_array($article))
        {
            return false;
        }

        $pricing = ohnous_get_article_pricing($article);
        return $pricing['promo_actif'] === true;
    }

    /* lien d'édition admin sur les articles publics */
    function ohnous_render_article_admin_edit_link($articleId, $context = 'card')
    {
        if(!ohnous_is_admin())
        {
            return '';
        }

        $contextClass = $context === 'detail' ? 'detail' : 'card';

        return '
        ';
    }

    /* navigation admin commune */
    function ohnous_render_admin_nav($current = 'dashboard')
    {
        $items = [
            'dashboard' => ['label' => 'Tableau de bord', 'link' => '/admin', 'icon' => 'fa-chart-line'],
            'boutiques' => ['label' => 'Boutiques', 'link' => '/admin-boutiques', 'icon' => 'fa-store'],
            'articles' => ['label' => 'Articles', 'link' => '/admin-articles', 'icon' => 'fa-tags'],
            'livraison' => ['label' => 'Livraison', 'link' => '/admin-zones-livraison', 'icon' => 'fa-truck-fast'],
            'admins' => ['label' => 'Admins', 'link' => '/admin-admins', 'icon' => 'fa-user-shield'],
        ];

        $html = '<nav class="admin-liquid-nav">';

        foreach($items as $key => $item)
        {
            $activeClass = $key === $current ? 'is-active' : '';
            $html .= '
                <a href="'.$item['link'].'" class="admin-liquid-nav__link '.$activeClass.'">
                    <i class="fa-solid '.$item['icon'].'"></i>
                    <span>'.$item['label'].'</span>
                </a>
            ';
        }

        $html .= '
            <a href="/deconnexion" class="admin-liquid-nav__link danger">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        ';

        $html .= '</nav>';
        return $html;
    }

    /* liste admin des boutiques */
    function ohnous_admin_fetch_stores($search = '', $status = 'all')
    {
        global $bdd;

        ohnous_sync_test_store_activation();

        $sql = "SELECT * FROM boutiques";
        $where = [];
        $params = [];

        $search = trim((string)$search);
        if($search !== '')
        {
            $where[] = "(nom LIKE :search OR adresse_email LIKE :search OR description LIKE :search)";
            $params[':search'] = '%'.$search.'%';
        }

        if($status === 'active')
        {
            $where[] = "(activer = 1 OR adresse_email IS NULL OR TRIM(adresse_email) = '')";
        }
        elseif($status === 'inactive')
        {
            $where[] = "(activer <> 1 AND adresse_email IS NOT NULL AND TRIM(adresse_email) <> '')";
        }
        elseif($status === 'test')
        {
            $where[] = "(adresse_email IS NULL OR TRIM(adresse_email) = '')";
        }

        if(!empty($where))
        {
            $sql .= " WHERE ".implode(' AND ', $where);
        }

        $sql .= " ORDER BY (CASE WHEN adresse_email IS NULL OR TRIM(adresse_email) = '' THEN 0 ELSE 1 END) ASC, date_ajout DESC, id DESC";

        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* liste admin des articles */
    function ohnous_admin_fetch_articles($search = '', $storeId = 0)
    {
        global $bdd;

        $sql = "
            SELECT a.*
            FROM articles a
            LEFT JOIN boutiques b ON b.id = a.boutique
            WHERE 1 = 1
        ";
        $params = [];

        if((int)$storeId > 0)
        {
            $sql .= " AND a.boutique = :store_id";
            $params[':store_id'] = (int)$storeId;
        }

        $search = trim((string)$search);
        if($search !== '')
        {
            $sql .= " AND (
                a.nom LIKE :search
                OR a.description LIKE :search
                OR a.slug LIKE :search
                OR CAST(a.prix AS CHAR) LIKE :search
                OR COALESCE(b.nom, '') LIKE :search
            )";
            $params[':search'] = '%'.$search.'%';
        }

        $sql .= " ORDER BY a.date_ajout DESC, a.id DESC";

        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* récupérer l'image principale d'un article */
    function ohnous_get_article_primary_image($articleId)
    {
        global $bdd;

        $images = select_bdd($bdd, "image_articles", "article = '".(int)$articleId."'", null, 0, "id ASC", false);
        if(empty($images))
        {
            return null;
        }

        return $images[0];
    }

    /* conversation admin <> boutique */
    function ohnous_get_admin_store_messages($storeId)
    {
        global $bdd;

        if(!ohnous_table_exists('admin_boutique_messages'))
        {
            return [];
        }

        $stmt = $bdd->prepare("
            SELECT *
            FROM admin_boutique_messages
            WHERE boutique_id = :boutique_id
            ORDER BY date_ajout ASC, id ASC
        ");
        $stmt->execute([':boutique_id' => (int)$storeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* rendu d'un message admin <> boutique */
    function ohnous_render_admin_store_message_bubble(array $message)
    {
        $fromType = $message['from_type'] ?? 'admin';
        $isAdmin = $fromType === 'admin';
        $class = $isAdmin ? 'is-mine' : 'is-theirs';
        $name = $isAdmin ? 'Admin OhNous' : 'Boutique';
        $avatar = ohnous_get_profile_picture($isAdmin ? '/asset/images/icons/favicon-1.png' : ($message['profile'] ?? ''), $isAdmin ? 'admin' : 'boutique');

        return '
            <article class="message-bubble admin-thread '.$class.'">
                <div class="message-bubble__avatar">
                    <img src="'.htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'">
                </div>
                <div class="message-bubble__content-wrap">
                    <strong>'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</strong>
                    <div class="message-bubble__content">'.nl2br(htmlspecialchars((string)$message['message'], ENT_QUOTES, 'UTF-8')).'</div>
                    <span class="message-bubble__date">'.ohnous_format_review_date($message['date_ajout']).'</span>
                </div>
            </article>
        ';
    }

    /* liste admin des comptes admins */
    function ohnous_admin_fetch_admins()
    {
        global $bdd;

        if(!ohnous_table_exists('admins'))
        {
            return [];
        }

        $stmt = $bdd->query("SELECT * FROM admins ORDER BY id DESC");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach($rows as &$row)
        {
            $row['profile_resolved'] = ohnous_get_profile_picture($row['profile'] ?? '', 'admin');
            $row['display_name'] = !empty($row['nom']) ? $row['nom'] : 'Admin OhNous';
        }

        return $rows;
    }

    /* générer un mot de passe lisible pour les comptes admins */
    function ohnous_generate_readable_password($length = 14)
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
        $password = '';
        $max = strlen($alphabet) - 1;

        for($i = 0; $i < $length; $i++)
        {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }

    /* encoder une valeur JSON pour l'injecter en sécurité dans un attribut HTML */
    function ohnous_js_html_arg($value)
    {
        return htmlspecialchars(
            json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ENT_QUOTES,
            'UTF-8'
        );
    }
?>
