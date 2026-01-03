<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    header('Content-Type: application/json; charset=utf-8');
    /* si il n'y a pas encore de session */
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $id = html_entity_decode(filter_var($_POST['id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $price = html_entity_decode(filter_var($_POST['price'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $name = html_entity_decode(filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $size = html_entity_decode(filter_var($_POST['size'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $image = html_entity_decode(filter_var($_POST['image'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $style = html_entity_decode(filter_var($_POST['style'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $background = html_entity_decode(filter_var($_POST['background'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $slug = html_entity_decode(filter_var($_POST['slug'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    /* id pour le panier */
    function cartKey($id, $size) {
        return $id . '_' . $size; 
    }
    /* ajouter au panier */
    function addToCart($id, $name, $price, $size, $image, $style = "", $background = "", $slug = "") {
        $key = cartKey($id, $size);

        if (!isset($_SESSION['cart-ohnous-123456789'])) {
            $_SESSION['cart-ohnous-123456789'] = [];
        }

        if (isset($_SESSION['cart-ohnous-123456789'][$key])) {
            $_SESSION['cart-ohnous-123456789'][$key]['qty']++;
        } else {
            $_SESSION['cart-ohnous-123456789'][$key] = [
                'id'    => $id,
                'name'  => $name,
                'price' => $price,
                'size'  => $size,
                'qty'   => 1,
                'image' => $image,
                'style' => $style,
                'background' => $background,
                'slug' => $slug
            ];
        }
    }
    $results = [
        "result" => "ok",
        "msg" => ""
    ];
    /* envoyer les donner pour le panier */
    if(isset($_POST['ajout']))
    {
        addToCart($id, $name, $price, $size, $image, $style, $background, $slug);
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>