<?php
    /* produit avec limitation */
    function getProducts($bdd, $base, $limit = null, $offset = 0) 
    {
        $request = "SELECT * FROM $base";
        if ($limit !== null) {
            $request .= " LIMIT :limit OFFSET :offset";
            $stmt = $bdd->prepare($request);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        } else {
            $stmt = $bdd->prepare($request);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function select_bdd($bdd, $base, $where = null, $limit = null, $offset = 0, $order = null, $random = false)
    {
        $request = "SELECT * FROM $base";

        if ($where !== null) {
            $request .= " WHERE " . $where;
        }

        if ($random) {
            $request .= " ORDER BY RAND()"; // Pour MySQL
            // $request .= " ORDER BY RANDOM()"; // Pour PostgreSQL
        } elseif ($order !== null) {
            $request .= " ORDER BY " . $order;
        }

        if ($limit !== null) {
            $request .= " LIMIT :limit OFFSET :offset";
            $stmt = $bdd->prepare($request);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        } else {
            $stmt = $bdd->prepare($request);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* nombre de ligne de la table */
    function getRowCount($bdd, $base, $where = null, $limit = null, $offset = 0, $order = null) 
    {
        $request = "SELECT COUNT(*) as count FROM $base";

        if ($where !== null) {
            $request .= " WHERE ".$where;
        }
        $stmt = $bdd->prepare($request);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }

    function update_bdd($bdd, $base, $update_data, $where) 
    {
        /**
         * Tableau associatif contenant les données à mettre à jour.
         * Les clés doivent correspondre aux noms des colonnes de la table en base de données,
         * et les valeurs doivent être les nouvelles valeurs à attribuer à ces colonnes.
         * Exemple :
         * [
         *     'nom_colonne1' => 'nouvelle_valeur1',
         *     'nom_colonne2' => 'nouvelle_valeur2',
         *     // ...
         * ]
         */
        $set = [];
        foreach ($update_data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set_clause = implode(', ', $set);
        $request = "UPDATE $base SET $set_clause WHERE $where";
        $stmt = $bdd->prepare($request);
        foreach ($update_data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }

    function insert_bdd($bdd, $base, $insert_data) 
    {
        /**
         * Tableau associatif contenant les données à insérer dans la base de données.
         *
         * Exemple de structure attendue pour $insert_data :
         * [
         *     'nom_colonne1' => 'valeur1',
         *     'nom_colonne2' => 'valeur2',
         *     // ...
         * ]
         *
         * Chaque clé du tableau correspond au nom d'une colonne dans la table cible,
         * et chaque valeur correspond à la donnée à insérer dans cette colonne.
         */
        $columns = implode(', ', array_keys($insert_data));
        $placeholders = ':' . implode(', :', array_keys($insert_data));
        $request = "INSERT INTO $base ($columns) VALUES ($placeholders)";
        $stmt = $bdd->prepare($request);
        foreach ($insert_data as $key => $value) {
            $value = html_entity_decode(htmlspecialchars($value));
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }
    /* créer un slug aux s'il y'en a pas */
    function createSlugIfNeeded($bdd, $base) {
        $request = "SELECT id, nom FROM $base WHERE slug IS NULL OR slug = ''";
        $stmt = $bdd->prepare($request);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $slug = generateSlug($row['nom']);
            update_bdd($bdd, $base, ['slug' => $slug], "id = " . intval($row['id']));
        }
    }
    /* while select sql */
    function while_select($table, $where = null, $order = null, $limit = null) {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        return $req;
    }
    /* select */
    function select($table, $where = null, $order = null, $limit = null)
    {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        if (!$req) {
            die("Erreur SQL : " . print_r($bdd->errorInfo(), true));
        }
        return $req;
    }
    /* select dernier */
    function select_dernier($table, $where = null, $order = null, $limit = null) {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        return $req->fetch(PDO::FETCH_ASSOC);
    }
    /* select only */
    function only_select($table, $where = null, $order = null, $limit = null)
    {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        if (!$req) {
            die("Erreur SQL : " . print_r($bdd->errorInfo(), true));
        }
        $req->execute();
    
        $req_ = $req->fetch(PDO::FETCH_ASSOC);
        return $req_;
    }
    /* select like */
    function select_like($get = null, $bdd = null) {
        /* si une recherche est faite */
        if($get || $get = '')
        {
            $search = isset($get) ? $get : '';
            $search = strtolower($search); // minuscule
            $search = preg_replace('/[^a-z0-9]/', '', $search); // enlever caractères spéciaux
        
            $query = $bdd->prepare("
                SELECT 'produit' AS source, id, nom
                FROM produit
                WHERE LOWER(REGEXP_REPLACE(nom, '[^a-zA-Z0-9]', '')) LIKE :search
                UNION
                SELECT 'etablissement' AS source, id, nom
                FROM etablissement
                WHERE LOWER(REGEXP_REPLACE(nom, '[^a-zA-Z0-9]', '')) LIKE :search
                UNION
                SELECT 'categorie' AS source, id, nom
                FROM categorie
                WHERE LOWER(REGEXP_REPLACE(nom, '[^a-zA-Z0-9]', '')) LIKE :search
                ORDER BY nom ASC
            ");
            $search_sql = '%' . $search . '%';
            $query->execute(['search' => $search_sql]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
        }
    }

?>
