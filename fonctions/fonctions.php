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
?>
