<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";

    header('Content-Type: application/json; charset=utf-8');

    if (!validateHoneypot('mot_de_passe_admin_oublie')) {
        ohnous_honeypot_neutral_json();
    }

    $email = trim((string)html_entity_decode(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS)));

    if($email === '')
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Entrez l’adresse email admin."
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
            'msg' => "Aucun compte admin correspondant."
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

    if(!ohnous_send_admin_password_reset_email($resetUrl))
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Impossible d’envoyer l’email de réinitialisation."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'ok',
        'msg' => "Un email de réinitialisation a été envoyé à edosysteme@gmail.com."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
