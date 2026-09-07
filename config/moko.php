<?php
return [
    'enabled' => getenv('MOKO_PAYOUT_ENABLED') === '1',
    'base_url' => rtrim(getenv('MOKO_BASE_URL') ?: 'https://payouts.gofreshpay.com', '/'),
    'public_key' => getenv('MOKO_PUBLIC_KEY') ?: '',
    'secret_key' => getenv('MOKO_SECRET_KEY') ?: '',
    'webhook_secret' => getenv('MOKO_WEBHOOK_SECRET') ?: '',
    'callback_url' => getenv('MOKO_CALLBACK_URL') ?: '',
];
