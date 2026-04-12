<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = trim((string)html_entity_decode(filter_var($_POST['token'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $password = (string)($_POST['mdp'] ?? '');

    if($token === '' || $password === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Données de réinitialisation invalides."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!ohnous_table_exists('admin_password_resets'))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Aucune demande de réinitialisation n’est disponible."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $stmt = $bdd->prepare("
        SELECT *
        FROM admin_password_resets
        WHERE token = :token
        AND used_at IS NULL
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$reset)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Ce lien de réinitialisation est invalide."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(new DateTime($reset['expire_at']) < new DateTime())
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Ce lien de réinitialisation a expiré."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    update_bdd($bdd, 'admins', [
        'mdp' => $hash
    ], "id = '".(int)$reset['admin_id']."'");

    update_bdd($bdd, 'admin_password_resets', [
        'used_at' => date('Y-m-d H:i:s')
    ], "id = '".(int)$reset['id']."'");

    $_SESSION['admin_ohnous_987654321'] = (int)$reset['admin_id'];

    echo json_encode([
        'result' => 'ok',
        'redirect' => '/admin'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
