<?php
    // Fonction pour générer un slug à partir d'une chaîne de caractères
    function generateSlug($string,$separator = '-')
    {
        global $bdd;
        // Génération du slug de base
        $slug = strtolower($string);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        $slug = preg_replace('/[^a-z0-9]+/i', $separator, $slug);
        $slug = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $slug);
        $slug = trim($slug, $separator);

        $baseSlug = $slug;
        $i = 1;

        // Récupérer toutes les tables qui ont une colonne "slug"
        $sql = "
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND COLUMN_NAME = 'slug'
        ";

        $tables = $bdd->query($sql)->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tables)) {
            return $slug; // aucune table avec slug → paix intérieure
        }

        // Vérifier l’unicité globale
        do {
            $exists = false;

            foreach ($tables as $table) {
                $check = $bdd->prepare("
                    SELECT 1 
                    FROM `$table`
                    WHERE slug = :slug
                    LIMIT 1
                ");
                $check->execute(['slug' => $slug]);

                if ($check->fetch()) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $slug = $baseSlug . $separator . $i;
                $i++;
            }

        } while ($exists);

        return $slug;
    }

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
?>