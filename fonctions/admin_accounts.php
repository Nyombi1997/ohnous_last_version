<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "fonctions.php";
    include_once "email.php";
    include_once "../model/AdminAccessToken.php";

    header('Content-Type: application/json; charset=utf-8');

    if(!ohnous_is_admin())
    {
        echo json_encode([
            'result' => 'error',
            'msg' => "Accès administrateur requis."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    createTable('admin_access_tokens', [
        'id INT AUTO_INCREMENT PRIMARY KEY',
        'admin_id INT NOT NULL',
        'token VARCHAR(190) NOT NULL',
        'redirect_path VARCHAR(255) NULL',
        'expire_at DATETIME NOT NULL',
        'used_at DATETIME NULL',
        'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
    ]);

    if(ohnous_table_exists('admins'))
    {
        $missingColumns = [
            'nom' => "ALTER TABLE admins ADD nom VARCHAR(190) NULL AFTER email",
            'profile' => "ALTER TABLE admins ADD profile TEXT NULL AFTER nom",
            'created_by' => "ALTER TABLE admins ADD created_by INT NOT NULL DEFAULT 0 AFTER profile",
        ];

        foreach($missingColumns as $column => $sql)
        {
            if(!ohnous_column_exists('admins', $column))
            {
                $bdd->exec($sql);
            }
        }
    }

    $action = trim((string)($_POST['action'] ?? ''));

    if($action === 'create_admin')
    {
        $nom = trim((string)($_POST['nom'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $autoPassword = (int)($_POST['auto_password'] ?? 1) === 1;

        if($nom === '' || $email === '')
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Veuillez renseigner le nom et l'email de l'admin."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "L'adresse email saisie est invalide."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $existing = only_select('admins', "email = '".addslashes($email)."'", null, null);
        if($existing)
        {
            echo json_encode([
                'result' => 'error',
                'msg' => "Un admin existe déjà avec cette adresse email."
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if($autoPassword || trim($password) === '')
        {
            $password = ohnous_generate_readable_password(14);
        }

        $currentAdmin = ohnous_get_current_account();

        $insertData = [
            'email' => $email,
            'mdp' => password_hash($password, PASSWORD_DEFAULT),
        ];

        if(ohnous_column_exists('admins', 'nom'))
        {
            $insertData['nom'] = $nom;
        }
        if(ohnous_column_exists('admins', 'profile'))
        {
            $insertData['profile'] = '/asset/images/icons/favicon-1.png';
        }
        if(ohnous_column_exists('admins', 'created_by'))
        {
            $insertData['created_by'] = (int)($currentAdmin['id'] ?? 0);
        }

        insert_bdd($bdd, 'admins', $insertData);
        $adminId = (int)$bdd->lastInsertId();

        $tokenModel = new AdminAccessToken($bdd);
        $token = $tokenModel->create($adminId, '/admin');
        $magicUrl = 'https://ohnous.store/admin-acces?token='.urlencode($token['token']);

        $emailSent = ohnous_send_admin_invitation_email(
            $email,
            $nom,
            $magicUrl,
            $password,
            $currentAdmin['nom'] ?? 'OhNous'
        );

        echo json_encode([
            'result' => 'ok',
            'msg' => $emailSent
                ? "Le nouvel admin a été créé et l'email d'accès a bien été envoyé."
                : "Le nouvel admin a été créé, mais l'email d'accès n'a pas pu être envoyé.",
            'generated_password' => $password,
            'magic_url' => $magicUrl
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'result' => 'error',
        'msg' => "Action admin inconnue."
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
