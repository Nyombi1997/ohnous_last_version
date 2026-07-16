<?php

if (!function_exists('ohnous_honeypot_start_session')) {
    function ohnous_honeypot_start_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('renderHoneypot')) {
    function renderHoneypot($form, $minimumSeconds = 1)
    {
        ohnous_honeypot_start_session();

        $form = preg_replace('/[^a-z0-9_-]/i', '', (string)$form);
        $token = bin2hex(random_bytes(24));
        $now = time();

        if (!isset($_SESSION['ohnous_honeypot']) || !is_array($_SESSION['ohnous_honeypot'])) {
            $_SESSION['ohnous_honeypot'] = [];
        }

        foreach ($_SESSION['ohnous_honeypot'] as $storedToken => $entry) {
            if (($entry['created_at'] ?? 0) < $now - 7200) {
                unset($_SESSION['ohnous_honeypot'][$storedToken]);
            }
        }

        $_SESSION['ohnous_honeypot'][$token] = [
            'form' => $form,
            'created_at' => $now,
            'minimum_seconds' => max(1, (int)$minimumSeconds),
        ];

        $csrf = function_exists('ohnous_csrf_token') ? ohnous_csrf_token() : '';
        echo '<div class="ohnous-honeypot" aria-hidden="true">'
            .'<label for="website_contact_'.$token.'">Votre site internet</label>'
            .'<input type="text" id="website_contact_'.$token.'" name="website_contact" value="" autocomplete="off" tabindex="-1">'
            .'</div>'
            .'<input type="hidden" name="ohnous_hp_token" value="'.htmlspecialchars($token, ENT_QUOTES, 'UTF-8').'">'
            .'<input type="hidden" name="csrf_token" value="'.htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8').'">';
    }
}

if (!function_exists('ohnous_log_honeypot_block')) {
    function ohnous_log_honeypot_block($form, $reason)
    {
        $directory = dirname(__DIR__).DIRECTORY_SEPARATOR.'logs';
        if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
            error_log('OHNOUS honeypot: impossible de créer le journal de sécurité.');
            return;
        }

        $line = json_encode([
            'date' => date('Y-m-d'),
            'heure' => date('H:i:s'),
            'formulaire' => (string)$form,
            'route' => (string)($_SERVER['REQUEST_URI'] ?? ''),
            'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            'raison' => (string)$reason,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($line !== false) {
            @file_put_contents($directory.DIRECTORY_SEPARATOR.'security-honeypot.log', $line.PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}

if (!function_exists('validateHoneypot')) {
    function validateHoneypot($form, array $request = null)
    {
        ohnous_honeypot_start_session();
        $request = $request ?? $_POST;
        $form = preg_replace('/[^a-z0-9_-]/i', '', (string)$form);
        $token = trim((string)($request['ohnous_hp_token'] ?? ''));
        $entry = $token !== '' ? ($_SESSION['ohnous_honeypot'][$token] ?? null) : null;
        $reason = '';

        if (trim((string)($request['website_contact'] ?? '')) !== '') {
            $reason = 'champ_leurre_rempli';
        } elseif (!is_array($entry) || !hash_equals((string)($entry['form'] ?? ''), $form)) {
            $reason = 'jeton_formulaire_invalide';
        } elseif (time() - (int)$entry['created_at'] < (int)$entry['minimum_seconds']) {
            $reason = 'soumission_trop_rapide';
        } elseif (time() - (int)$entry['created_at'] > 7200) {
            $reason = 'formulaire_expire';
        } elseif (function_exists('ohnous_validate_csrf') && !ohnous_validate_csrf($request['csrf_token'] ?? '')) {
            $reason = 'csrf_invalide';
        }

        if ($reason !== '' && $token !== '') {
            unset($_SESSION['ohnous_honeypot'][$token]);
        }

        if ($reason !== '') {
            ohnous_log_honeypot_block($form, $reason);
            return false;
        }

        return true;
    }
}

if (!function_exists('ohnous_honeypot_neutral_json')) {
    function ohnous_honeypot_neutral_json()
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['result' => 'ok', 'msg' => 'Votre demande a bien été prise en compte.'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}
