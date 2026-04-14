<?php

class AdminController
{
    public function showAdminAccounts()
    {
        $view = new View('admin-admins');
        $view->render('Ohnous | Gestion des admins');
    }

    public function consumeAccessToken()
    {
        include_once MODEL . 'bdd.php';
        include_once MODEL . 'select.php';
        include_once FONCTION . 'fonctions.php';
        include_once MODEL . 'AdminAccessToken.php';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $tokenValue = trim((string)($_GET['token'] ?? ''));
        if ($tokenValue === '') {
            header('Location:/admin-login?error=token');
            exit();
        }

        $tokenModel = new AdminAccessToken($bdd);
        $resolved = $tokenModel->consumeValidToken($tokenValue);

        if (!$resolved) {
            header('Location:/admin-login?error=expired-token');
            exit();
        }

        $_SESSION['admin_ohnous_987654321'] = (int)$resolved['admin_id'];

        $redirect = '/admin-admins?login=1';
        if (!empty($resolved['redirect_path'])) {
            $redirect = $resolved['redirect_path'];
        }

        header('Location:' . $redirect);
        exit();
    }
}
