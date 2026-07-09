<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once __DIR__ . '/dependances.php';

/**
 * Prépare PHPMailer uniquement au moment où l'envoi est demandé.
 * Ainsi, le front peut continuer à s'afficher même si vendor/ manque.
 */
function ohnous_build_mailer()
{
    if (!ohnous_load_phpmailer()) {
        error_log(ohnous_missing_phpmailer_message());
        return false;
    }

    try {
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->Encoding = 'base64';
        return $mailer;
    } catch (Exception $e) {
        error_log('Impossible d\'initialiser PHPMailer : ' . $e->getMessage());
        return false;
    }
}

/* email bienvenue */
function welcome($email = "", $isActive = true, $name = "", $activationUrl = "https://ohnous.store/activation-compte")
{
    $mail = ohnous_build_mailer();
    if ($mail === false) {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store'; // ton email
    $mail->Password = 'OhNous@2026'; // ton mot de passe
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenue sur OhNous';
    $activationBlock = $isActive ? '' : '
            <p class="texte">Votre compte n’est pas encore activé. Soumettez votre demande d’activation pour profiter pleinement de votre espace OhNous.</p>
            <div class="div-lien" style="margin-bottom:16px;">
                <a href="'.htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8').'" style="color:#ffffff; text-decoration:none; font-weight:bold;" target="_blank" class="lien">Demander l’activation</a>
            </div>';
    $mail->Body = '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="x-apple-disable-message-reformatting">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <style type="text/css">
            body
            {
                padding-left: 50px;
                padding-right: 50px;
                padding-top: 20px;
                padding-bottom: 20px;
                font-family: Arial, Helvetica, sans-serif;
            }
            .banniere
            {
                width: 100%;
                background-color: #6775d6;
            }
            .image-banniere
            {
                width: 100%;
            }
            .content
            {
                background: #ffffff;
            }
            .titre
            {
                width: 100%;
                padding-top: 10px;
                padding-bottom: 10px;
                text-align: center;
                color: #000;
            }
            .en-valeur
            {
                color: #6775d6;
            }
            .texte
            {
                width: 100%;
                font-size: 16px;
                color: #000;
                text-align: center;
            }
            .div-lien
            {
                width: 100%;
                text-align: center;
                padding-top: 30px;
                padding-bottom: 30px;
            }
            .lien
            {
                text-decoration: none;
                color: #ffffff;
                font-weight: bold;
                padding: 10px;
                border-radius: 10px;
                background: #6775d6;
                outline: none
            }
        </style>
    </head>
    <body>
        <div class="banniere">
            <img src="https://ohnous.store/asset/images/icons/logo-1.png" alt="" srcset="" class="image-banniere">
        </div>
        <div class="content">
            <h1 class="titre">Bienvenue sur <strong class="en-valeur">OhNous</strong></h1>
            '.(trim((string)$name) !== '' ? '<p class="texte">Bonjour '.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').',</p>' : '').'
            <p class="texte">Commencez votre nouvelle expérience avec OhNous</p>
            '.$activationBlock.'
            <div class="div-lien">
                <a href="https://ohnous.store" style="color:#ffffff; text-decoration:none; font-weight:bold;" target="_blank" class="lien">Visiter le site</a>
            </div>
        </div>
    </body>
    </html>
    ';

    try {
        return $mail->send();
    } catch (Exception $e) {
        error_log('Erreur email de bienvenue : ' . $e->getMessage());
        return false;
    }
}

function ohnous_add_admin_recipients($mail)
{
    global $bdd;

    if(!function_exists('ohnous_table_exists') || !ohnous_table_exists('admins'))
    {
        $mail->addAddress('contact@ohnous.store');
        return;
    }

    $admins = select_bdd($bdd, "admins", null, null, 0, "id ASC", false);
    $hasRecipient = false;

    foreach($admins as $admin)
    {
        $email = trim((string)($admin['email'] ?? ''));
        if($email === '' || strtolower($email) === 'admin@admin.com' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            continue;
        }
        $mail->addAddress($email);
        $hasRecipient = true;
    }

    if(!$hasRecipient)
    {
        $mail->addAddress('contact@ohnous.store');
    }
}

function ohnous_send_user_activation_request_email(array $user, array $request)
{
    $mail = ohnous_build_mailer();
    if ($mail === false) {
        return false;
    }

    $adminUrl = 'https://ohnous.store/admin-activation-utilisateurs';

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    ohnous_add_admin_recipients($mail);
    $mail->isHTML(true);
    $mail->Subject = 'Demande d’activation utilisateur - '.$user['nom'];
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">Demande d’activation utilisateur</h1>
                    <p><strong>Utilisateur :</strong> '.htmlspecialchars((string)$user['nom'], ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Email :</strong> '.htmlspecialchars((string)$user['adresse_email'], ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>WhatsApp :</strong> '.htmlspecialchars((string)($request['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Appel :</strong> '.htmlspecialchars((string)($request['telephone'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Instagram :</strong> '.htmlspecialchars((string)($request['instagram'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Facebook :</strong> '.htmlspecialchars((string)($request['facebook'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>TikTok :</strong> '.htmlspecialchars((string)($request['tiktok'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p style="margin-top:24px;">
                        <a href="'.$adminUrl.'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Ouvrir l’espace admin</a>
                    </p>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}


/* email code de vérification */
function code_verification($email = "", $code = '000000')
{
    $mail = ohnous_build_mailer();
    if ($mail === false) {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store'; // ton email
    $mail->Password = 'OhNous@2026'; // ton mot de passe
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Code de vérification du mot de passe';
    $mail->Body = '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="x-apple-disable-message-reformatting">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <style type="text/css">
            body
            {
                padding-left: 50px;
                padding-right: 50px;
                padding-top: 20px;
                padding-bottom: 20px;
                font-family: Arial, Helvetica, sans-serif;
            }
            .banniere
            {
                width: 100%;
                background-color: #6775d6;
            }
            .image-banniere
            {
                width: 100%;
            }
            .content
            {
                background: #ffffff;
            }
            .titre
            {
                width: 100%;
                padding-top: 10px;
                padding-bottom: 10px;
                text-align: center;
                color: #000;
            }
            .en-valeur
            {
                color: #6775d6;
            }
            .texte
            {
                width: 100%;
                font-size: 16px;
                color: #000;
                text-align: center;
            }
            .div-lien
            {
                width: 100%;
                text-align: center;
                padding-top: 30px;
                padding-bottom: 30px;
            }
            .lien
            {
                text-decoration: none;
                color: #ffffff;
                font-weight: bold;
                padding: 10px;
                border-radius: 10px;
                background: #6775d6;
                outline: none;
            }
            /*verification code*/
            .code_verification
            {
                width: 100%;
                padding: 10px 0px;
                font-size: 40px;
                color: #6775d6;
                font-weight: 800;
                text-align: center;
                letter-spacing: 5px;
            }
        </style>
    </head>
    <body>
        <div class="banniere">
            <img src="https://ohnous.store/asset/images/icons/logo-1.png" alt="" srcset="" class="image-banniere">
        </div>
        <div class="content">
            <h1 class="titre">Code de vérification <strong class="en-valeur">OhNous</strong></h1>
            <p class="texte">Voici votre code de vérification</p>
            <div class="code_verification">
                '.$code.'
            </div>
        </div>
    </body>
    </html>
    ';

    return $mail->send();
}

/* email demande d'activation boutique */
function ohnous_send_store_activation_request_email(array $boutique, array $request)
{
    $mail = ohnous_build_mailer();
    if ($mail === false) {
        return false;
    }

    $adminUrl = 'https://ohnous.store/admin-activation-boutique?token='.urlencode($request['token']);

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    ohnous_add_admin_recipients($mail);
    $mail->isHTML(true);
    $mail->Subject = 'Demande d’activation boutique - '.$boutique['nom'];
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">Demande d’activation boutique</h1>
                    <p>Une boutique demande l’activation de son espace.</p>
                    <p><strong>Boutique :</strong> '.htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Email :</strong> '.htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Slug :</strong> '.htmlspecialchars((string)($boutique['slug'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>WhatsApp :</strong> '.htmlspecialchars((string)($request['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Appel :</strong> '.htmlspecialchars((string)($request['telephone'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Instagram :</strong> '.htmlspecialchars((string)($request['instagram'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Facebook :</strong> '.htmlspecialchars((string)($request['facebook'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>TikTok :</strong> '.htmlspecialchars((string)($request['tiktok'] ?? ''), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Description :</strong><br>'.nl2br(htmlspecialchars((string)($boutique['description'] ?? ''), ENT_QUOTES, 'UTF-8')).'</p>
                    <p><strong>Date de demande :</strong> '.date('d/m/Y H:i').'</p>
                    <div style="padding-top:20px;">
                        <a href="'.$adminUrl.'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Ouvrir la page d’activation</a>
                    </div>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

/* email notification message */
function ohnous_send_message_notification_email($email = "", $senderName = "", $messageUrl = "", $messagePreview = "")
{
    $mail = ohnous_build_mailer();
    if ($mail === false || trim($email) === '') {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Nouveau message sur OhNous';
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">Nouveau message</h1>
                    <p><strong>'.htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8').'</strong> vous a envoyé un message sur OhNous.</p>
                    <blockquote style="margin:16px 0; padding:16px 18px; background:#f6f8ff; border-radius:18px; color:#44506d;">'.nl2br(htmlspecialchars(mb_strimwidth($messagePreview, 0, 220, '...'), ENT_QUOTES, 'UTF-8')).'</blockquote>
                    <a href="'.htmlspecialchars($messageUrl, ENT_QUOTES, 'UTF-8').'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Ouvrir la conversation</a>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

/* email de réinitialisation admin */
function ohnous_send_admin_password_reset_email($resetUrl = "")
{
    $mail = ohnous_build_mailer();
    if ($mail === false || trim($resetUrl) === '') {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress('edosysteme@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = 'Réinitialisation du mot de passe admin OhNous';
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">Réinitialisation admin</h1>
                    <p>Une demande de réinitialisation du mot de passe admin OhNous vient d’être générée.</p>
                    <p>Si c’est bien vous, utilisez le bouton ci-dessous pour définir un nouveau mot de passe.</p>
                    <a href="'.htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8').'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Définir un nouveau mot de passe</a>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

/* notification email envoyée à une boutique depuis l’admin */
function ohnous_send_admin_store_contact_email($email = "", $storeName = "", $messagePreview = "", $conversationUrl = "")
{
    $mail = ohnous_build_mailer();
    if ($mail === false || trim($email) === '') {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'L’admin OhNous vous a contacté';
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">L’admin OhNous vous a contacté</h1>
                    <p>Bonjour '.htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8').',</p>
                    <p>Un nouveau message vous attend de la part de l’administration OhNous.</p>
                    <blockquote style="margin:16px 0; padding:16px 18px; background:#f6f8ff; border-radius:18px; color:#44506d;">'.nl2br(htmlspecialchars(mb_strimwidth($messagePreview, 0, 260, '...'), ENT_QUOTES, 'UTF-8')).'</blockquote>
                    <a href="'.htmlspecialchars($conversationUrl, ENT_QUOTES, 'UTF-8').'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Ouvrir la conversation</a>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

/* email d'invitation admin avec lien d'accès direct */
function ohnous_send_admin_invitation_email($email = "", $adminName = "", $magicUrl = "", $temporaryPassword = "", $inviterName = "OhNous")
{
    $mail = ohnous_build_mailer();
    if ($mail === false || trim($email) === '' || trim($magicUrl) === '') {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Votre accès admin OhNous';
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <div style="text-align:center; margin-bottom:20px;">
                        <img src="https://ohnous.store/asset/images/icons/favicon-1.png" alt="OhNous" style="width:72px; height:72px; border-radius:18px;">
                    </div>
                    <h1 style="margin-top:0;">Bienvenue dans l\'espace admin OhNous</h1>
                    <p>Bonjour '.htmlspecialchars($adminName !== '' ? $adminName : 'Admin OhNous', ENT_QUOTES, 'UTF-8').',</p>
                    <p><strong>'.htmlspecialchars($inviterName, ENT_QUOTES, 'UTF-8').'</strong> vient de créer votre accès administrateur.</p>
                    '.($temporaryPassword !== ''
                        ? '<p>Mot de passe temporaire : <strong style="letter-spacing:1px;">'.htmlspecialchars($temporaryPassword, ENT_QUOTES, 'UTF-8').'</strong></p>'
                        : '<p>Votre compte est prêt. Utilisez le lien ci-dessous pour accéder directement à votre espace.</p>'
                    ).'
                    <div style="padding:24px 0 12px;">
                        <a href="'.htmlspecialchars($magicUrl, ENT_QUOTES, 'UTF-8').'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Accéder directement à mon compte admin</a>
                    </div>
                    <p style="color:#6f7392; font-size:14px;">Ce lien est à usage unique et expire automatiquement pour garder votre compte sécurisé.</p>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

/* email signalement article envoyé aux admins */
function ohnous_send_article_report_admin_email(array $article, array $boutique, array $report)
{
    $mail = ohnous_build_mailer();
    if ($mail === false) {
        return false;
    }

    $articleUrl = 'https://ohnous.store/article/'.urlencode((string)$article['slug']);
    $adminUrl = 'https://ohnous.store/admin-articles?search='.urlencode((string)$article['nom']);

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress('contact@ohnous.store');
    $mail->addAddress('edosysteme@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = 'Signalement article - '.$article['nom'];
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">Nouvel article signalé</h1>
                    <p><strong>Article :</strong> '.htmlspecialchars((string)$article['nom'], ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Boutique :</strong> '.htmlspecialchars((string)($boutique['nom'] ?? 'Boutique inconnue'), ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Motif :</strong> '.htmlspecialchars((string)$report['motif'], ENT_QUOTES, 'UTF-8').'</p>
                    <p><strong>Signalé par :</strong> '.htmlspecialchars((string)$report['client_nom'], ENT_QUOTES, 'UTF-8').'</p>
                    <blockquote style="margin:16px 0; padding:16px 18px; background:#f6f8ff; border-radius:18px; color:#44506d;">'.nl2br(htmlspecialchars((string)$report['message'], ENT_QUOTES, 'UTF-8')).'</blockquote>
                    <a href="'.htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8').'" style="display:inline-block; background:#6775d6; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:bold;">Investiguer dans l’admin</a>
                    <a href="'.htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8').'" style="display:inline-block; margin-left:8px; color:#6775d6; text-decoration:none; font-weight:bold;">Voir l’article</a>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

/* email envoyé à la boutique après suppression d'un article signalé */
function ohnous_send_article_deleted_store_email(array $boutique, array $article, $reason = "")
{
    $mail = ohnous_build_mailer();
    $email = trim((string)($boutique['adresse_email'] ?? ''));
    if ($mail === false || $email === '') {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Article supprimé sur OhNous';
    $mail->Body = '
        <html lang="fr">
            <body style="font-family:Arial, Helvetica, sans-serif; background:#f5f7ff; padding:24px;">
                <div style="max-width:680px; margin:0 auto; background:#ffffff; border-radius:24px; padding:32px; box-shadow:0 20px 60px rgba(46, 61, 104, 0.12);">
                    <h1 style="margin-top:0;">Article supprimé</h1>
                    <p>Bonjour '.htmlspecialchars((string)($boutique['nom'] ?? 'Boutique OhNous'), ENT_QUOTES, 'UTF-8').',</p>
                    <p>L’article <strong>'.htmlspecialchars((string)$article['nom'], ENT_QUOTES, 'UTF-8').'</strong> a été supprimé par l’administration OhNous après vérification.</p>
                    <p><strong>Raison :</strong></p>
                    <blockquote style="margin:16px 0; padding:16px 18px; background:#f6f8ff; border-radius:18px; color:#44506d;">'.nl2br(htmlspecialchars((string)$reason, ENT_QUOTES, 'UTF-8')).'</blockquote>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}

function ohnous_send_payment_receipt_email(array $payment, array $order, array $items)
{
    $mail = ohnous_build_mailer();
    $email = trim((string)($order['email'] ?? ''));
    if ($mail === false || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $currency = htmlspecialchars((string)($payment['currency'] ?? 'USD'), ENT_QUOTES, 'UTF-8');
    $subtotal = (float)($order['sous_total'] ?? 0);
    $delivery = (float)($order['livraison_prix'] ?? 0);
    $amountHt = (float)($payment['amount_ht'] ?? ($subtotal + $delivery));
    $feeAmount = (float)($payment['payment_fee_amount'] ?? max(((float)($payment['amount'] ?? 0) - $amountHt), 0));
    $total = (float)($payment['amount'] ?? ($amountHt + $feeAmount));
    $invoiceNumber = 'FAC-' . date('Ymd', strtotime((string)($payment['created_at'] ?? 'now'))) . '-' . str_pad((string)($payment['id'] ?? 0), 5, '0', STR_PAD_LEFT);

    $rows = '';
    foreach ($items as $item) {
        $name = htmlspecialchars((string)($item['article_nom'] ?? 'Article OhNous'), ENT_QUOTES, 'UTF-8');
        $qty = max(1, (int)($item['quantite'] ?? 1));
        $unit = (float)($item['prix_unitaire'] ?? 0);
        $lineTotal = $unit * $qty;
        $rows .= '
            <tr>
                <td style="padding:12px; border-bottom:1px solid #e9ecf5;">'.$name.'</td>
                <td style="padding:12px; border-bottom:1px solid #e9ecf5; text-align:center;">'.$qty.'</td>
                <td style="padding:12px; border-bottom:1px solid #e9ecf5; text-align:right;">'.number_format($unit, 2, '.', ' ').' '.$currency.'</td>
                <td style="padding:12px; border-bottom:1px solid #e9ecf5; text-align:right;">'.number_format($lineTotal, 2, '.', ' ').' '.$currency.'</td>
            </tr>
        ';
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store';
    $mail->Password = 'OhNous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Reçu de paiement OhNous - '.$invoiceNumber;
    $mail->Body = '
        <html lang="fr">
            <body style="margin:0; font-family:Arial, Helvetica, sans-serif; background:#f6f7fb; color:#161722;">
                <div style="max-width:760px; margin:0 auto; padding:24px;">
                    <div style="background:#ffffff; border:1px solid #e9ecf5; border-radius:10px; padding:28px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:24px;">
                            <img src="https://ohnous.store/asset/images/icons/logo.png" alt="OHNOUS" style="max-width:150px; height:auto;">
                            <div style="text-align:right;">
                                <strong style="display:block; font-size:18px;">Paiement reçu</strong>
                                <span style="color:#5d6478;">'.htmlspecialchars(date('d/m/Y H:i', strtotime((string)($payment['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8').'</span>
                            </div>
                        </div>
                        <h1 style="font-size:24px; margin:0 0 18px;">Reçu de paiement</h1>
                        <table role="presentation" style="width:100%; border-collapse:collapse; margin-bottom:22px;">
                            <tr>
                                <td style="padding:8px 0; color:#5d6478;">Numéro de facture</td>
                                <td style="padding:8px 0; text-align:right;"><strong>'.$invoiceNumber.'</strong></td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#5d6478;">Numéro de paiement</td>
                                <td style="padding:8px 0; text-align:right;"><strong>'.htmlspecialchars((string)($payment['reference'] ?? ''), ENT_QUOTES, 'UTF-8').'</strong></td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#5d6478;">Mode de paiement</td>
                                <td style="padding:8px 0; text-align:right;"><strong>'.htmlspecialchars((string)($payment['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8').'</strong></td>
                            </tr>
                        </table>
                        <table style="width:100%; border-collapse:collapse; border:1px solid #e9ecf5; border-radius:10px; overflow:hidden;">
                            <thead>
                                <tr style="background:#f6f7fb;">
                                    <th style="padding:12px; text-align:left;">Article</th>
                                    <th style="padding:12px; text-align:center;">Qté</th>
                                    <th style="padding:12px; text-align:right;">Prix unitaire</th>
                                    <th style="padding:12px; text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>'.$rows.'</tbody>
                        </table>
                        <table role="presentation" style="width:100%; border-collapse:collapse; margin-top:22px;">
                            <tr>
                                <td style="padding:8px 0; color:#5d6478;">Sous-total</td>
                                <td style="padding:8px 0; text-align:right;">'.number_format($amountHt, 2, '.', ' ').' '.$currency.'</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#5d6478;">TVA / Frais (10 %)</td>
                                <td style="padding:8px 0; text-align:right;">'.number_format($feeAmount, 2, '.', ' ').' '.$currency.'</td>
                            </tr>
                            <tr>
                                <td style="padding:12px 0; font-size:18px;"><strong>Montant total payé</strong></td>
                                <td style="padding:12px 0; text-align:right; font-size:18px;"><strong>'.number_format($total, 2, '.', ' ').' '.$currency.'</strong></td>
                            </tr>
                        </table>
                        <div style="margin-top:24px; padding-top:18px; border-top:1px solid #e9ecf5; color:#5d6478; font-size:14px; line-height:1.6;">
                            <strong style="color:#161722;">OHNOUS</strong><br>
                            NIF : G2526655H<br>
                            RCCM : CD/KNG/RCCM/21-A-01722<br>
                            Email : contact@ohnous.store<br>
                            Téléphone / WhatsApp : +243857663333
                        </div>
                    </div>
                </div>
            </body>
        </html>
    ';

    return $mail->send();
}


?>
