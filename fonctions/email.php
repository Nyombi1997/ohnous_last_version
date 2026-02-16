<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



/* email bienvenue */
function welcome($email = "")
{
    $mail = new PHPMailer(true);
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

    $mail->send();
}


/* email code de verification */
function code_verification($email = "", $code = 000000)
{
    $mail = new PHPMailer(true);
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
    $mail->Subject = 'Code de verification mot de passe';
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

    $mail->send();
}


?>