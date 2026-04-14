<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if(!ohnous_is_admin())
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Accès administrateur requis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!ohnous_table_exists('delivery_zones') || !ohnous_table_exists('delivery_settings'))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Les tables SQL de livraison sont manquantes. Appliquez le SQL ajouté dans le README.md."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $action = trim((string)($_POST['action'] ?? ''));

    function upsertDeliverySetting($key, $value)
    {
        global $bdd;

        $existing = only_select('delivery_settings', "setting_key = '".addslashes($key)."'", null, null);
        if($existing)
        {
            update_bdd($bdd, 'delivery_settings', ['setting_value' => (string)$value], "id = '".(int)$existing['id']."'");
            return;
        }

        insert_bdd($bdd, 'delivery_settings', [
            'setting_key' => $key,
            'setting_value' => (string)$value
        ]);
    }

    if($action === 'save_settings')
    {
        $useGlobalPrice = (int)($_POST['use_global_price'] ?? 0) === 1 ? 1 : 0;
        $globalPrice = (float)($_POST['global_price'] ?? 0);

        upsertDeliverySetting('use_global_price', $useGlobalPrice);
        upsertDeliverySetting('global_price', $globalPrice);

        echo json_encode([
            'result' => 'ok',
            'msg' => "La configuration de livraison a été enregistrée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'save_zone')
    {
        $zoneId = (int)($_POST['zone_id'] ?? 0);
        $nom = trim((string)($_POST['nom'] ?? ''));
        $prix = (float)($_POST['prix'] ?? 0);
        $actif = (int)($_POST['actif'] ?? 1) === 1 ? 1 : 0;

        if($nom === '')
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Le nom de la zone est obligatoire."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $data = [
            'nom' => $nom,
            'slug' => generateSlug($nom),
            'prix' => $prix,
            'actif' => $actif
        ];

        if($zoneId > 0)
        {
            $existingZone = only_select('delivery_zones', 'id = '.$zoneId, null, null);
            if(!$existingZone)
            {
                echo json_encode([
                    'result' => 'error',
                    'msg' => "Zone introuvable."
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }

            update_bdd($bdd, 'delivery_zones', $data, "id = '".(int)$zoneId."'");
        }
        else
        {
            insert_bdd($bdd, 'delivery_zones', $data);
        }

        echo json_encode([
            'result' => 'ok',
            'msg' => "La zone de livraison a bien été enregistrée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if($action === 'toggle_zone')
    {
        $zoneId = (int)($_POST['zone_id'] ?? 0);
        $zone = only_select('delivery_zones', 'id = '.$zoneId, null, null);

        if(!$zone)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Zone introuvable."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $nextState = (int)($zone['actif'] ?? 1) === 1 ? 0 : 1;
        update_bdd($bdd, 'delivery_zones', ['actif' => $nextState], "id = '".(int)$zoneId."'");

        echo json_encode([
            'result' => 'ok',
            'msg' => $nextState === 1 ? "La zone a été activée." : "La zone a été désactivée."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'error',
        'msg' => "Action livraison inconnue."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
