<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";
    header('Content-Type: application/json; charset=utf-8');

    if (!validateHoneypot('inscription_utilisateur')) {
        ohnous_honeypot_neutral_json();
    }

    $user_name = html_entity_decode(filter_var($_POST['user_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = html_entity_decode(filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $unique_id = uniqid('user_', true);
    // Hachage du mot de passe
    $mdp = password_hash(
        html_entity_decode(filter_var($_POST['mdp'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
        PASSWORD_DEFAULT
    );

    $verif_pseudo = only_select("boutiques", "nom = '$user_name'", $order = null, $limit = null);
    $verif_pseudo_user = only_select("utilisateur", "nom = '$user_name'", $order = null, $limit = null);
    $verif_email = only_select("boutiques", "adresse_email = '$email'", $order = null, $limit = null);
    $verif_email_user = only_select("utilisateur", "adresse_email = '$email'", $order = null, $limit = null);
    
    if($verif_pseudo)
    {
        $results = [
            "result" => "error",
            "msg" => "Le nom de boutique est déjà utiliser"
        ];
    }
    else if($verif_pseudo_user)
    {
        $results = [
            "result" => "error",
            "msg" => "Le nom d'utilisateur est déjà utiliser"
        ];
    }
    else if($verif_email)
    {
        $results = [
            "result" => "error",
            "msg" => "L'adresse email est déjà utiliser"
        ];
    }
    else if($verif_email_user)
    {
        $results = [
            "result" => "error",
            "msg" => "L'adresse email est déjà utiliser"
        ];
    }
    else
    {
        $insert_data = [
            "nom" => $user_name,
            "adresse_email" => $email,
            "mdp" => $mdp,
            "unique_id" => $unique_id
        ];

        insert_bdd($bdd, "utilisateur", $insert_data);

        /* creer des slugs s'il y'en a pas */
        createSlugIfNeeded($bdd, "utilisateur");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_ohnous_987654321'] = $unique_id;

        $newUser = only_select("utilisateur", "unique_id = '".addslashes($unique_id)."'", null, null);
        if($newUser)
        {
            ohnous_send_welcome_email_once($newUser, 'utilisateur');
        }

        $results = [
            "result" => "ok",
            "msg" => ""
        ];
    }

    // Retour en JSON
    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
