<?php
if (PHP_SAPI !== 'cli') exit;
session_start(['use_cookies'=>false, 'cache_limiter'=>'', 'save_path'=>sys_get_temp_dir()]);
require __DIR__.'/../fonctions/moko_recipient.php';
$blocks = [];
function ohnous_log_honeypot_block($form, $reason) { $GLOBALS['blocks'][] = [$form, $reason]; }
function ohnous_csrf_token() { return $_SESSION['test_csrf'] ?? ($_SESSION['test_csrf'] = bin2hex(random_bytes(24))); }
function ohnous_validate_csrf($token) { return is_string($token) && hash_equals(ohnous_csrf_token(), $token); }
require __DIR__.'/../fonctions/honeypot.php';
function check($value, $message) { if (!$value) throw new RuntimeException($message); }
try {
    $csrf = ohnous_recipient_csrf();
    check(ohnous_validate_recipient_csrf($csrf), 'CSRF valide refusé');
    check(!ohnous_validate_recipient_csrf('faux') && !ohnous_validate_recipient_csrf([]), 'CSRF invalide accepté');
    $_SESSION['recipient_csrf_at'] = time() - 7201;
    check(!ohnous_validate_recipient_csrf($csrf), 'CSRF expiré accepté');
    $next = ohnous_recipient_csrf(true);
    check($next !== $csrf && !ohnous_validate_recipient_csrf($csrf) && ohnous_validate_recipient_csrf($next), 'CSRF non renouvelé');
    ob_start(); renderHoneypot('moko_recipient'); $html = ob_get_clean();
    check(strpos($html, 'aria-hidden="true"') !== false && strpos($html, 'tabindex="-1"') !== false && strpos($html, 'autocomplete="off"') !== false, 'Champ leurre incomplet');
    $token = array_key_last($_SESSION['ohnous_honeypot']);
    $entry = $_SESSION['ohnous_honeypot'][$token];
    $request = ['website_contact'=>'','ohnous_hp_token'=>$token,'csrf_token'=>ohnous_csrf_token()];
    check(!validateHoneypot('moko_recipient', $request), 'Soumission instantanée acceptée');
    $_SESSION['ohnous_honeypot'][$token] = array_merge($entry, ['created_at'=>time()-2]);
    check(validateHoneypot('moko_recipient', $request), 'Soumission normale refusée');
    check(!validateHoneypot('moko_recipient', array_merge($request, ['website_contact'=>'robot'])), 'Leurre rempli accepté');
    $_SESSION['ohnous_honeypot'][$token] = array_merge($entry, ['created_at'=>time()-2]);
    check(!validateHoneypot('moko_recipient', array_merge($request, ['csrf_token'=>'faux'])), 'CSRF du formulaire ignoré');
    check(count($blocks) === 3, 'Détections non journalisées');
    echo "Formulaire bénéficiaire : CSRF expirant et renouvelé, délai serveur, Honeypot et soumission normale sans JavaScript : OK.\n";
} finally { session_destroy(); }
