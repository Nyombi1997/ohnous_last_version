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
        return new PHPMailer(true);
    } catch (Exception $e) {
        error_log('Impossible d\'initialiser PHPMailer : ' . $e->getMessage());
        return false;
    }
}

/* email bienvenue */
function welcome($email = "")
{
    $mail = ohnous_build_mailer();
    if ($mail === false) {
        return false;
    }

    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@ohnous.store'; // ton email
    $mail->Password = 'Ohnous@2026'; // ton mot de passe
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Bienvenue sur OhNous';
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
            <p class="texte">Commencez votre nouvelle expérience avec OhNous</p>
            <div class="div-lien">
                <a href="https://ohnous.store" style="color:#ffffff; text-decoration:none; font-weight:bold;" target="_blank" class="lien">Visiter le site</a>
            </div>
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
    $mail->Password = 'Ohnous@2026'; // ton mot de passe
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
    $mail->Password = 'Ohnous@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('contact@ohnous.store', 'Ohnous');
    $mail->addAddress('contact@ohnous.store');
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
    $mail->Password = 'Ohnous@2026';
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
    $mail->Password = 'Ohnous@2026';
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
    $mail->Password = 'Ohnous@2026';
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


?>
