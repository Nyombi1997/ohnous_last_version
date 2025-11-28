<?php
    include_once "../model/bdd.php";    
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(isset($_SESSION['panier_OhNous']))
    {
        if(isset($_POST['retire']))
        {
            foreach ($_SESSION['panier_OhNous'] as $index => $item) {
                if(
                    $item['produit']==$_POST['produit'] &&
                    $item['taille']==$_POST['taille'] &&
                    $item['quantite']==$_POST['quantite']
                ){
                    unset($_SESSION['panier_OhNous'][$index]);
                    $_SESSION['panier_OhNous'] = array_values($_SESSION['panier_OhNous']);
                    $results = [
                        "msg" => "1"
                    ];
                    break;
                };
            }
        }
        else
        {
            $_SESSION['panier_OhNous'][] = [
                "produit" => $_POST['produit'],
                "taille" => $_POST['taille'],
                "quantite" => $_POST['quantite'],
            ];
            $results = [
                "msg" => "1"
            ];            
        }
    }
    else
    {
        $_SESSION['panier_OhNous'][] = [
            "produit" => $_POST['produit'],
            "taille" => $_POST['taille'],
            "quantite" => $_POST['quantite'],
        ];
        $results = [
            "msg" => "1"
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>