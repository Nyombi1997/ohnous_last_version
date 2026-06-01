<?php

if (!defined('WHATSAPP_VERIFY_TOKEN')) {
    define('WHATSAPP_VERIFY_TOKEN', getenv('WHATSAPP_VERIFY_TOKEN') ?: 'change-me-verify-token');
}

if (!defined('WHATSAPP_ACCESS_TOKEN')) {
    define('WHATSAPP_ACCESS_TOKEN', getenv('WHATSAPP_ACCESS_TOKEN') ?: 'change-me-access-token');
}

if (!defined('WHATSAPP_PHONE_NUMBER_ID')) {
    define('WHATSAPP_PHONE_NUMBER_ID', getenv('WHATSAPP_PHONE_NUMBER_ID') ?: 'change-me-phone-number-id');
}

if (!defined('WHATSAPP_API_VERSION')) {
    define('WHATSAPP_API_VERSION', 'v25.0');
}
