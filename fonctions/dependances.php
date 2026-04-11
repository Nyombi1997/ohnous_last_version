<?php
/**
 * Charge les dépendances externes uniquement quand elles sont nécessaires.
 * Cela évite les warnings globaux si vendor/ a été supprimé localement.
 */

if (!function_exists('ohnous_project_root')) {
    function ohnous_project_root()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('ohnous_vendor_path')) {
    function ohnous_vendor_path($relativePath = '')
    {
        $vendorRoot = defined('VENDOR') ? VENDOR : ohnous_project_root() . 'vendor' . DIRECTORY_SEPARATOR;

        return $vendorRoot . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('ohnous_load_phpmailer')) {
    function ohnous_load_phpmailer()
    {
        if (
            class_exists('PHPMailer\\PHPMailer\\PHPMailer') &&
            class_exists('PHPMailer\\PHPMailer\\Exception') &&
            class_exists('PHPMailer\\PHPMailer\\SMTP')
        ) {
            return true;
        }

        $autoloadPath = ohnous_vendor_path('autoload.php');
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        $phpMailerFiles = [
            'phpmailer/phpmailer/src/Exception.php',
            'phpmailer/phpmailer/src/PHPMailer.php',
            'phpmailer/phpmailer/src/SMTP.php',
        ];

        foreach ($phpMailerFiles as $file) {
            $fullPath = ohnous_vendor_path($file);
            if (file_exists($fullPath)) {
                require_once $fullPath;
            }
        }

        return (
            class_exists('PHPMailer\\PHPMailer\\PHPMailer') &&
            class_exists('PHPMailer\\PHPMailer\\Exception') &&
            class_exists('PHPMailer\\PHPMailer\\SMTP')
        );
    }
}

if (!function_exists('ohnous_missing_phpmailer_message')) {
    function ohnous_missing_phpmailer_message()
    {
        return "PHPMailer est introuvable. Réinstallez les dépendances Composer du projet.";
    }
}

if (!function_exists('ohnous_load_imagekit')) {
    function ohnous_load_imagekit()
    {
        if (class_exists('ImageKit\\ImageKit')) {
            return true;
        }

        $autoloadPath = ohnous_vendor_path('autoload.php');
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        $legacyAutoload = __DIR__ . DIRECTORY_SEPARATOR . 'autoload_imagekit.php';
        if (!class_exists('ImageKit\\ImageKit') && file_exists($legacyAutoload)) {
            require_once $legacyAutoload;
        }

        return class_exists('ImageKit\\ImageKit');
    }
}
