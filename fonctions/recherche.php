<?php
    include_once "../model/bdd.php";
    //header('Content-Type: application/json; charset=utf-8');

    // Récupérer le terme recherché
    $q = $_POST['q'] ?? '';
    $q = trim($q);

    if ($q === '' || strlen($q) < 1) {
        echo json_encode([]);
        exit;
    }
    // Extraction d'un montant si la recherche contient "700 $" ou "700 dollars"
    if (preg_match('/(\d+)\s*(\$|dollars?|fcfa?|euros?|francs?|\w*)?/i', $q, $matches)) {
        $q_numeric = $matches[1];
        $q = $matches[1]; // On remplace $q par la valeur numérique extraite
    }


    // Requête multi-tables avec pondération + pertinence
    $sql = "
        (
            SELECT id, nom COLLATE utf8mb4_general_ci AS label, prix, NULL AS icone, slug COLLATE utf8mb4_general_ci AS slug, 'produit' AS source,
                (
                    (CASE WHEN nom LIKE :start THEN 5
                        WHEN nom LIKE :middle THEN 3
                        WHEN nom LIKE :any THEN 1 ELSE 0 END)
                    +
                    (CASE WHEN prix LIKE :any THEN 1 ELSE 0 END)
                ) AS score
            FROM produit
            WHERE (nom LIKE :any OR prix LIKE :any OR (:q_numeric IS NOT NULL AND prix <= :q_numeric))
        )
        UNION
        (
            SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, NULL AS icone, slug COLLATE utf8mb4_general_ci AS slug, 'etablissement' AS source,
                (
                    (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                        WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                        WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                ) AS score
            FROM etablissement
            WHERE nom COLLATE utf8mb4_general_ci LIKE :any
        )
        UNION
        (
            SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, icone, slug COLLATE utf8mb4_general_ci AS slug, 'types' AS source,
                (
                    (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                        WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                        WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                ) AS score
            FROM types
            WHERE nom COLLATE utf8mb4_general_ci LIKE :any
        )
        UNION
        (
            SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, icone, slug COLLATE utf8mb4_general_ci AS slug, 'theme' AS source,
                (
                    (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                        WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                        WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                ) AS score
            FROM theme
            WHERE nom COLLATE utf8mb4_general_ci LIKE :any
        )
        ORDER BY score DESC, label ASC
        LIMIT 20
        ";

    // Préparation et exécution
    $stmt = $bdd->prepare($sql);
    $q_numeric = is_numeric($q) ? $q : null;
    $stmt->execute([
        ":start"     => "$q%",
        ":middle"    => "% $q%",
        ":any"       => "%$q%",
        ":q_numeric" => $q_numeric
    ]);

    $results = $stmt->fetchAll();

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>