<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";

    header('Content-Type: application/json; charset=utf-8');

    if (!validateHoneypot('connexion_admin')) {
        ohnous_honeypot_neutral_json();
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $email = trim((string)html_entity_decode(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    $mdp = (string)($_POST['mdp'] ?? '');

    if($email === '' || $mdp === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Veuillez renseigner votre email et votre mot de passe."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(!ohnous_table_exists('admins'))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "La table admins est introuvable."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $stmt = $bdd->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$admin)
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Identifiants admin invalides."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if(password_verify($mdp, (string)$admin['mdp']))
    {
        $_SESSION['admin_ohnous_987654321'] = (int)$admin['id'];

        echo json_encode([
            'result' => 'ok',
            'redirect' => '/admin'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    createTable('admin_password_resets', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'admin_id INT NOT NULL',
        'token VARCHAR(190) NOT NULL',
        'expire_at DATETIME NOT NULL',
        'used_at DATETIME NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);

    $token = bin2hex(random_bytes(24));
    $expireAt = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

    insert_bdd($bdd, 'admin_password_resets', [
        'admin_id' => (int)$admin['id'],
        'token' => $token,
        'expire_at' => $expireAt
    ]);

    $resetUrl = 'https://ohnous.store/admin-nouveau-mot-de-passe?token='.urlencode($token);
    ohnous_send_admin_password_reset_email($resetUrl);

    echo json_encode([
        'result' => 'error',
        'msg' => "Mot de passe incorrect. Un email de réinitialisation a été envoyé à edosysteme@gmail.com."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
