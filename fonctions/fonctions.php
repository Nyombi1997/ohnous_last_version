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
        $all_tailles = fetch_tailles_list($produitId);
        $taille = "";
        foreach($all_tailles as $item)
        {
            if(!empty($taille))
            {
                $taille .= ", ";
            }
            $taille .= $item['nom'];
        }
        return $taille;
    }
    /* trouver les tailles en liste pour les choix panier */
    function fetch_tailles_list($produitId)
    {
        global $bdd;
        $all_tailles = select_bdd($bdd, "taille_articles", $where = "article = '".(int)$produitId."'", $limit = null, $offset = 0, $order = null, $random = false);
        $taille_array = array();
        $result = array();

        for($i = 0; $i < count($all_tailles); $i++)
        {
            $tailleId = (int)$all_tailles[$i]['taille'];
            if(in_array($tailleId, $taille_array, true))
            {
                continue;
            }

            $tailles = only_select("tailles", $where = "id = ".$tailleId, $order = null, $limit = null);
            if($tailles)
            {
                $result[] = [
                    'id' => $tailleId,
                    'nom' => $tailles['nom']
                ];
                $taille_array[] = $tailleId;
            }
        }

        return $result;
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
    function ohnous_get_payment_status_path()
    {
        return CONFIG . 'payment-status.json';
    }
    function ohnous_is_payment_enabled()
    {
        $paymentConfig = include CONFIG . 'payment.php';
        $enabled = isset($paymentConfig['enabled']) ? (bool)$paymentConfig['enabled'] : true;
        $statusPath = ohnous_get_payment_status_path();

        if(is_file($statusPath))
        {
            $status = json_decode((string)file_get_contents($statusPath), true);
            if(is_array($status) && array_key_exists('enabled', $status))
            {
                $enabled = (bool)$status['enabled'];
            }
        }

        return $enabled;
    }
    function ohnous_set_payment_enabled($enabled)
    {
        $payload = [
            'enabled' => (bool)$enabled,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return file_put_contents(
            ohnous_get_payment_status_path(),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        ) !== false;
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

        $exists = ((int)$stmt->fetchColumn()) > 0;
        if($exists)
        {
            $cache[$key] = true;
        }
        return $exists;
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

    /* construire une URL absolue pour les aperçus de partage */
    function ohnous_absolute_url($url)
    {
        $url = trim((string)$url);
        if($url === '')
        {
            return '';
        }

        if(preg_match('/^https?:\/\//i', $url))
        {
            return $url;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme.'://'.$host.'/'.ltrim($url, '/');
    }

    /* récupérer jusqu'à 4 images optimisées pour les cartes de partage */
    function ohnous_get_article_share_images($articleId, $limit = 4)
    {
        $images = array_slice(ohnous_get_article_images((int)$articleId), 0, max(1, (int)$limit));
        $shareImages = [];

        foreach($images as $image)
        {
            $url = ohnous_absolute_url(ohnous_imagekit_url((string)($image['img'] ?? ''), ['w-1200', 'h-630', 'c-at_max', 'q-85']));
            if($url !== '')
            {
                $shareImages[] = $url;
            }
        }

        return $shareImages;
    }

    /* préparer les métadonnées sociales d'un article */
    function ohnous_get_article_share_meta($article)
    {
        $article = is_array($article) ? $article : [];
        $title = trim((string)($article['nom'] ?? 'Article OhNous'));
        $description = trim(strip_tags((string)($article['description'] ?? '')));
        $slug = trim((string)($article['slug'] ?? ''));

        if($description === '')
        {
            $description = 'Découvrez cet article sur OhNous.';
        }

        if(function_exists('mb_substr'))
        {
            $description = mb_substr($description, 0, 180, 'UTF-8');
        }
        else
        {
            $description = substr($description, 0, 180);
        }

        $shareUrl = '/article/'.$slug;
        if(ohnous_is_article_reserved($article))
        {
            $shareUrl .= '?commande=1';
        }

        return [
            'title' => $title,
            'description' => $description,
            'url' => ohnous_absolute_url($shareUrl),
            'images' => ohnous_get_article_share_images((int)($article['id'] ?? 0), 4),
        ];
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

    /* générer un slug boutique unique sans bloquer l'édition de la boutique courante */
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

    function ohnous_is_user_active($user)
    {
        if(!$user || !is_array($user))
        {
            return false;
        }

        if(!ohnous_column_exists('utilisateur', 'activer'))
        {
            return true;
        }

        return isset($user['activer']) && (int)$user['activer'] === 1;
    }

    function ohnous_clean_social_account($value)
    {
        $value = trim((string)$value);
        $value = preg_replace('#^https?://#i', '', $value);
        $value = preg_replace('#^(www\.)?#i', '', $value);
        $value = preg_replace('#^(instagram\.com|facebook\.com|fb\.com|tiktok\.com)/#i', '', $value);
        $value = ltrim($value, '@/');
        $value = preg_replace('/[^a-zA-Z0-9._-]/', '', $value);
        return mb_substr($value, 0, 120);
    }

    function ohnous_clean_international_phone($value)
    {
        $value = trim((string)$value);
        $value = preg_replace('/[^\d+]/', '', $value);
        if(strpos($value, '00') === 0)
        {
            $value = '+'.substr($value, 2);
        }
        if(substr_count($value, '+') > 1 || ($value !== '' && $value[0] !== '+'))
        {
            return '';
        }
        $digits = preg_replace('/\D/', '', $value);
        if(strlen($digits) < 8 || strlen($digits) > 15)
        {
            return '';
        }
        return '+'.$digits;
    }

    function ohnous_get_user_activation_status_label($status)
    {
        $labels = [
            'en_attente' => 'En attente',
            'acceptee' => 'Acceptée',
            'refusee' => 'Refusée',
        ];
        return $labels[$status] ?? 'En attente';
    }

    function ohnous_get_user_pending_activation_request($userId)
    {
        if(!ohnous_table_exists('user_activation_requests'))
        {
            return null;
        }

        return only_select("user_activation_requests", "utilisateur_id = ".(int)$userId." AND statut = 'en_attente'", "date_ajout DESC", 1);
    }

    function ohnous_get_latest_user_activation_request($userId)
    {
        if(!ohnous_table_exists('user_activation_requests'))
        {
            return null;
        }

        return only_select("user_activation_requests", "utilisateur_id = ".(int)$userId, "date_ajout DESC", 1);
    }

    function ohnous_admin_fetch_user_activation_requests()
    {
        global $bdd;

        if(!ohnous_table_exists('user_activation_requests'))
        {
            return [];
        }

        $selectActive = ohnous_column_exists('utilisateur', 'activer') ? "u.activer" : "1 AS activer";
        $stmt = $bdd->query("
            SELECT r.*, u.nom, u.adresse_email, u.profile, ".$selectActive."
            FROM user_activation_requests r
            INNER JOIN utilisateur u ON u.id = r.utilisateur_id
            ORDER BY r.date_ajout DESC, r.id DESC
        ");

        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    function ohnous_ensure_store_activation_request_schema()
    {
        global $bdd;

        if(!ohnous_table_exists('boutique_activation_requests'))
        {
            createTable('boutique_activation_requests', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'boutique_id INT NOT NULL',
                'token TEXT NULL',
                'whatsapp VARCHAR(40) NULL',
                'telephone VARCHAR(40) NULL',
                'instagram VARCHAR(120) NULL',
                'facebook VARCHAR(120) NULL',
                'tiktok VARCHAR(120) NULL',
                'statut VARCHAR(30) NOT NULL DEFAULT "en_attente"',
                'duree_jours INT NOT NULL DEFAULT 0',
                'date_traitement DATETIME NULL',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);
            return;
        }

        $columns = [
            'whatsapp' => 'VARCHAR(40) NULL AFTER token',
            'telephone' => 'VARCHAR(40) NULL AFTER whatsapp',
            'instagram' => 'VARCHAR(120) NULL AFTER telephone',
            'facebook' => 'VARCHAR(120) NULL AFTER instagram',
            'tiktok' => 'VARCHAR(120) NULL AFTER facebook',
        ];

        foreach($columns as $column => $definition)
        {
            if(!ohnous_column_exists('boutique_activation_requests', $column))
            {
                $bdd->exec("ALTER TABLE boutique_activation_requests ADD ".$column." ".$definition);
            }
        }
    }

    function ohnous_get_store_activation_status_label($status)
    {
        $labels = [
            'en_attente' => 'En attente',
            'traitee' => 'Traitée',
            'refusee' => 'Refusée',
        ];
        return $labels[$status] ?? 'En attente';
    }

    function ohnous_get_store_pending_activation_request($storeId)
    {
        ohnous_ensure_store_activation_request_schema();
        return only_select("boutique_activation_requests", "boutique_id = ".(int)$storeId." AND statut = 'en_attente'", "date_ajout DESC", 1);
    }

    function ohnous_get_latest_store_activation_request($storeId)
    {
        ohnous_ensure_store_activation_request_schema();
        return only_select("boutique_activation_requests", "boutique_id = ".(int)$storeId, "date_ajout DESC", 1);
    }

    function ohnous_admin_fetch_store_activation_requests()
    {
        global $bdd;

        ohnous_ensure_store_activation_request_schema();
        $selectActive = ohnous_column_exists('boutiques', 'activer') ? "b.activer" : "0 AS activer";
        $stmt = $bdd->query("
            SELECT r.*, b.nom, b.adresse_email, b.profile, b.description, ".$selectActive."
            FROM boutique_activation_requests r
            INNER JOIN boutiques b ON b.id = r.boutique_id
            ORDER BY r.date_ajout DESC, r.id DESC
        ");

        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    function ohnous_send_welcome_email_once(array $account, $type = 'utilisateur')
    {
        global $bdd;

        if(!ohnous_table_exists('bienvenue_email'))
        {
            return false;
        }

        $uniqueId = (string)($account['unique_id'] ?? '');
        $email = trim((string)($account['adresse_email'] ?? ''));

        if($uniqueId === '' || $email === '')
        {
            return false;
        }

        $exists = only_select("bienvenue_email", "client_unique_id = '".addslashes($uniqueId)."'", null, null);
        if($exists)
        {
            return false;
        }

        $isActive = $type === 'boutique' ? ohnous_is_store_active($account) : ohnous_is_user_active($account);
        $activationUrl = $type === 'boutique' ? 'https://ohnous.store/activer-boutique' : 'https://ohnous.store/activation-compte';

        if(welcome($email, $isActive, (string)($account['nom'] ?? ''), $activationUrl))
        {
            insert_bdd($bdd, "bienvenue_email", [
                "client_unique_id" => $uniqueId
            ]);
            return true;
        }

        return false;
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

    /* vérifier si un article est réservé */
    function ohnous_is_article_reserved($article)
    {
        if(!$article || !is_array($article))
        {
            return false;
        }

        return isset($article['reserve']) && (int)$article['reserve'] !== 1;
    }

    /* vérifier la visibilité publique d'un article selon sa boutique et sa réservation */
    function ohnous_is_article_visible($article)
    {
        if(!$article || !is_array($article))
        {
            return false;
        }

        if(ohnous_is_article_reserved($article))
        {
            return false;
        }

        $store = ohnous_get_store_by_id($article['boutique'] ?? 0);
        return ohnous_is_store_active($store);
    }

    /* vérifier si la page détail d'un article peut être ouverte */
    function ohnous_can_view_article_details($article)
    {
        if(ohnous_is_article_visible($article) || ohnous_is_admin() || ohnous_can_manage_article($article))
        {
            return true;
        }

        if(ohnous_is_article_reserved($article) && isset($_GET['commande']) && (string)$_GET['commande'] === '1')
        {
            $store = ohnous_get_store_by_id($article['boutique'] ?? 0);
            return ohnous_is_store_active($store);
        }

        return false;
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

    function ohnous_get_public_store_description($description, $maxLength = 56)
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

        return rtrim(mb_substr($description, 0, max(0, $maxLength - 3), 'UTF-8')).'...';
    }

    function ohnous_render_public_store_card(array $boutique, $return = false, $isCta = false)
    {
        if($isCta)
        {
            $html = '
                <article class="public-store-card public-store-card--cta">
                    <a href="/boutiques" class="public-store-card__link">
                        <div class="public-store-card__visual public-store-card__visual--cta">
                            <div class="public-store-card__orb"></div>
                            <div class="public-store-card__avatar public-store-card__avatar--cta">
                                <i class="fa-solid fa-store"></i>
                            </div>
                        </div>
                        <div class="public-store-card__content">
                            <span class="public-store-card__eyebrow">Explorer</span>
                            <h3>Voir toutes les boutiques</h3>
                            <p>Retrouvez plus de boutiques et accédez vite aux articles.</p>
                        </div>
                        <div class="public-store-card__aside">
                            <span class="public-store-card__action">Tout voir <i class="fa-solid fa-arrow-right-long"></i></span>
                        </div>
                    </a>
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
                    </div>
                    <div class="public-store-card__aside">
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

        return $socials;
    }

    /* récupérer le nombre de messages non lus pour le compte courant */
    function ohnous_ensure_messages_chat_columns()
    {
        global $bdd;
        static $done = false;

        if($done)
        {
            return;
        }

        $tableStmt = $bdd->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'messages'
        ");
        $tableStmt->execute();

        if((int)$tableStmt->fetchColumn() === 0)
        {
            return;
        }

        $columns = [
            'conversation_type' => "ALTER TABLE messages ADD conversation_type VARCHAR(30) NOT NULL DEFAULT 'boutique'",
            'client_type' => "ALTER TABLE messages ADD client_type VARCHAR(30) NOT NULL DEFAULT 'utilisateur'",
            'from_type' => "ALTER TABLE messages ADD from_type VARCHAR(30) NOT NULL DEFAULT 'utilisateur'"
        ];

        foreach($columns as $column => $sql)
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'messages'
                AND COLUMN_NAME = :column
            ");
            $stmt->execute([':column' => $column]);

            if((int)$stmt->fetchColumn() === 0)
            {
                $bdd->exec($sql);
            }
        }

        $bdd->exec("
            UPDATE messages
            SET conversation_type = 'boutique',
                client_type = 'utilisateur',
                from_type = CASE
                    WHEN from_id = boutique_id AND boutique_id > 0 THEN 'boutique'
                    ELSE 'utilisateur'
                END
            WHERE conversation_type = 'boutique'
            AND boutique_id > 0
        ");

        $done = true;
    }

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

        ohnous_ensure_messages_chat_columns();

        if($account['type'] === 'boutique')
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM messages
                WHERE lu = 0
                AND (
                    (boutique_id = :id AND conversation_type = 'boutique' AND from_type = 'utilisateur')
                    OR (conversation_type = 'admin' AND client_type = 'boutique' AND client_id = :id AND from_type = 'admin')
                )
            ");
        }
        elseif($account['type'] === 'admin')
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM messages
                WHERE conversation_type = 'admin'
                AND from_type != 'admin'
                AND lu = 0
            ");
        }
        else
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM messages
                WHERE lu = 0
                AND (
                    (client_id = :id AND conversation_type = 'boutique' AND from_type = 'boutique')
                    OR (conversation_type = 'admin' AND client_type = 'utilisateur' AND client_id = :id AND from_type = 'admin')
                )
            ");
        }

        $account['type'] === 'admin' ? $stmt->execute() : $stmt->execute([':id' => (int)$account['id']]);
        return (int)$stmt->fetchColumn();
    }

    /* préparer l'aperçu propre d'une conversation */
    function ohnous_get_conversation_message_preview($messageText)
    {
        $messageText = (string)$messageText;
        $articleIds = ohnous_get_message_article_ids($messageText);
        $cleanText = trim(preg_replace('/\[\[article:\d+\]\]/', '', $messageText));
        $articleName = '';

        if(!empty($articleIds))
        {
            $article = only_select("articles", "id = ".(int)$articleIds[0], null, null);
            $articleName = $article['nom'] ?? 'Article OhNous';
        }

        if($cleanText !== '' && $articleName !== '')
        {
            $preview = $cleanText.' · '.$articleName;
        }
        elseif($articleName !== '')
        {
            $preview = $articleName;
        }
        else
        {
            $preview = $cleanText;
        }

        return [
            'text' => $preview,
            'has_article' => !empty($articleIds),
            'article_name' => $articleName
        ];
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

        ohnous_ensure_messages_chat_columns();

        if($account['type'] === 'boutique')
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE (conversation_type = 'boutique' AND boutique_id = :id)
                OR (conversation_type = 'admin' AND client_type = 'boutique' AND client_id = :id)
                ORDER BY date_ajout DESC, id DESC
            ");
        }
        elseif($account['type'] === 'admin')
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE conversation_type = 'admin'
                ORDER BY date_ajout DESC, id DESC
            ");
        }
        else
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE (conversation_type = 'boutique' AND client_id = :id)
                OR (conversation_type = 'admin' AND client_type = 'utilisateur' AND client_id = :id)
                ORDER BY date_ajout DESC, id DESC
            ");
        }
        $account['type'] === 'admin' ? $stmt->execute() : $stmt->execute([':id' => (int)$account['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conversations = [];

        foreach($rows as $row)
        {
            $clientId = (int)$row['client_id'];
            $boutiqueId = (int)$row['boutique_id'];
            $conversationType = (string)($row['conversation_type'] ?? 'boutique');
            $clientType = (string)($row['client_type'] ?? 'utilisateur');
            $conversationKey = $conversationType === 'admin'
                ? 'admin-'.$clientType.'-'.$clientId
                : $clientId.'-'.$boutiqueId;

            if(!isset($conversations[$conversationKey]))
            {
                if($conversationType === 'admin')
                {
                    $source = $clientType === 'boutique'
                        ? only_select("boutiques", "id = ".$clientId, null, null)
                        : only_select("utilisateur", "id = ".$clientId, null, null);
                    $other = $account['type'] === 'admin' ? $source : ['nom' => 'Admin OhNous', 'profile' => '/asset/images/icons/favicon-1.png', 'slug' => ''];
                    $otherType = $account['type'] === 'admin' ? $clientType : 'admin';
                }
                else
                {
                    $boutique = only_select("boutiques", "id = ".$boutiqueId, null, null);
                    $utilisateur = only_select("utilisateur", "id = ".$clientId, null, null);
                    $other = $account['type'] === 'boutique' ? $utilisateur : $boutique;
                    $otherType = $account['type'] === 'boutique' ? 'utilisateur' : 'boutique';
                }

                $otherName = $other['nom'] ?? 'Compte OhNous';
                $otherProfile = ohnous_get_profile_picture($other['profile'] ?? '', $otherType);
                $otherSlug = $other['slug'] ?? '';
                $otherLink = '#';

                if($otherType === 'boutique' && $otherSlug !== '')
                {
                    $otherLink = '/boutique/'.$otherSlug;
                }
                elseif($otherType === 'utilisateur' && $otherSlug !== '')
                {
                    $otherLink = '/utilisateur/'.$otherSlug;
                }

                $conversations[$conversationKey] = [
                    'conversation_key' => $conversationKey,
                    'conversation_type' => $conversationType,
                    'client_type' => $clientType,
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
                $messagePreview = ohnous_get_conversation_message_preview($row['messages']);
                $conversations[$conversationKey]['last_message'] = $row['messages'];
                $conversations[$conversationKey]['last_message_preview'] = $messagePreview['text'];
                $conversations[$conversationKey]['last_message_has_article'] = $messagePreview['has_article'];
                $conversations[$conversationKey]['last_message_article_name'] = $messagePreview['article_name'];
                $conversations[$conversationKey]['last_message_date'] = $row['date_ajout'];
            }

            $isUnreadForCurrent = false;
            if($account['type'] === 'admin' && $conversationType === 'admin' && ($row['from_type'] ?? '') !== 'admin' && (int)$row['lu'] === 0)
            {
                $isUnreadForCurrent = true;
            }
            if($account['type'] === 'boutique' && $conversationType === 'boutique' && ($row['from_type'] ?? '') === 'utilisateur' && (int)$row['lu'] === 0)
            {
                $isUnreadForCurrent = true;
            }
            if($account['type'] === 'utilisateur' && $conversationType === 'boutique' && ($row['from_type'] ?? '') === 'boutique' && (int)$row['lu'] === 0)
            {
                $isUnreadForCurrent = true;
            }
            if($account['type'] !== 'admin' && $conversationType === 'admin' && ($row['from_type'] ?? '') === 'admin' && (int)$row['lu'] === 0)
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
    function ohnous_get_messages_for_conversation($clientId, $boutiqueId, $conversationType = 'boutique', $clientType = 'utilisateur')
    {
        global $bdd;

        if(!ohnous_table_exists('messages'))
        {
            return [];
        }

        ohnous_ensure_messages_chat_columns();

        if($conversationType === 'admin')
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE conversation_type = 'admin'
                AND client_type = :client_type
                AND client_id = :client_id
                ORDER BY date_ajout ASC, id ASC
            ");
            $stmt->execute([
                ':client_type' => $clientType,
                ':client_id' => (int)$clientId
            ]);
        }
        else
        {
            $stmt = $bdd->prepare("
                SELECT *
                FROM messages
                WHERE conversation_type = 'boutique'
                AND client_id = :client_id
                AND boutique_id = :boutique_id
                ORDER BY date_ajout ASC, id ASC
            ");
            $stmt->execute([
                ':client_id' => (int)$clientId,
                ':boutique_id' => (int)$boutiqueId
            ]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* extraire les articles tagués dans un message */
    function ohnous_get_message_article_ids($messageText)
    {
        preg_match_all('/\[\[article:(\d+)\]\]/', (string)$messageText, $matches);
        return array_values(array_unique(array_map('intval', $matches[1] ?? [])));
    }

    /* extraire les liens présents dans un message */
    function ohnous_get_message_links($messageText)
    {
        preg_match_all('/https?:\/\/[^\s<>"\']+/i', (string)$messageText, $matches);
        return array_values(array_unique($matches[0] ?? []));
    }

    /* récupérer les informations compactes d'un article pour le chat */
    function ohnous_get_chat_article_card_data($articleId)
    {
        $articleId = (int)$articleId;
        if($articleId <= 0)
        {
            return null;
        }

        $article = only_select("articles", "id = ".$articleId, null, null);
        if(!$article)
        {
            return null;
        }

        $boutique = only_select("boutiques", "id = ".(int)$article['boutique'], null, null);
        $image = ohnous_get_article_primary_image($articleId);
        $pricing = ohnous_get_article_pricing($article);

        return [
            'id' => $articleId,
            'nom' => (string)($article['nom'] ?? 'Article OhNous'),
            'slug' => (string)($article['slug'] ?? ''),
            'prix' => (float)$pricing['prix_final'],
            'taille' => trim((string)fetch_tailles($articleId)),
            'image' => (string)($image['img'] ?? ''),
            'boutique_id' => (int)($article['boutique'] ?? 0),
            'boutique_nom' => (string)($boutique['nom'] ?? 'Boutique OhNous'),
            'boutique_slug' => (string)($boutique['slug'] ?? ''),
        ];
    }

    /* carte article taguée dans un message */
    function ohnous_render_chat_article_card(array $articleData, $currentBoutiqueId = 0)
    {
        $articleLink = $articleData['slug'] !== '' ? '/article/'.$articleData['slug'] : '#';
        $storeLabel = ((int)$currentBoutiqueId > 0 && (int)$currentBoutiqueId === (int)$articleData['boutique_id'])
            ? ''
            : '<span>Boutique : '.htmlspecialchars($articleData['boutique_nom'], ENT_QUOTES, 'UTF-8').'</span>';
        $image = trim((string)$articleData['image']) !== '' ? $articleData['image'] : '/asset/images/profile/default.jpg';
        $taille = trim((string)$articleData['taille']) !== '' ? $articleData['taille'] : 'Non précisée';

        return '
            <a class="message-article-card" href="'.htmlspecialchars($articleLink, ENT_QUOTES, 'UTF-8').'">
                <img src="'.htmlspecialchars($image, ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars($articleData['nom'], ENT_QUOTES, 'UTF-8').'">
                <span class="message-article-card__body">
                    <strong>'.htmlspecialchars($articleData['nom'], ENT_QUOTES, 'UTF-8').'</strong>
                    <span>'.number_format((float)$articleData['prix'], 2, '.', ' ').' USD</span>
                    <span>Taille : '.htmlspecialchars($taille, ENT_QUOTES, 'UTF-8').'</span>
                    '.$storeLabel.'
                </span>
            </a>
        ';
    }

    /* panneau droit de la conversation */
    function ohnous_get_chat_report_html(array $messages, array $account, $selectedBoutiqueId = 0)
    {
        $articles = [];
        $links = [];
        $currentAccountId = (int)($account['id'] ?? 0);
        $currentAccountType = (string)($account['type'] ?? '');

        foreach($messages as $message)
        {
            $isMine = ($currentAccountType === 'boutique' && (int)$message['from_id'] === (int)$message['boutique_id'])
                || ($currentAccountType === 'utilisateur' && (int)$message['from_id'] === (int)$message['client_id']);

            if($isMine || $currentAccountId <= 0)
            {
                continue;
            }

            foreach(ohnous_get_message_article_ids($message['messages'] ?? '') as $articleId)
            {
                $articleData = ohnous_get_chat_article_card_data($articleId);
                if($articleData)
                {
                    $articles[$articleId] = $articleData;
                }
            }

            foreach(ohnous_get_message_links($message['messages'] ?? '') as $link)
            {
                $links[$link] = $link;
            }
        }

        $html = '';

        if(empty($articles) && empty($links))
        {
            return '<div class="empty-liquid-state compact"><div class="empty-liquid-state__icon"><i class="fa-regular fa-folder-open"></i></div><p>Aucun article ou lien reçu.</p></div>';
        }

        foreach($articles as $articleData)
        {
            $html .= ohnous_render_chat_article_card($articleData, $selectedBoutiqueId);
        }

        foreach($links as $link)
        {
            $host = parse_url($link, PHP_URL_HOST) ?: $link;
            $html .= '
                <a class="message-link-card" href="'.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener">
                    <i class="fa-solid fa-link"></i>
                    <span>'.htmlspecialchars($host, ENT_QUOTES, 'UTF-8').'</span>
                </a>
            ';
        }

        return $html;
    }

    /* chercher des articles à taguer avec @ */
    function ohnous_search_chat_articles($query, $selectedBoutiqueId = 0, $limit = 8)
    {
        global $bdd;

        $query = trim((string)$query);
        if($query === '')
        {
            return [];
        }

        $account = ohnous_get_current_account();
        $params = [
            ':query' => '%'.$query.'%',
        ];
        $where = "a.nom LIKE :query";

        if(($account['type'] ?? '') === 'boutique')
        {
            $where .= " AND a.boutique = :boutique_id";
            $params[':boutique_id'] = (int)$account['id'];
        }
        elseif((int)$selectedBoutiqueId > 0)
        {
            $where .= " AND a.boutique = :boutique_id";
            $params[':boutique_id'] = (int)$selectedBoutiqueId;
        }

        $stmt = $bdd->prepare("
            SELECT a.id
            FROM articles a
            INNER JOIN boutiques b ON b.id = a.boutique
            WHERE ".$where."
            ORDER BY a.date_ajout DESC, a.id DESC
            LIMIT ".(int)$limit
        );
        $stmt->execute($params);

        $items = [];
        foreach($stmt->fetchAll(PDO::FETCH_COLUMN) as $articleId)
        {
            $articleData = ohnous_get_chat_article_card_data((int)$articleId);
            if($articleData)
            {
                $items[] = $articleData;
            }
        }

        return $items;
    }

    /* chercher une personne, boutique ou l'admin pour démarrer un chat */
    function ohnous_search_chat_recipients($query, $limit = 10)
    {
        global $bdd;

        $account = ohnous_get_current_account();
        $query = trim((string)$query);
        $items = [];

        if(($account['type'] ?? '') !== 'admin')
        {
            $items[] = [
                'label' => 'Admin OhNous',
                'type' => 'admin',
                'profile' => ohnous_get_profile_picture('/asset/images/icons/favicon-1.png', 'admin'),
                'url' => '/message?admin=1'
            ];
        }

        $like = $query === '' ? '%' : '%'.$query.'%';

        if(($account['type'] ?? '') === 'utilisateur')
        {
            $stmt = $bdd->prepare("
                SELECT id, nom, profile
                FROM boutiques
                WHERE nom LIKE :q
                OR adresse_email LIKE :q
                OR description LIKE :q
                ORDER BY nom ASC
                LIMIT ".(int)$limit
            );
            $stmt->execute([':q' => $like]);
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
            {
                $items[] = [
                    'label' => (string)$row['nom'],
                    'type' => 'boutique',
                    'profile' => ohnous_get_profile_picture($row['profile'] ?? '', 'boutique'),
                    'url' => '/message?client='.(int)$account['id'].'&boutique='.(int)$row['id']
                ];
            }
        }
        elseif(($account['type'] ?? '') === 'boutique')
        {
            $stmt = $bdd->prepare("
                SELECT id, nom, profile
                FROM utilisateur
                WHERE nom LIKE :q
                OR adresse_email LIKE :q
                OR description LIKE :q
                ORDER BY nom ASC
                LIMIT ".(int)$limit
            );
            $stmt->execute([':q' => $like]);
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
            {
                $items[] = [
                    'label' => (string)$row['nom'],
                    'type' => 'utilisateur',
                    'profile' => ohnous_get_profile_picture($row['profile'] ?? '', 'utilisateur'),
                    'url' => '/message?client='.(int)$row['id'].'&boutique='.(int)$account['id']
                ];
            }
        }
        elseif(($account['type'] ?? '') === 'admin')
        {
            foreach(['utilisateur' => 'utilisateur', 'boutique' => 'boutiques'] as $type => $table)
            {
                $stmt = $bdd->prepare("
                    SELECT id, nom, profile
                    FROM ".$table."
                    WHERE nom LIKE :q
                    OR adresse_email LIKE :q
                    OR description LIKE :q
                    ORDER BY nom ASC
                    LIMIT ".(int)$limit
                );
                $stmt->execute([':q' => $like]);
                foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
                {
                    $items[] = [
                        'label' => (string)$row['nom'],
                        'type' => $type,
                        'profile' => ohnous_get_profile_picture($row['profile'] ?? '', $type),
                        'url' => '/message?admin=1&client_type='.$type.'&client='.(int)$row['id']
                    ];
                }
            }
        }

        return $items;
    }

    /* rendre un message lu pour le compte courant */
    function ohnous_mark_conversation_as_read($clientId, $boutiqueId, $conversationType = 'boutique', $clientType = 'utilisateur')
    {
        global $bdd;

        $account = ohnous_get_current_account();
        if(!$account['connected'] || !ohnous_table_exists('messages'))
        {
            return;
        }

        ohnous_ensure_messages_chat_columns();

        if($conversationType === 'admin')
        {
            if($account['type'] === 'admin')
            {
                $stmt = $bdd->prepare("
                    UPDATE messages
                    SET lu = 1
                    WHERE conversation_type = 'admin'
                    AND client_type = :client_type
                    AND client_id = :client_id
                    AND from_type != 'admin'
                ");
            }
            else
            {
                $stmt = $bdd->prepare("
                    UPDATE messages
                    SET lu = 1
                    WHERE conversation_type = 'admin'
                    AND client_type = :client_type
                    AND client_id = :client_id
                    AND from_type = 'admin'
                ");
            }

            $stmt->execute([
                ':client_type' => $clientType,
                ':client_id' => (int)$clientId
            ]);
            return;
        }

        if($account['type'] === 'boutique')
        {
            $stmt = $bdd->prepare("
                UPDATE messages
                SET lu = 1
                WHERE client_id = :client_id
                AND boutique_id = :boutique_id
                AND conversation_type = 'boutique'
                AND from_type = 'utilisateur'
            ");
        }
        else
        {
            $stmt = $bdd->prepare("
                UPDATE messages
                SET lu = 1
                WHERE client_id = :client_id
                AND boutique_id = :boutique_id
                AND conversation_type = 'boutique'
                AND from_type = 'boutique'
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
        $isMine = ($message['from_type'] ?? '') === ($account['type'] ?? '')
            && (int)$message['from_id'] === (int)($account['id'] ?? 0);
        $class = $isMine ? 'is-mine' : 'is-theirs';
        $date = ohnous_format_review_date($message['date_ajout']);
        $messageText = (string)$message['messages'];
        $articleIds = ohnous_get_message_article_ids($messageText);
        $cleanText = trim(preg_replace('/\[\[article:\d+\]\]/', '', $messageText));
        $content = $cleanText !== '' ? nl2br(htmlspecialchars($cleanText, ENT_QUOTES, 'UTF-8')) : '';
        $cards = '';

        foreach($articleIds as $articleId)
        {
            $articleData = ohnous_get_chat_article_card_data($articleId);
            if($articleData)
            {
                $cards .= ohnous_render_chat_article_card($articleData, (int)($message['boutique_id'] ?? 0));
            }
        }

        return '
            <article class="message-bubble '.$class.'" data-message-id="'.(int)$message['id'].'">
                '.($content !== '' ? '<div class="message-bubble__content">'.$content.'</div>' : '').'
                '.$cards.'
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

    /* vérifier si une boutique connectée peut gérer un article */
    function ohnous_can_manage_article($article)
    {
        if(!$article || !is_array($article))
        {
            return false;
        }

        $account = ohnous_get_current_account();
        return !empty($account['connected'])
            && ($account['type'] ?? '') === 'boutique'
            && (int)($account['id'] ?? 0) === (int)($article['boutique'] ?? 0);
    }

    /* bloquer les accès boutique côté vues */
    function ohnous_require_store_or_redirect($redirect = '/connexion')
    {
        $account = ohnous_get_current_account();
        if(empty($account['connected']) || ($account['type'] ?? '') !== 'boutique')
        {
            header('Location:'.$redirect);
            exit();
        }
    }

    /* actions propriétaire sur les cartes d'articles */
    function ohnous_render_store_article_manage_actions($article, $context = 'card')
    {
        if(!ohnous_can_manage_article($article))
        {
            return '';
        }

        $articleId = (int)($article['id'] ?? 0);
        if($articleId <= 0)
        {
            return '';
        }

        $contextClass = $context === 'detail' ? 'detail' : 'card';

        return '
            <div class="store-article-actions '.$contextClass.'">
                <a href="/editer-article?id='.$articleId.'" class="store-article-actions__btn edit" aria-label="Modifier l\'article">
                    <i class="fa-solid fa-pen"></i>
                    <span>Modifier</span>
                </a>
                <button type="button" class="store-article-actions__btn delete js-store-delete-article" data-article-id="'.$articleId.'" data-article-name="'.htmlspecialchars((string)($article['nom'] ?? 'Article'), ENT_QUOTES, 'UTF-8').'" aria-label="Supprimer l\'article">
                    <i class="fa-solid fa-trash"></i>
                    <span>Supprimer</span>
                </button>
            </div>
        ';
    }

    /* supprimer un fichier distant ImageKit si son fileId est connu */
    function ohnous_delete_imagekit_file($fileId)
    {
        require_once __DIR__ . '/dependances.php';

        $fileId = trim((string)$fileId);
        if($fileId === '')
        {
            return [
                'success' => false,
                'skipped' => true,
                'status' => 0,
                'body' => '',
                'error' => 'fileId manquant'
            ];
        }

        if(strpos($fileId, 'http://') === 0 || strpos($fileId, 'https://') === 0 || strpos($fileId, '/') !== false)
        {
            return [
                'success' => false,
                'skipped' => false,
                'status' => 0,
                'body' => '',
                'error' => 'fileId invalide : utilisez fileId, pas une URL ni un chemin'
            ];
        }

        if(!ohnous_load_imagekit())
        {
            return ohnous_delete_imagekit_file_http($fileId, 0, 'SDK ImageKit introuvable');
        }

        try {
            $imageKit = new \ImageKit\ImageKit(
                'public_RBnOctCZRQjH0d5pMKWrl8jQ/zI=',
                'private_yuDBuAtEO0mMujifa4DSzDuUBqI=',
                'https://ik.imagekit.io/nyombi1997/'
            );

            $response = $imageKit->deleteFile($fileId);
            $status = (int)($response->responseMetadata['statusCode'] ?? 0);
            $success = $response->error === null && ($status === 0 || ($status >= 200 && $status < 300) || $status === 404);

            if($success)
            {
                return [
                    'success' => true,
                    'skipped' => false,
                    'status' => $status,
                    'body' => json_encode($response->result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'error' => ''
                ];
            }

            return ohnous_delete_imagekit_file_http(
                $fileId,
                $status,
                is_string($response->error) ? $response->error : json_encode($response->error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        } catch(Throwable $e) {
            return ohnous_delete_imagekit_file_http($fileId, 0, $e->getMessage());
        }
    }

    function ohnous_delete_imagekit_file_http($fileId, $sdkStatus = 0, $sdkError = '')
    {
        $fileId = trim((string)$fileId);
        $privateKey = 'private_yuDBuAtEO0mMujifa4DSzDuUBqI=';
        $url = 'https://api.imagekit.io/v1/files/'.rawurlencode($fileId);

        if(function_exists('curl_init'))
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_USERPWD, $privateKey.':');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $body = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

            return [
                'success' => ($error === '' && (($status >= 200 && $status < 300) || $status === 404)),
                'skipped' => false,
                'status' => $status,
                'body' => (string)$body,
                'error' => $error !== '' ? $error : $sdkError
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => "Authorization: Basic ".base64_encode($privateKey.':')."\r\n",
                'ignore_errors' => true,
                'timeout' => 20
            ]
        ]);
        $body = @file_get_contents($url, false, $context);
        $status = 0;
        $headers = [];

        if(function_exists('http_get_last_response_headers'))
        {
            $headers = http_get_last_response_headers();
        }

        if(is_array($headers))
        {
            foreach($headers as $header)
            {
                if(preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches))
                {
                    $status = (int)$matches[1];
                    break;
                }
            }
        }

        return [
            'success' => (($status >= 200 && $status < 300) || $status === 404),
            'skipped' => false,
            'status' => $status,
            'body' => (string)$body,
            'error' => $sdkError
        ];
    }

    function ohnous_delete_imagekit_file_ids(array $fileIds)
    {
        $results = [];
        $seen = [];

        foreach($fileIds as $fileId)
        {
            $fileId = trim((string)$fileId);
            if($fileId === '' || isset($seen[$fileId]))
            {
                continue;
            }

            $seen[$fileId] = true;
            $results[$fileId] = ohnous_delete_imagekit_file($fileId);
        }

        return $results;
    }

    function ohnous_normalize_imagekit_file_ids(array $fileIds)
    {
        $clean = [];

        foreach($fileIds as $fileId)
        {
            $fileId = trim((string)$fileId);
            if($fileId === '' || strpos($fileId, 'http://') === 0 || strpos($fileId, 'https://') === 0 || strpos($fileId, '/') !== false)
            {
                continue;
            }

            $clean[$fileId] = true;
        }

        return array_keys($clean);
    }

    function ohnous_get_product_images_kept_file_ids(array $productImages)
    {
        $fileIds = [];

        foreach($productImages as $image)
        {
            if(!is_array($image))
            {
                continue;
            }

            $fileId = trim((string)($image['fileId'] ?? ''));
            if($fileId !== '')
            {
                $fileIds[] = $fileId;
            }
        }

        return ohnous_normalize_imagekit_file_ids($fileIds);
    }

    function ohnous_get_article_image_file_ids($articleId)
    {
        $fileIds = [];

        if(!ohnous_column_exists('image_articles', 'fileId'))
        {
            return [];
        }

        foreach(ohnous_get_article_images((int)$articleId) as $image)
        {
            if(!empty($image['fileId']))
            {
                $fileIds[] = (string)$image['fileId'];
            }
        }

        return ohnous_normalize_imagekit_file_ids($fileIds);
    }

    function ohnous_filter_deleted_imagekit_file_ids(array $candidateFileIds, array $keptFileIds)
    {
        $keptMap = array_fill_keys(ohnous_normalize_imagekit_file_ids($keptFileIds), true);
        $deleted = [];

        foreach(ohnous_normalize_imagekit_file_ids($candidateFileIds) as $fileId)
        {
            if(isset($keptMap[$fileId]))
            {
                continue;
            }

            $deleted[] = $fileId;
        }

        return $deleted;
    }

    /* supprimer une ligne image d'article en base, la suppression ImageKit est déclenchée après succès DB */
    function ohnous_delete_article_image_row(array $imageRow)
    {
        global $bdd;

        $remoteResult = [
            'success' => false,
            'skipped' => true,
            'status' => 0,
            'body' => '',
            'error' => ''
        ];

        $stmt = $bdd->prepare('DELETE FROM image_articles WHERE id = :id');
        $deleted = $stmt->execute([':id' => (int)($imageRow['id'] ?? 0)]);
        if(!$deleted)
        {
            throw new Exception("Impossible de supprimer une image article.");
        }

        return $remoteResult;
    }

    /* synchroniser les images d'un article selon l'état envoyé par l'UI */
    function ohnous_sync_article_images($articleId, array $productImages, $altText = '')
    {
        global $bdd;

        $articleId = (int)$articleId;
        $altText = trim((string)$altText);
        $existingImages = ohnous_get_article_images($articleId);
        $existingMap = [];
        $keptIds = [];
        $deleteAfterDbFileIds = [];

        foreach($existingImages as $row)
        {
            $existingMap[(int)$row['id']] = $row;
        }

        foreach($productImages as $index => $image)
        {
            $dbId = (int)($image['db_id'] ?? ($image['dbId'] ?? 0));
            $updateData = [
                'alt_text' => $altText,
                'background' => (string)($image['background'] ?? ''),
                'styles' => (string)($image['style'] ?? ''),
            ];

            if(ohnous_column_exists('image_articles', 'display_order'))
            {
                $updateData['display_order'] = $index + 1;
            }

            if(ohnous_column_exists('image_articles', 'is_primary'))
            {
                $updateData['is_primary'] = $index === 0 ? 1 : 0;
            }

            if($dbId > 0 && isset($existingMap[$dbId]))
            {
                $keptIds[$dbId] = true;
                $oldFileId = (string)($image['old_fileId'] ?? ($existingMap[$dbId]['fileId'] ?? ''));
                $newFileId = (string)($image['fileId'] ?? ($existingMap[$dbId]['fileId'] ?? ''));
                if(!empty($image['url']))
                {
                    $updateData['img'] = (string)$image['url'];
                }
                if(ohnous_column_exists('image_articles', 'fileId'))
                {
                    $updateData['fileId'] = $newFileId;
                }

                $updated = update_bdd($bdd, 'image_articles', $updateData, "id = '".(int)$dbId."'");
                if(!$updated)
                {
                    throw new Exception("Impossible de mettre à jour une image article.");
                }

                if($oldFileId !== '' && $newFileId !== '' && $oldFileId !== $newFileId)
                {
                    $deleteAfterDbFileIds[] = $oldFileId;
                }
                continue;
            }

            if(empty($image['url']))
            {
                continue;
            }

            $insert = [
                'article' => $articleId,
                'img' => (string)$image['url'],
                'alt_text' => $altText,
                'background' => (string)($image['background'] ?? ''),
                'styles' => (string)($image['style'] ?? ''),
            ];

            if(ohnous_column_exists('image_articles', 'fileId'))
            {
                $insert['fileId'] = (string)($image['fileId'] ?? '');
            }

            if(ohnous_column_exists('image_articles', 'display_order'))
            {
                $insert['display_order'] = $index + 1;
            }

            if(ohnous_column_exists('image_articles', 'is_primary'))
            {
                $insert['is_primary'] = $index === 0 ? 1 : 0;
            }

            $inserted = insert_bdd($bdd, 'image_articles', $insert);
            if(!$inserted)
            {
                throw new Exception("Impossible d'ajouter une image article.");
            }
        }

        foreach($existingImages as $row)
        {
            $rowId = (int)$row['id'];
            if(isset($keptIds[$rowId]))
            {
                continue;
            }

            ohnous_delete_article_image_row($row);
            if(ohnous_column_exists('image_articles', 'fileId') && !empty($row['fileId']))
            {
                $deleteAfterDbFileIds[] = (string)$row['fileId'];
            }
        }

        return ohnous_normalize_imagekit_file_ids($deleteAfterDbFileIds);
    }

    /* supprimer un article et ses liaisons principales */
    function ohnous_delete_article_and_relations($articleId)
    {
        global $bdd;

        $articleId = (int)$articleId;
        $images = ohnous_get_article_images($articleId);
        $deleteAfterDbFileIds = [];

        foreach($images as $imageRow)
        {
            if(ohnous_column_exists('image_articles', 'fileId') && !empty($imageRow['fileId']))
            {
                $deleteAfterDbFileIds[] = (string)$imageRow['fileId'];
            }

            ohnous_delete_article_image_row($imageRow);
        }

        $bdd->prepare('DELETE FROM categorie_article WHERE article = :article')->execute([':article' => $articleId]);
        $bdd->prepare('DELETE FROM types_article WHERE article = :article')->execute([':article' => $articleId]);
        $bdd->prepare('DELETE FROM taille_articles WHERE article = :article')->execute([':article' => $articleId]);

        if(ohnous_table_exists('article_likes'))
        {
            $bdd->prepare('DELETE FROM article_likes WHERE article_id = :article')->execute([':article' => $articleId]);
        }

        if(ohnous_table_exists('notes_article'))
        {
            $bdd->prepare('DELETE FROM notes_article WHERE article_id = :article')->execute([':article' => $articleId]);
        }

        if(ohnous_table_exists('article_reports'))
        {
            $bdd->prepare('DELETE FROM article_reports WHERE article_id = :article')->execute([':article' => $articleId]);
        }

        $bdd->prepare('DELETE FROM articles WHERE id = :article')->execute([':article' => $articleId]);

        return ohnous_delete_imagekit_file_ids($deleteAfterDbFileIds);
    }

    /* table des signalements articles */
    function ohnous_ensure_article_reports_table()
    {
        createTable('article_reports', [
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'article_id INT NOT NULL',
            'boutique_id INT NOT NULL DEFAULT 0',
            'client_type VARCHAR(30) NULL',
            'client_id INT NOT NULL DEFAULT 0',
            'client_nom VARCHAR(190) NULL',
            'motif VARCHAR(120) NOT NULL',
            'message TEXT NOT NULL',
            'statut VARCHAR(30) NOT NULL DEFAULT \'nouveau\'',
            'admin_reason TEXT NULL',
            'date_traitement DATETIME NULL',
            'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    function ohnous_get_article_report_reasons()
    {
        return [
            'contenu_inapproprie' => 'Contenu inapproprié',
            'article_trompeur' => 'Article trompeur',
            'contrefacon' => 'Contrefaçon',
            'autre' => 'Autre problème'
        ];
    }

    function ohnous_get_article_reports_count($articleId)
    {
        global $bdd;

        if(!ohnous_table_exists('article_reports'))
        {
            return 0;
        }

        $stmt = $bdd->prepare("SELECT COUNT(*) FROM article_reports WHERE article_id = :article_id AND statut = 'nouveau'");
        $stmt->execute([':article_id' => (int)$articleId]);
        return (int)$stmt->fetchColumn();
    }

    function ohnous_get_article_latest_report($articleId)
    {
        global $bdd;

        if(!ohnous_table_exists('article_reports'))
        {
            return null;
        }

        $stmt = $bdd->prepare("
            SELECT *
            FROM article_reports
            WHERE article_id = :article_id
            ORDER BY date_ajout DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([':article_id' => (int)$articleId]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        return $report ?: null;
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

    /* trier les tailles du plus grand au plus petit */
    function ohnous_sort_size_rows_desc(array $rows)
    {
        usort($rows, function($a, $b){
            $nameA = trim((string)($a['nom'] ?? ''));
            $nameB = trim((string)($b['nom'] ?? ''));
            $numA = preg_match('/^\d+(?:[.,]\d+)?$/', $nameA) ? (float)str_replace(',', '.', $nameA) : null;
            $numB = preg_match('/^\d+(?:[.,]\d+)?$/', $nameB) ? (float)str_replace(',', '.', $nameB) : null;
            $rankMap = [
                'XXXXL' => 90,
                'XXXL' => 80,
                'XXL' => 70,
                'XL' => 60,
                'L' => 50,
                'M' => 40,
                'S' => 30,
                'XS' => 20,
                'XXS' => 10,
            ];
            $keyA = strtoupper(preg_replace('/\s+/', '', $nameA));
            $keyB = strtoupper(preg_replace('/\s+/', '', $nameB));
            $rankA = $rankMap[$keyA] ?? null;
            $rankB = $rankMap[$keyB] ?? null;

            if($numA !== null && $numB !== null)
            {
                return $numB <=> $numA;
            }

            if($rankA !== null && $rankB !== null)
            {
                return $rankB <=> $rankA;
            }

            return strnatcasecmp($nameB, $nameA);
        });

        return $rows;
    }

    /* bornes de filtres de prix côté catalogue */
    function ohnous_get_price_filter_ranges()
    {
        return [
            'plus-100' => ['label' => 'Plus de 100 $', 'min' => 100, 'max' => null],
            '50-100' => ['label' => '50 $ à 100 $', 'min' => 50, 'max' => 100],
            '25-50' => ['label' => '25 $ à 50 $', 'min' => 25, 'max' => 50],
            'moins-25' => ['label' => 'Moins de 25 $', 'min' => null, 'max' => 25],
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
            'utilisateurs' => ['label' => 'Activations', 'link' => '/admin-activation-utilisateurs', 'icon' => 'fa-user-check'],
            'livraison' => ['label' => 'Livraison', 'link' => '/admin-zones-livraison', 'icon' => 'fa-truck-fast'],
            'admins' => ['label' => 'Admins', 'link' => '/admin-admins', 'icon' => 'fa-user-shield'],
        ];

        $html = '<nav class="admin-liquid-nav">';

        foreach($items as $key => $item)
        {
            $activeClass = $key === $current ? 'is-active' : '';
            $html .= '
                <a href="'.$item['link'].'" class="admin-liquid-nav__link '.$activeClass.'">
                    <i class="'.(strpos($item['icon'], 'fa-brands') === 0 ? $item['icon'] : 'fa-solid '.$item['icon']).'"></i>
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

    /* recuperer toutes les images d'un article dans l'ordre naturel */
    function ohnous_get_article_images($articleId)
    {
        global $bdd;
        return select_bdd($bdd, "image_articles", "article = '".(int)$articleId."'", null, 0, "id ASC", false);
    }

    /* rendu partage des galeries d'images pour les cartes, details et listes admin */
    function ohnous_render_article_gallery($articleId, $slug = '', $context = 'card', $link = '', $images = null)
    {
        $images = is_array($images) ? array_values($images) : ohnous_get_article_images($articleId);

        if(empty($images))
        {
            return '';
        }

        $context = in_array($context, ['card', 'detail', 'admin-mini', 'admin-thumb'], true) ? $context : 'card';
        $hasMultipleImages = count($images) > 1;
        $altBase = trim((string)$slug) !== '' ? (string)$slug : 'article-ohnous';
        $link = trim((string)$link);
        $sizes = $context === 'detail'
            ? '(max-width: 768px) 92vw, 780px'
            : '(max-width: 768px) 50vw, 320px';

        if($hasMultipleImages)
        {
            $html = '<div class="swiper article-gallery-swiper article-gallery-swiper--'.$context.' js-product-gallery-swiper" data-gallery-context="'.$context.'">';
            $html .= '<div class="swiper-wrapper">';

            foreach($images as $image)
            {
                $liquidImage = ohnous_prepare_liquid_image((string)$image['img'], $sizes);
                $mediaHtml = '
                    <div class="div_img_affiche_produit'.($context === 'detail' ? ' div_img_affiche_produit--detail' : '').'" style="background: '.htmlspecialchars((string)($image['background'] ?? ''), ENT_QUOTES, 'UTF-8').';">
                        <img
                            crossorigin="anonymous"
                            src="'.htmlspecialchars($liquidImage['placeholder'], ENT_QUOTES, 'UTF-8').'"
                            alt="'.htmlspecialchars($altBase, ENT_QUOTES, 'UTF-8').'"
                            class="img_affiche blur-up js-liquid-image"
                            data-img ="'.htmlspecialchars((string)$image['img'], ENT_QUOTES, 'UTF-8').'"
                            data-image-base="'.htmlspecialchars($liquidImage['base'], ENT_QUOTES, 'UTF-8').'"
                            data-image-fallback="'.htmlspecialchars($liquidImage['fallback'], ENT_QUOTES, 'UTF-8').'"
                            data-image-high="'.htmlspecialchars($liquidImage['high'], ENT_QUOTES, 'UTF-8').'"
                            data-image-srcset="'.htmlspecialchars($liquidImage['srcset'], ENT_QUOTES, 'UTF-8').'"
                            data-image-sizes="'.htmlspecialchars($liquidImage['sizes'], ENT_QUOTES, 'UTF-8').'"
                            style="'.htmlspecialchars((string)($image['styles'] ?? ''), ENT_QUOTES, 'UTF-8').'"
                            loading="lazy"
                        >
                    </div>';

                if($link !== '')
                {
                    $mediaHtml = '<a href="'.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'" class="article-gallery-link">'.$mediaHtml.'</a>';
                }

                $html .= '<div class="swiper-slide">'.$mediaHtml.'</div>';
            }

            $html .= '</div>';
            $html .= '<div class="article-gallery-counter article-gallery-counter--'.$context.'"><span class="current">1</span>/<span class="total">'.count($images).'</span></div>';
            $html .= '</div>';

            return $html;
        }

        $image = $images[0];
        $liquidImage = ohnous_prepare_liquid_image((string)$image['img'], $sizes);
        $html = '
            <div class="article-gallery-swiper article-gallery-swiper--'.$context.' article-gallery-swiper--static">
                <div class="div_img_affiche_produit'.($context === 'detail' ? ' div_img_affiche_produit--detail' : '').'" style="background: '.htmlspecialchars((string)($image['background'] ?? ''), ENT_QUOTES, 'UTF-8').';">
                    <img
                        crossorigin="anonymous"
                        src="'.htmlspecialchars($liquidImage['placeholder'], ENT_QUOTES, 'UTF-8').'"
                        alt="'.htmlspecialchars($altBase, ENT_QUOTES, 'UTF-8').'"
                        class="img_affiche blur-up js-liquid-image"
                        data-img ="'.htmlspecialchars((string)$image['img'], ENT_QUOTES, 'UTF-8').'"
                        data-image-base="'.htmlspecialchars($liquidImage['base'], ENT_QUOTES, 'UTF-8').'"
                        data-image-fallback="'.htmlspecialchars($liquidImage['fallback'], ENT_QUOTES, 'UTF-8').'"
                        data-image-high="'.htmlspecialchars($liquidImage['high'], ENT_QUOTES, 'UTF-8').'"
                        data-image-srcset="'.htmlspecialchars($liquidImage['srcset'], ENT_QUOTES, 'UTF-8').'"
                        data-image-sizes="'.htmlspecialchars($liquidImage['sizes'], ENT_QUOTES, 'UTF-8').'"
                        style="'.htmlspecialchars((string)($image['styles'] ?? ''), ENT_QUOTES, 'UTF-8').'"
                        loading="lazy"
                    >
                </div>
            </div>';

        if($link !== '')
        {
            $html = '<a href="'.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'" class="article-gallery-link">'.$html.'</a>';
        }

        return $html;
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

