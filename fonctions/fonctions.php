<?php
    // Fonction pour générer un slug à partir d'une chaîne de caractères
    function generateSlug($string, $separator = '-') {
        // Convertir en minuscules
        $slug = strtolower($string);

        // Remplacer les caractères accentués
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);

        // Remplacer tout ce qui n'est pas alphanumérique par le séparateur
        $slug = preg_replace('/[^a-z0-9]+/i', $separator, $slug);

        // Supprimer les séparateurs multiples
        $slug = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $slug);

        // Supprimer le séparateur au début et à la fin
        $slug = trim($slug, $separator);

        return $slug;
    }
?>