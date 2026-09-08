<?php
include_once '../model/bdd.php';
include_once '../model/select.php';
include_once 'fonctions.php';
include_once 'admin_password_security.php';

header('Content-Type: application/json; charset=utf-8');
if (!validateHoneypot('nouveau_mot_de_passe_admin')) {
    ohnous_honeypot_neutral_json();
}

try {
    $token = trim((string)($_POST['token'] ?? ''));
    $password = (string)($_POST['mdp'] ?? '');
    $oneTimeCode = strtoupper(trim((string)($_POST['one_time_code'] ?? '')));
    if (strpos($token, 'otp:') === 0) $token = '';
    if ($oneTimeCode !== '') $token = 'otp:'.hash('sha256', $oneTimeCode);
    $adminId = ohnous_complete_admin_password_reset($bdd, $token, $password);
    unset($_SESSION['ohnous_honeypot'][$_POST['ohnous_hp_token'] ?? ''], $_SESSION['ohnous_csrf']);
    session_regenerate_id(true);
    $_SESSION['admin_ohnous_987654321'] = $adminId;
    echo json_encode(['result'=>'ok', 'redirect'=>'/admin'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['result'=>'error', 'msg'=>'Réinitialisation impossible. Vérifiez le code ou demandez un nouveau lien.'], JSON_UNESCAPED_UNICODE);
}
