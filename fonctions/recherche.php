<?php
    include_once __DIR__ . "/../model/bdd.php";
    include_once __DIR__ . "/../model/select.php";
    include_once __DIR__ . "/fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    $q = trim((string)($_POST['q'] ?? ''));
    $results = found($q, 12, 0, null, false);

    function findClosestWord($input, $phrases) {
        $bestWord = null;
        $bestDistance = -1;

        foreach ($phrases as $phrase) {
            $words = explode(' ', $phrase);

            foreach ($words as $word) {
                $lev = levenshtein($input, $word);

                if ($lev === 0) {
                    return $word;
                }

                if ($bestDistance < 0 || $lev < $bestDistance) {
                    $bestDistance = $lev;
                    $bestWord = $word;
                }
            }
        }

        $maxDistance = getTolerance($input);
        return ($bestDistance <= $maxDistance) ? $bestWord : null;
    }

    function getTolerance($word) {
        if (is_numeric($word)) return 0;
        if (strlen($word) <= 3) return 0;
        if (strlen($word) <= 7) return 2;
        if (strlen($word) <= 10) return 3;
        return 4;
    }

    function normalize($string) {
        $string = strtolower($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        return preg_replace('/[^a-z0-9 ]/', '', $string);
    }

    function correctSentence($input, $phrases) {
        $inputWords = explode(' ', normalize($input));
        $corrected = [];
        $hasCorrection = false;

        foreach ($inputWords as $word) {
            if (strlen($word) <= 2) {
                $corrected[] = $word;
                continue;
            }

            $suggestion = findClosestWord($word, $phrases);
            if ($suggestion !== null && $suggestion !== $word) {
                $corrected[] = $suggestion;
                $hasCorrection = true;
            } else {
                $corrected[] = $word;
            }
        }

        if (!$hasCorrection) {
            return false;
        }

        return implode(' ', $corrected);
    }

    $suggestion = false;

    if($q !== '')
    {
        $sources = [
            "SELECT nom FROM articles",
            "SELECT nom FROM boutiques",
            "SELECT nom FROM categorie",
            "SELECT nom FROM types",
            "SELECT nom FROM tailles",
        ];

        foreach($sources as $sql)
        {
            $stmt = $bdd->query($sql);
            $words = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
            $candidate = correctSentence($q, $words);
            if($candidate)
            {
                $suggestion = $candidate;
                break;
            }
        }
    }

    if(count($results) === 0 && $q !== '')
    {
        if($suggestion)
        {
            echo json_encode([
                'suggestion' => $suggestion
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        echo json_encode([
            'noResult' => true
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $payload = [];

    foreach($results as $result)
    {
        $item = [
            'id' => (int)$result['id'],
            'label' => $result['label'],
            'slug' => $result['slug'],
            'source' => $result['source'],
            'description' => $result['description'] ?? '',
            'url' => '/shop?query='.rawurlencode($result['label']),
        ];

        if($result['source'] === 'articles')
        {
            $article = only_select("articles", "id = '".(int)$result['id']."'", null, null);
            if(!$article || !ohnous_is_article_visible($article))
            {
                continue;
            }

            $image = ohnous_get_article_primary_image((int)$result['id']);
            $pricing = ohnous_get_article_pricing($article);

            $item['url'] = '/article/'.$result['slug'];
            $item['price_label'] = '$ '.number_format((float)$pricing['prix_final'], 2, '.', ' ');
            $item['background'] = $image['background'] ?? '';
            $item['style'] = $image['styles'] ?? '';
            $item['image'] = $image['img'] ?? '';
        }
        elseif($result['source'] === 'boutiques')
        {
            $item['url'] = '/boutique/'.$result['slug'];
        }
        elseif($result['source'] === 'categorie')
        {
            $item['url'] = '/shop?categorie='.rawurlencode((string)$result['slug']);
        }
        elseif($result['source'] === 'types')
        {
            $item['url'] = '/shop?type='.rawurlencode((string)$result['slug']);
        }
        elseif($result['source'] === 'tailles')
        {
            $item['url'] = '/shop?taille='.rawurlencode((string)$result['slug']);
        }

        $payload[] = $item;
    }

    if($suggestion)
    {
        echo json_encode([
            'suggestion' => $suggestion,
            'results' => $payload
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
