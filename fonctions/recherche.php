<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');

    // Récupérer le terme recherché
    $q = $_POST['q'] ?? '';

    $results = found($q);
    /* trouver le mot le plus proche */
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
    /* gestion du niveau de tolerance */
    function getTolerance($word) {
        if (is_numeric($word)) return 0;        // prix, tailles
        if (strlen($word) <= 2) return 0;       // trop ambigu
        if (strlen($word) <= 3) return 0;       // trop court
        if (strlen($word) <= 4) return 2;
        if (strlen($word) <= 7) return 2;
        if (strlen($word) <= 10) return 3;
        return 4;
    }
    /* normalisation, du mot */
    function normalize($string) {
        $string = strtolower($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        return preg_replace('/[^a-z0-9 ]/', '', $string);
    }
    /* correction de phrases */
    function correctSentence($input, $phrases) {
        $inputWords = explode(' ', normalize($input));
        $corrected = [];
        $hasCorrection = false;

        foreach ($inputWords as $word) {

            // mots trop courts → on ignore
            if (strlen($word) <= 2) {
                $corrected[] = $word;
                continue;
            }

            $suggestion = findClosestWord($word, $phrases);

            // si correction valable ET différente
            if ($suggestion !== null && $suggestion !== $word) {
                $corrected[] = $suggestion;
                $hasCorrection = true;
            } else {
                $corrected[] = $word;
            }
        }

        // AUCUNE vraie correction → aucune suggestion
        if (!$hasCorrection) {
            return false;
        }

        return implode(' ', $corrected);
    }



    /* si on aucun resultat */
    if(count($results) == 0 && $q != "")
    {
        /* rechercher les articles se rapprochant de la recherche */
        $stmt = $bdd->query("SELECT nom FROM articles");
        $words = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $suggestion = correctSentence($q, $words);
        if($suggestion)
        {
            $results = [
                "suggestion" => $suggestion
            ];
        }
        else
        {
            /* rechercher les boutiques se rapprochant de la recherche */
            $stmt = $bdd->query("SELECT nom FROM boutiques");
            $words = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $suggestion = correctSentence($q, $words);
            if($suggestion)
            {
                $results = [
                    "suggestion" => $suggestion
                ];
            }
            else
            {
                /* rechercher les categories se rapprochant de la recherche */
                $stmt = $bdd->query("SELECT nom FROM categorie");
                $words = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $suggestion = correctSentence($q, $words);
                if($suggestion)
                {
                    $results = [
                        "suggestion" => $suggestion
                    ];
                }
                else
                {
                    /* rechercher les types se rapprochant de la recherche */
                    $stmt = $bdd->query("SELECT nom FROM types");
                    $words = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $suggestion = correctSentence($q, $words);
                    if($suggestion)
                    {
                        $results = [
                            "suggestion" => $suggestion
                        ];
                    }
                    else
                    {
                        /* rechercher les tailles se rapprochant de la recherche */
                        $stmt = $bdd->query("SELECT nom FROM tailles");
                        $words = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        $suggestion = correctSentence($q, $words);
                        if($suggestion)
                        {
                            $results = [
                                "suggestion" => $suggestion
                            ];
                        }
                        else
                        {
                            $results = [
                                "noResult" => ''
                            ];                            
                        }                        
                    }
                }
            }
        }
    }


    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>